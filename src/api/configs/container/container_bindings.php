<?php

declare(strict_types=1);

use Aws\S3\S3Client;
use Clockwork\Clockwork;
use Clockwork\DataSource\DoctrineDataSource;
use Clockwork\Storage\FileStorage;

use function DI\create;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use DoctrineExtensions\Query\Mysql\DateFormat;
use DoctrineExtensions\Query\Mysql\Month;
use DoctrineExtensions\Query\Mysql\Year;
use JR\Tracker\Config;
use JR\Tracker\DataObject\Config\AdminAuthCookieConfig;
use JR\Tracker\DataObject\Config\AdminTokenConfig;
use JR\Tracker\DataObject\Config\AuthCookieConfig;
use JR\Tracker\DataObject\Config\SessionConfig;
use JR\Tracker\DataObject\Config\TokenConfig;
use JR\Tracker\Enum\AppEnvironmentEnum;
use JR\Tracker\Enum\SameSiteEnum;
use JR\Tracker\Enum\StorageDriverEnum;
use JR\Tracker\Filter\UserFilter;
use JR\Tracker\Repository\Contract\PasswordResetRepositoryInterface;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Repository\Contract\VerifyEmailRepositoryInterface;
use JR\Tracker\Repository\Implementation\PasswordResetRepository;
use JR\Tracker\Repository\Implementation\UserRepository;
use JR\Tracker\Repository\Implementation\VerifyEmailRepository;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorFactoryInterface;
use JR\Tracker\RequestValidator\Request\Implementation\RequestValidatorFactory;
use JR\Tracker\Service\Contract\AuthServiceInterface;
use JR\Tracker\Service\Contract\CookieServiceInterface;
use JR\Tracker\Service\Contract\EntityManagerServiceInterface;
use JR\Tracker\Service\Contract\HashServiceInterface;
use JR\Tracker\Service\Contract\LoggerServiceInterface;
use JR\Tracker\Service\Contract\PasswordResetServiceInterface;
use JR\Tracker\Service\Contract\RequestServiceInterface;
use JR\Tracker\Service\Contract\SessionServiceInterface;
use JR\Tracker\Service\Contract\TokenServiceInterface;
use JR\Tracker\Service\Contract\VerifyEmailServiceInterface;
use JR\Tracker\Service\Implementation\AuthService;
use JR\Tracker\Service\Implementation\CookieService;
use JR\Tracker\Service\Implementation\CsrfService;
use JR\Tracker\Service\Implementation\EntityManagerService;
use JR\Tracker\Service\Implementation\HashService;
use JR\Tracker\Service\Implementation\LoggerService;
use JR\tracker\Service\Implementation\MailerService;
use JR\Tracker\Service\Implementation\PasswordResetService;
use JR\Tracker\Service\Implementation\RequestService;
use JR\Tracker\Service\Implementation\SessionService;
use JR\Tracker\Service\Implementation\TokenService;
use JR\Tracker\Service\Implementation\VerifyEmailService;
use JR\Tracker\Shared\RouteEntityBindingStrategy;
use JR\Tracker\Strategy\Contract\AuthStrategyFactoryInterface;
use JR\Tracker\Strategy\Implementation\AuthStrategyFactory;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\SimpleCache\CacheInterface;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;

// TODO: Pokud budu chtít filtrovat data podle uživatele, tak to musím udělat pomocí filtrů. Video 129
return [
    #region Project config
  App::class => function (ContainerInterface $container) {
    AppFactory::setContainer($container);

    $middlewarePath = CONFIG_PATH . '/middleware.php';
    $addMiddleware = file_exists($middlewarePath) ? require $middlewarePath : function (App $app) {
    };
    $router = require CONFIG_PATH . '/routes/web.php';

    $app = AppFactory::create();

    $app->getRouteCollector()->setDefaultInvocationStrategy(
      new RouteEntityBindingStrategy(
        $container->get(EntityManagerService::class),
        $app->getResponseFactory()
      )
    );

    $router($app);
    $addMiddleware($app);

    return $app;
  },
  Config::class => create(Config::class)->constructor(
    require CONFIG_PATH . '/app.php'
  ),
    #endregion

    #region Database
  EntityManagerInterface::class => function (Config $config) {
    $ormConfig = ORMSetup::createAttributeMetadataConfiguration(
      $config->get('doctrine.entity_dir'),
      $config->get('doctrine.dev_mode'),
    );

    $ormConfig->enableNativeLazyObjects(true);

    $ormConfig->addFilter('user', UserFilter::class);

    if (class_exists('DoctrineExtensions\Query\Mysql\Year')) {
      $ormConfig->addCustomDatetimeFunction('YEAR', Year::class);
    }

    if (class_exists('DoctrineExtensions\Query\Mysql\Month')) {
      $ormConfig->addCustomDatetimeFunction('MONTH', Month::class);
    }

    if (class_exists('DoctrineExtensions\Query\Mysql\DateFormat')) {
      $ormConfig->addCustomStringFunction('DATE_FORMAT', DateFormat::class);
    }

    return new EntityManager(
      DriverManager::getConnection($config->get('doctrine.connection'), $ormConfig),
      $ormConfig
    );
  },
    #endregion

    #region Configs
  SessionConfig::class => fn(Config $config) => new SessionConfig(
    $config->get('session.name', ''),
    $config->get('session.flash_name', 'flash'),
    $config->get('session.secure', true),
    $config->get('session.httponly', true),
    SameSiteEnum::from($config->get('session.samesite', 'lax'))
  ),
  TokenConfig::class => fn(Config $config) => new TokenConfig(
    $config->get('token.exp_access'),
    $config->get('token.exp_refresh'),
    $config->get('token.algorithm'),
    $config->get('token.key_access'),
    $config->get('token.key_refresh')
  ),
  AdminTokenConfig::class => fn(Config $config) => new AdminTokenConfig(
    (int) $config->get('admin_token.exp_access'),
    (int) $config->get('admin_token.exp_refresh'),
    $config->get('admin_token.algorithm'),
    $config->get('admin_token.key_access'),
    $config->get('admin_token.key_refresh')
  ),
  AuthCookieConfig::class => fn(Config $config) => new AuthCookieConfig(
    $config->get('auth_cookie.name'),
    $config->get('auth_cookie.secure'),
    $config->get('auth_cookie.http_only'),
    SameSiteEnum::from($config->get('auth_cookie.same_site')),
    $config->get('auth_cookie.expires'),
    $config->get('auth_cookie.path')
  ),
  AdminAuthCookieConfig::class => fn(Config $config) => new AdminAuthCookieConfig(
    $config->get('admin_auth_cookie.name'),
    (bool) $config->get('admin_auth_cookie.secure'),
    (bool) $config->get('admin_auth_cookie.http_only'),
    SameSiteEnum::from($config->get('admin_auth_cookie.same_site')),
    (int) $config->get('admin_auth_cookie.expires'),
    $config->get('admin_auth_cookie.path')
  ),
    #endregion

    #region Factories
  RequestValidatorFactoryInterface::class => fn(ContainerInterface $container) => $container->get(
    RequestValidatorFactory::class
  ),
  ResponseFactoryInterface::class => fn(App $app) => $app->getResponseFactory(),
  RateLimiterFactory::class => fn(RedisAdapter $redisAdapter, Config $config) => new RateLimiterFactory(
    $config->get('limiter'),
    new CacheStorage($redisAdapter)
  ),
  AuthStrategyFactoryInterface::class => fn(ContainerInterface $container) => $container->get(
    AuthStrategyFactory::class
  ),
    #endregion

    #region Services
  TokenServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    TokenService::class
  ),
  CookieServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    CookieService::class
  ),
  SessionServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    SessionService::class
  ),
  HashServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    HashService::class
  ),
  EntityManagerServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    EntityManagerService::class
  ),
  RequestServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    RequestService::class
  ),
  AuthServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    AuthService::class
  ),
  VerifyEmailServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    VerifyEmailService::class
  ),
  LoggerServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    LoggerService::class
  ),
  PasswordResetServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    PasswordResetService::class
  ),
    #endregion

    #region Repositories
  UserRepositoryInterface::class => fn(ContainerInterface $container) => $container->get(
    UserRepository::class
  ),
  VerifyEmailRepositoryInterface::class => fn(ContainerInterface $container) => $container->get(
    VerifyEmailRepository::class
  ),
  PasswordResetRepositoryInterface::class => fn(ContainerInterface $container) => $container->get(
    PasswordResetRepository::class
  ),
    #endregion

    #region Other
  Twig::class => function (Config $config, ContainerInterface $container) {
    $twig = Twig::create(TEMPLATE_PATH, [
      'cache' => STORAGE_PATH . '/cache/templates',
      'auto_reload' => AppEnvironmentEnum::isDevelopment($config->get('app_environment')),
    ]);

    // $twig->addExtension(new IntlExtension());
    return $twig;
  },
  'csrf' => fn(ResponseFactoryInterface $responseFactory, CsrfService $csrf) => new Guard(
    $responseFactory,
    failureHandler: $csrf->failureHandler(),
    persistentTokenMode: true
  ),
  Filesystem::class => function (Config $config) {
    $digitalOcean = function (array $options) {
      $client = new S3Client(
        [
          'credentials' => [
            'key' => $options['key'],
            'secret' => $options['secret'],
          ],
          'region' => $options['region'],
          'version' => $options['version'],
          'endpoint' => $options['endpoint'],
        ]
      );

      return new AwsS3V3Adapter(
        $client,
        $options['bucket']
      );
    };

    $adapter = match ($config->get('storage.driver')) {
      StorageDriverEnum::Local => new LocalFilesystemAdapter(STORAGE_PATH),
      StorageDriverEnum::Remote_DO => $digitalOcean($config->get('storage.s3'))
    };

    return new Filesystem($adapter);
  },
  Clockwork::class => function (Config $config, EntityManagerInterface $entityManager) {
    $clockwork = new Clockwork();

    $clockwork->storage(new FileStorage(STORAGE_PATH . '/clockwork'));
    $clockwork->addDataSource(new DoctrineDataSource($entityManager));

    return $clockwork;
  },
  MailerInterface::class => function (Config $config) {
    if ($config->get('mailer.driver') === 'log') {
      return new MailerService();
    }

    $transport = Transport::fromDsn($config->get('mailer.dsn'));

    return new Mailer($transport);
  },
  BodyRendererInterface::class => fn(Twig $twig) => new BodyRenderer($twig->getEnvironment()),
  RouteParserInterface::class => fn(App $app) => $app->getRouteCollector()->getRouteParser(),
  CacheInterface::class => fn(RedisAdapter $redisAdapter) => new Psr16Cache($redisAdapter),
  RedisAdapter::class => function (Config $config) {
    $redis = new Redis();
    $config = $config->get('redis');

    $redis->connect($config['host'], (int) $config['port']);

    if ($config['password']) {
      $redis->auth($config['password']);
    }

    return new RedisAdapter($redis);
  },

  #endregion

  // TODO: Pokud budu chtít napojit cache, napojím ji do dané metody podle videa 133, 19:00 a dát do env proměnné
];
