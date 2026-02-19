<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use JR\Tracker\DataObject\Config\AuthCookieConfig;
use JR\Tracker\DataObject\Config\TokenConfig;
use JR\Tracker\DataObject\Data\CookieConfigData;
use JR\Tracker\DataObject\Data\LoginUserData;
use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Enum\DomainContextEnum;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Exception\ValidationException;
use JR\Tracker\Mail\SignUpEmail;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Service\Contract\AuthServiceInterface;
use JR\Tracker\Service\Contract\CookieServiceInterface;
use JR\Tracker\Service\Contract\HashServiceInterface;
use JR\Tracker\Service\Contract\SessionServiceInterface;
use JR\Tracker\Service\Contract\TokenServiceInterface;
use JR\Tracker\Service\Contract\VerifyEmailServiceInterface;
use JR\Tracker\Shared\Helper\UserRoleHelper;
use JR\Tracker\Strategy\Contract\AuthStrategyFactoryInterface;

class AuthService implements AuthServiceInterface
{
  public function __construct(
    private readonly UserRepositoryInterface $userRepository,
    private readonly HashServiceInterface $hashService,
    private readonly TokenServiceInterface $tokenService,
    private readonly CookieServiceInterface $cookieService,
    private readonly AuthStrategyFactoryInterface $authStrategyFactory,
    private readonly SessionServiceInterface $sessionService,
    private readonly SignUpEmail $signUpEmail,
    private readonly VerifyEmailServiceInterface $verifyEmailService,
  ) {
  }

  /**
   * Registers a new user
   *
   * Creates a new user account by hashing the password, storing the user data,
   * and sending a welcome email notification.
   *
   * @param RegisterUserData $data User registration data (email and password)
   * @return UserInterface The created user entity
   * @author Jan Ribka
   */
  public function register(RegisterUserData $data): UserInterface
  {
    $hashedPassword = $this->hashService->hash($data->password);
    $data = $data->withHashedPassword($hashedPassword);

    $user = $this->userRepository->create($data);

    $this->signUpEmail->send($user, $this->verifyEmailService->createLink(...));

    return $user;
  }

  public function attemptLogin(LoginUserData $data, DomainContextEnum $domain): array
  {
    // V cookie pude path /amin a /, podle toho jestli je administrace nebo web a budou mít různé názvy
    // Do session dat session_log_info a session_log_info_admin, kde bude název kukiny s tokenem. POkud ses zavolá refresh token?? tak se zkontroloje zda je platnost kukiny session a session nexistuje, tak se kukina smaže

    $user = $this->userRepository->getByEmail($data->email);

    $strategy = $this->authStrategyFactory->create($domain);
    $strategy->verifyUser($user, $data->password);

    return $this->login($user, $data->persistLogin, $domain);
  }

  public function attemptLogout(DomainContextEnum $domain): void
  {
    $strategy = $this->authStrategyFactory->create($domain);
    $authCookieConfig = $strategy->getCookieConfig();
    $cookieConfigData = CookieConfigData::fromAuthCookieConfig($authCookieConfig);

    $refreshToken = $this->cookieService->get($authCookieConfig->name);

    if (!$refreshToken) {
      throw new ValidationException(['noContent' => ['noCookie']], HttpStatusCode::NO_CONTENT->value);
    }

    $user = $this->userRepository->getByRefreshToken($refreshToken, $domain);

    if (!$user) {
      $this->cookieService->delete($authCookieConfig->name, $cookieConfigData);

      if ($this->sessionService->isActive()) {
        $this->sessionService->destroy();
      }

      throw new ValidationException(['forbidden' => ['noUser']], HttpStatusCode::FORBIDDEN->value);
    }

    $this->logout($user, $domain, $authCookieConfig);
  }

  public function attemptRefreshToken(array $credentials, DomainContextEnum $domain): array
  {
    $persistLogin = (bool) ($credentials['persistLogin']);

    $strategy = $this->authStrategyFactory->create($domain);
    $authCookieConfig = $strategy->getCookieConfig($persistLogin);
    $tokenConfig = $strategy->getTokenConfig();

    $result = $this->verifyRefreshToken($authCookieConfig, $tokenConfig, $domain);



    return $this->refreshToken(
      $result['user'],
      $result['refreshToken'],
      $authCookieConfig,
      $tokenConfig,
      $domain,
    );
  }

  #region Private methods

  private function login(UserInterface $user, bool $persistLogin, DomainContextEnum $domain): array
  {
    $strategy = $this->authStrategyFactory->create($domain);
    $authCookieConfig = $strategy->getCookieConfig($persistLogin);
    $tokenConfig = $strategy->getTokenConfig();

    $tokenCookie = $this->cookieService->get($authCookieConfig->name);

    $this->userRepository->logLoginAttempt($domain, $user, true);

    if ($tokenCookie) {
      // Scenario added here:
      // 1) User logs in but never uses RT and does not logout
      // 2) RT is stolen
      // 3) If 1 & 2, reuse detection is needed to clear all RTs when user logs in

      $foundToken = $this->userRepository->refreshTokenExists($tokenCookie);

      // Detected refresh token reuse!
      if (!$foundToken) {
        // Clear out ALL previous refresh tokens
        $this->userRepository->deleteRefreshTokes($user->getUuid(), $domain);
      }

      $cookieConfigData = CookieConfigData::fromAuthCookieConfig($authCookieConfig);
      $this->cookieService->delete($authCookieConfig->name, $cookieConfigData);
    }

    $userRoles = $this->userRepository->getRoleByIdUser($user->getUuid());
    $roleValueArray = UserRoleHelper::getRoleValueArrayFromUserRoles($userRoles);
    $refreshToken = $this->tokenService->createRefreshToken($user, $tokenConfig);

    $this->cookieService->set(
      $authCookieConfig->name,
      $refreshToken,
      CookieConfigData::fromAuthCookieConfig($authCookieConfig)
    );
    $this->userRepository->createRefreshToken(
      $user,
      $refreshToken,
      $domain,
      (new \DateTime())->setTimestamp($tokenConfig->expRefresh)
    );

    if (!$this->sessionService->isActive()) {
      $this->sessionService->start();
    }

    $this->sessionService->regenerate();

    $accessToken = $this->tokenService->createAccessToken($user, $roleValueArray, $tokenConfig);

    return [
      'email' => $user->getEmail(),
      'accessToken' => $accessToken,
    ];
  }

  private function logout(UserInterface $user, DomainContextEnum $domain, AuthCookieConfig $authCookieConfig): void
  {
    $this->userRepository->deleteRefreshToken($user->getUuid(), $domain);

    $config = CookieConfigData::fromAuthCookieConfig($authCookieConfig);

    $this->cookieService->delete($authCookieConfig->name, $config);

    if ($this->sessionService->isActive()) {
      $this->sessionService->destroy();
    }
  }

  public function verifyRefreshToken(AuthCookieConfig $authCookieConfig, TokenConfig $tokenConfig, DomainContextEnum $domain): array
  {
    $refreshToken = $this->cookieService->get($authCookieConfig->name);

    if (!$refreshToken) {
      throw new ValidationException(['unauthorized' => ['noCookie']], HttpStatusCode::UNAUTHORIZED->value);
    }

    $cookieConfigData = CookieConfigData::fromAuthCookieConfig($authCookieConfig);
    $this->cookieService->delete($authCookieConfig->name, $cookieConfigData);
    $user = $this->userRepository->getByRefreshToken($refreshToken, $domain);

    // Detected refresh token reuse!
    if (!$user) {
      $decoded = $this->tokenService->decodeToken($refreshToken, $tokenConfig->keyRefresh, $tokenConfig->algorithm);

      if (!$decoded) {
        throw new ValidationException(['forbidden' => ['noUser']], HttpStatusCode::FORBIDDEN->value);
      }

      $hackedEmail = $decoded->email;
      $hackedUser = $this->userRepository->getByEmail($hackedEmail);

      $this->userRepository->deleteRefreshTokes($hackedUser->getUuid(), $domain);

      throw new ValidationException(['forbidden' => ['noUser']], HttpStatusCode::FORBIDDEN->value);
    }

    return [
      'user' => $user,
      'refreshToken' => $refreshToken,
    ];
  }

  private function refreshToken(UserInterface $user, string $refreshToken, AuthCookieConfig $authCookieConfig, TokenConfig $tokenConfig, DomainContextEnum $domain): array
  {
    $decoded = $this->tokenService->decodeToken($refreshToken, $tokenConfig->keyRefresh, $tokenConfig->algorithm);

    if (!$decoded) {
      $this->userRepository->deleteRefreshToken($user->getUuid(), $domain);
    }
    if ($user->getUuid() !== $decoded->uuid) {
      throw new ValidationException(['forbidden' => ['invalidToken']], HttpStatusCode::FORBIDDEN->value);
    }

    // Refresh token was still valid
    $userRoles = $this->userRepository->getRoleByIdUser($user->getUuid());
    $roleValueArray = UserRoleHelper::getRoleValueArrayFromUserRoles($userRoles);
    $accessToken = $this->tokenService->createAccessToken($user, $roleValueArray, $tokenConfig);
    $newRefreshToken = $this->tokenService->createRefreshToken($user, $tokenConfig);

    $this->userRepository->updateRefreshToken(
      $refreshToken,
      $newRefreshToken,
      (new \DateTime())->setTimestamp($tokenConfig->expRefresh)
    );

    $this->cookieService->set(
      $authCookieConfig->name,
      $newRefreshToken,
      CookieConfigData::fromAuthCookieConfig($authCookieConfig)
    );

    if (!$this->sessionService->isActive()) {
      $this->sessionService->start();
    }
    $this->sessionService->regenerate();

    return [
      'email' => $user->getEmail(),
      'accessToken' => $accessToken,
    ];
  }


  #region
}
