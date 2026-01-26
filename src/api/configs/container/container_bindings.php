<?php

declare(strict_types=1);

use Slim\App;
use Aws\S3\S3Client;
use Slim\Views\Twig;
use JR\Tracker\Config;
use function DI\create;
use Clockwork\Clockwork;
use Doctrine\ORM\ORMSetup;
use Slim\Factory\AppFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use League\Flysystem\Filesystem;
use JR\Tracker\Filter\UserFilter;
use Clockwork\Storage\FileStorage;
use Twig\Extra\Intl\IntlExtension;
use JR\ChefsDiary\Mail\SignUpEmail;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Asset\Package;
use Symfony\Component\Mailer\Mailer;
use JR\ChefsDiary\Enums\SameSiteEnum;
use Psr\Container\ContainerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Mailer\Transport;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineExtensions\Query\Mysql\Year;
use DoctrineExtensions\Query\Mysql\Month;
use Slim\Interfaces\RouteParserInterface;
use JR\ChefsDiary\Enums\StorageDriverEnum;
use JR\ChefsDiary\Mail\TwoFactorAuthEmail;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use JR\ChefsDiary\Enums\AppEnvironmentEnum;
use Clockwork\DataSource\DoctrineDataSource;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Symfony\Component\Mailer\MailerInterface;
use DoctrineExtensions\Query\Mysql\DateFormat;
use Psr\Http\Message\ResponseFactoryInterface;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Mime\BodyRendererInterface;
use JR\ChefsDiary\DataObjects\Configs\TokenConfig;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use JR\ChefsDiary\DataObjects\Configs\SessionConfig;
use JR\ChefsDiary\Shared\RouteEntityBindingStrategy;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use JR\ChefsDiary\Services\Implementation\AuthService;
use JR\ChefsDiary\Services\Implementation\UserService;
use JR\ChefsDiary\DataObjects\Configs\AuthCookieConfig;
use JR\ChefsDiary\Services\Implementation\TokenService;
use Symfony\Component\RateLimiter\Storage\CacheStorage;
use JR\ChefsDiary\Services\Implementation\CookieService;
use JR\ChefsDiary\Services\Implementation\MailerService;
use JR\ChefsDiary\Services\Implementation\VerifyService;
use JR\ChefsDiary\Services\Contract\AuthServiceInterface;
use JR\ChefsDiary\Services\Contract\UserServiceInterface;
use JR\ChefsDiary\Services\Implementation\SessionService;
use JR\ChefsDiary\Services\Contract\TokenServiceInterface;
use JR\ChefsDiary\Services\Contract\CookieServiceInterface;
use JR\ChefsDiary\Services\Contract\VerifyServiceInterface;
use JR\ChefsDiary\RequestValidators\RequestValidatorFactory;
use JR\ChefsDiary\Services\Contract\SessionServiceInterface;
use JR\ChefsDiary\Repositories\Implementation\UserRepository;
use Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;
use JR\ChefsDiary\Services\Implementation\EntityManagerService;
use JR\ChefsDiary\Services\Implementation\UserLoginCodeService;
use JR\ChefsDiary\Repositories\Contract\UserRepositoryInterface;
use JR\ChefsDiary\Services\Contract\EntityManagerServiceInterface;
use JR\ChefsDiary\Services\Contract\UserLoginCodeServiceInterface;
use JR\ChefsDiary\RequestValidators\RequestValidatorFactoryInterface;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;

// TODO: Pokud budu chtít filtrovat data podle uživatele, tak to musím udělat pomocí filtrů. Video 129
return [
        #region Project config
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        $addMiddleware = require CONFIG_PATH . '/middleware.php';
        $router = require CONFIG_PATH . '/routes/web.php';

        $app = AppFactory::create();

        // $app->getRouteCollector()->setDefaultInvocationStrategy(
        //     new RouteEntityBindingStrategy(
        //         $container->get(EntityManagerService::class),
        //         $app->getResponseFactory()
        //     )
        // );
    
        $router($app);
        $addMiddleware($app);

        return $app;
    },
    Config::class => create(Config::class)->constructor(
        require CONFIG_PATH . '/app.php'
    ),
        // ResponseFactoryInterface::class => fn(App $app) => $app->getResponseFactory(),
        //     // AuthServiceInterface::class => fn(ContainerInterface $container) => $container->get(
        //     //     AuthService::class
        //     // ),
        //     #endregion

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

    //     #region Configs
    // TokenConfig::class => fn(Config $config) => new TokenConfig(
    //     $config->get('token.exp_access'),
    //     $config->get('token.exp_refresh'),
    //     $config->get('token.algorithm'),
    //     $config->get('token.key_access'),
    //     $config->get('token.key_refresh')
    // ),
    // SessionConfig::class => fn(Config $config) => new SessionConfig(
    //     $config->get('session.name', ''),
    //     $config->get('session.flash_name', 'flash'),
    //     $config->get('session.secure', true),
    //     $config->get('session.httponly', true),
    //     SameSiteEnum::from($config->get('session.samesite', 'lax'))
    // ),
    // AuthCookieConfig::class => fn(Config $config) => new AuthCookieConfig(
    //     $config->get('auth_cookie.name'),
    //     $config->get('auth_cookie.secure'),
    //     $config->get('auth_cookie.http_only'),
    //     SameSiteEnum::from($config->get('auth_cookie.same_site')),
    //     $config->get('auth_cookie.expires'),
    //     $config->get('auth_cookie.path')
    // ),
    //     #endregion

    //     #region Factories
    // RequestValidatorFactoryInterface::class => fn(ContainerInterface $container) => $container->get(
    //     RequestValidatorFactory::class
    // ),
    // ResponseFactoryInterface::class => fn(App $app) => $app->getResponseFactory(),
    //     #endregion

    //     #region Services
    // TokenServiceInterface::class => fn(ContainerInterface $container) => new TokenService(
    //     $container->get(
    //         ResponseFactoryInterface::class
    //     ),
    //     $container->get(
    //         TokenConfig::class
    //     ),
    // ),
    // CookieServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    //     CookieService::class
    // ),
    // SessionServiceInterface::class => fn(ContainerInterface $container) => new SessionService(
    //     $container->get(
    //         SessionConfig::class
    //     ),
    // ),
    // UserLoginCodeServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    //     UserLoginCodeService::class
    // ),
    // AuthServiceInterface::class => fn(ContainerInterface $container) => new AuthService(
    //     $container->get(
    //         UserRepositoryInterface::class
    //     ),
    //     $container->get(
    //         TokenServiceInterface::class
    //     ),
    //     $container->get(
    //         CookieServiceInterface::class
    //     ),
    //     $container->get(
    //         AuthCookieConfig::class
    //     ),
    //     $container->get(
    //         TokenConfig::class
    //     ),
    //     $container->get(
    //         SessionServiceInterface::class
    //     ),
    //     $container->get(
    //         SignUpEmail::class
    //     ),
    //     $container->get(
    //         TwoFactorAuthEmail::class
    //     ),
    //     $container->get(
    //         UserLoginCodeServiceInterface::class
    //     )
    // ),
    // UserServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    //     UserService::class
    // ),
    // EntityManagerServiceInterface::class => fn(EntityManagerInterface $entityManager) => new EntityManagerService(
    //     $entityManager
    // ),
    // VerifyServiceInterface::class => fn(ContainerInterface $container) => $container->get(
    //     VerifyService::class
    // ),
    //     #endregion

    //     #region Repositories
    // UserRepositoryInterface::class => fn(ContainerInterface $container) => $container->get(
    //     UserRepository::class
    // ),
    //     #endregion

    //     #region Other
    // Twig::class => function (Config $config, ContainerInterface $container) {
    //     $twig = Twig::create(TEMPLATE_PATH, [
    //         'cache' => STORAGE_PATH . '/cache/templates',
    //         'auto_reload' => AppEnvironmentEnum::isDevelopment($config->get('app_environment')),
    //     ]);

    //     $twig->addExtension(new IntlExtension());
    //     $twig->addExtension(new EntryFilesTwigExtension($container));
    //     $twig->addExtension(new AssetExtension($container->get('webpack_encore.packages')));

    //     return $twig;
    // },
    // /**
    //  * The following two bindings are needed for EntryFilesTwigExtension & AssetExtension to work for Twig
    //  */
    // 'webpack_encore.packages' => fn() => new Packages(
    //     new Package(new JsonManifestVersionStrategy(BUILD_PATH . '/manifest.json'))
    // ),
    // 'webpack_encore.tag_renderer' => fn(ContainerInterface $container) => new TagRenderer(
    //     $container->get(EntrypointLookupCollectionInterface::class),
    //     $container->get('webpack_encore.packages'),
    // ),
    // Filesystem::class => function (Config $config) {
    //     $digitalOcean = function (array $options) {
    //         $client = new S3Client(
    //             [
    //                 'credentials' => [
    //                     'key' => $options['key'],
    //                     'secret' => $options['secret'],
    //                 ],
    //                 'region' => $options['region'],
    //                 'version' => $options['version'],
    //                 'endpoint' => $options['endpoint'],
    //             ]
    //         );

    //         return new AwsS3V3Adapter(
    //             $client,
    //             $options['bucket']
    //         );
    //     };

    //     $adapter = match ($config->get('storage.driver')) {
    //         StorageDriverEnum::Local => new LocalFilesystemAdapter(STORAGE_PATH),
    //         StorageDriverEnum::Remote_DO => $digitalOcean($config->get('storage.s3'))
    //     };

    //     return new Filesystem($adapter);
    // },
    // Clockwork::class => function (EntityManagerInterface $entityManager) {
    //     $clockwork = new Clockwork();

    //     $clockwork->storage(new FileStorage(STORAGE_PATH . '/clockwork'));
    //     $clockwork->addDataSource(new DoctrineDataSource($entityManager));

    //     return $clockwork;
    // },
    // MailerInterface::class => function (Config $config) {
    //     if ($config->get('mailer.driver') === 'log') {
    //         return new MailerService();
    //     }

    //     $transport = Transport::fromDsn($config->get('mailer.dsn'));

    //     return new Mailer($transport);
    // },
    // BodyRendererInterface::class => fn(Twig $twig) => new BodyRenderer($twig->getEnvironment()),
    // RouteParserInterface::class => fn(App $app) => $app->getRouteCollector()->getRouteParser(),
    // CacheInterface::class => fn(RedisAdapter $redisAdapter) => new Psr16Cache($redisAdapter),
    // RedisAdapter::class => function (Config $config) {
    //     $redis = new \Redis();
    //     $config = $config->get('redis');

    //     $redis->connect($config['host'], (int) $config['port']);

    //     if ($config['password']) {
    //         $redis->auth($config['password']);
    //     }

    //     return new RedisAdapter($redis);
    // },
    // RateLimiterFactory::class => fn(RedisAdapter $redisAdapter, Config $config) => new RateLimiterFactory(
    //     $config->get('limiter'),
    //     new CacheStorage($redisAdapter)
    // ),
    #endregion

    // TODO: Pokud budu chtít napojit cache, napojím ji do dané metody podle videa 133, 19:00 a dát do env proměnné
];