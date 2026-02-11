<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use JR\Tracker\Mail\SignUpEmail;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Enum\UserRoleTypeEnum;
use JR\Tracker\Enum\DomainContextEnum;
use JR\Tracker\Shared\Helper\UserRoleHelper;
use JR\Tracker\DataObject\Data\LoginUserData;
use JR\Tracker\Exception\ValidationException;
use JR\Tracker\DataObject\Data\CookieConfigData;
use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\Tracker\DataObject\Config\AuthCookieConfig;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Service\Contract\AuthServiceInterface;
use JR\Tracker\Service\Contract\HashServiceInterface;
use JR\Tracker\Service\Contract\TokenServiceInterface;
use JR\Tracker\Service\Contract\CookieServiceInterface;
use JR\Tracker\Service\Contract\SessionServiceInterface;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Strategy\Contract\AuthStrategyFactoryInterface;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly HashServiceInterface $hashService,
        private readonly TokenServiceInterface $tokenService,
        private readonly CookieServiceInterface $cookieService,
        private readonly AuthStrategyFactoryInterface $authStrategyFactory,
        private readonly AuthCookieConfig $authCookieConfig,
        private readonly SessionServiceInterface $sessionService,
        private readonly SignUpEmail $signUpEmail,
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

        $this->signUpEmail->send($user);

        return $user;
    }


    public function attemptLogin(LoginUserData $data, DomainContextEnum $domain): array
    {
        // V cookie pude path /amin a /, podle toho jestli je administrace nebo web a budou mít různé názvy
        // Do session dat session_log_info a session_log_info_admin, kde bude název kukiny s tokenem. POkud ses zavolá refresh token?? tak se zkontroloje zda je platnost kukiny session a session nexistuje, tak se kukina smaže

        $user = $this->userRepository->getByEmail($data->email);

        $this->verifyUser($data, $domain, $user);

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

    // public function attemptRefreshToken(array $credentials): RefreshTokenAttemptStatusEnum|array
    // {
    //     $persistLogin = (bool) ($credentials['persistLogin'] ?? false);
    //     $refreshToken = $this->cookieService->get($this->authCookieConfig->name);

    //     if (!$refreshToken) {
    //         return RefreshTokenAttemptStatusEnum::NO_COOKIE;
    //     }

    //     $this->cookieService->delete($this->authCookieConfig->name);
    //     $user = $this->userRepository->getByRefreshToken($refreshToken);

    //     // Detected refresh token reuse!
    //     if (!$user) {
    //         $decoded = $this->tokenService->decodeToken($refreshToken, $this->tokenConfig->keyRefresh);

    //         if (!$decoded) {
    //             return RefreshTokenAttemptStatusEnum::NO_USER;
    //         }

    //         $hackedLogin = $decoded->login;
    //         $hackedUser = $this->userRepository->getByLogin($hackedLogin);
    //         // TODO: O jakou se jedna domenu, by se mohlo nacitat z url. V enumu pro domeny bude funkce na to
    //         $this->userRepository->deleteRefreshTokes($hackedUser->getId());

    //         return RefreshTokenAttemptStatusEnum::NO_USER;
    //     }

    //     return $this->refreshToken($user, $refreshToken, DomainEnum::WEB, $persistLogin);
    // }

    #region Private methods

    private function verifyUser(LoginUserData $data, DomainContextEnum $domain, ?UserInterface $user): void
    {
        $password = $data->password;

        if (!isset($user)) {
            throw new ValidationException(['unauthorized' => ['incorrectLoginPassword']], HttpStatusCode::UNAUTHORIZED->value);
        }

        if ($user->getWebLoginRestrictedUntil() && $user->getWebLoginRestrictedUntil() > new \DateTime()) {
            throw new ValidationException(['forbidden' => ['loginRestricted']], HttpStatusCode::FORBIDDEN->value);
        }

        if ($user->getIsDisabled()) {
            throw new ValidationException(['forbidden' => ['accessDenied']], HttpStatusCode::FORBIDDEN->value);
        }

        if (!$this->checkCredentials($user, $password)) {
            $this->userRepository->logLoginAttempt($domain, $user, false);

            throw new ValidationException(['unauthorized' => ['incorrectLoginPassword']], HttpStatusCode::UNAUTHORIZED->value);
        }

        $emailVerifiedAt = $user->getEmailVerifiedAt();
        if (!isset($emailVerifiedAt)) {
            throw new ValidationException(['forbidden' => ['emailNotVerified']], HttpStatusCode::FORBIDDEN->value);
        }

        $userRoles = $this->userRepository->getRoleByIdUser($user->getUuid());

        if (!UserRoleHelper::hasRole($userRoles, UserRoleTypeEnum::EDITOR)) {
            $this->userRepository->logLoginAttempt($domain, $user, false);

            throw new ValidationException(['forbidden' => ['accessDenied']], HttpStatusCode::FORBIDDEN->value);
        }
    }

    private function checkCredentials(UserInterface $user, string $password): bool
    {
        return password_verify($password, $user->getPassword());
    }

    private function login(UserInterface $user, bool $persistLogin, DomainContextEnum $domain): array
    {
        $tokenCookie = $this->cookieService->get($this->authCookieConfig->name);

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
                $this->userRepository->deleteRefreshTokes($user->getUuid());
            }

            $cookieConfigData = CookieConfigData::fromAuthCookieConfig($this->authCookieConfig);
            $this->cookieService->delete($this->authCookieConfig->name, $cookieConfigData);
        }

        $userRoles = $this->userRepository->getRoleByIdUser($user->getUuid());
        $roleValueArray = UserRoleHelper::getRoleValueArrayFromUserRoles($userRoles);
        $refreshToken = $this->tokenService->createRefreshToken($user);

        $config = new CookieConfigData(
            $this->authCookieConfig->secure,
            $this->authCookieConfig->httpOnly,
            $this->authCookieConfig->sameSite,
            $persistLogin ? $this->authCookieConfig->expires : "session",
            $this->authCookieConfig->path
        );

        $this->cookieService->set(
            $this->authCookieConfig->name,
            $refreshToken,
            $config
        );
        $this->userRepository->createRefreshToken(
            $user,
            $refreshToken,
            $domain
        );

        if (!$this->sessionService->isActive()) {
            $this->sessionService->start();
        }

        $this->sessionService->regenerate();

        $accessToken = $this->tokenService->createAccessToken($user, $roleValueArray);

        return [
            'email' => $user->getEmail(),
            'accessToken' => $accessToken
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

    // private function refreshToken(UserInterface $user, string $refreshToken, DomainEnum $domain, bool $persistLogin): RefreshTokenAttemptStatusEnum|array
    // {
    //     $decoded = $this->tokenService->decodeToken($refreshToken, $this->tokenConfig->keyRefresh);

    //     if (!$decoded) {
    //         $this->userRepository->deleteRefreshTokenByUserIdAndDomain($user->getId(), $domain);
    //     }
    //     if ($user->getUuid() !== $decoded->uuid) {
    //         return RefreshTokenAttemptStatusEnum::USER_NOT_EQUAL;
    //     }

    //     // Refresh token was still valid
    //     $userRoles = $this->userRepository->getUserRolesByUserId($user->getId());
    //     $roleValueArray = UserRoleHelper::getRoleValueArrayFromUserRoles($userRoles);
    //     $accessToken = $this->tokenService->createAccessToken($user, $roleValueArray);
    //     $newRefreshToken = $this->tokenService->createRefreshToken($user);

    //     $this->userRepository->createUpdateRefreshToken($user, $newRefreshToken, $domain);

    //     $config = new CookieConfigData(
    //         $this->authCookieConfig->secure,
    //         $this->authCookieConfig->httpOnly,
    //         $this->authCookieConfig->sameSite,
    //         $persistLogin ? $this->authCookieConfig->expires : "session",
    //         $this->authCookieConfig->path
    //     );

    //     $this->cookieService->set(
    //         $this->authCookieConfig->name,
    //         $newRefreshToken,
    //         $config
    //     );

    //     return [
    //         'login' => $user->getLogin(),
    //         'accessToken' => $accessToken
    //     ];
    // }


    #region
}
