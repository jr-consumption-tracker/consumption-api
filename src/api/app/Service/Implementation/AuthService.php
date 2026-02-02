<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use JR\Tracker\Mail\SignUpEmail;
use JR\ChefsDiary\Enums\DomainEnum;
use JR\ChefsDiary\Mail\TwoFactorAuthEmail;
use JR\ChefsDiary\Enums\AuthAttemptStatusEnum;
use JR\ChefsDiary\Shared\Helpers\BooleanHelper;
use JR\ChefsDiary\Enums\LogoutAttemptStatusEnum;
use JR\ChefsDiary\Shared\Helpers\UserRoleHelper;
use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\ChefsDiary\DataObjects\Configs\TokenConfig;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\ChefsDiary\DataObjects\Data\CookieConfigData;
use JR\Tracker\Service\Contract\AuthServiceInterface;
use JR\Tracker\Service\Contract\HashServiceInterface;
use JR\ChefsDiary\Enums\RefreshTokenAttemptStatusEnum;
use JR\ChefsDiary\DataObjects\Configs\AuthCookieConfig;
use JR\ChefsDiary\Services\Contract\TokenServiceInterface;
use JR\ChefsDiary\Services\Contract\CookieServiceInterface;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\ChefsDiary\Services\Contract\SessionServiceInterface;
use JR\ChefsDiary\Services\Contract\UserLoginCodeServiceInterface;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly HashServiceInterface $hashService,
        // private readonly TokenServiceInterface $tokenService,
        // private readonly CookieServiceInterface $cookieService,
        // private readonly AuthCookieConfig $authCookieConfig,
        // private readonly TokenConfig $tokenConfig,
        // private readonly SessionServiceInterface $sessionService,
        private readonly SignUpEmail $signUpEmail,
        // private readonly TwoFactorAuthEmail $twoFactorAuthEmail,
        // private readonly UserLoginCodeServiceInterface $userLoginCodeService
    ) {
    }

    /**
     * Register user
     * @param \JR\Tracker\DataObject\Data\RegisterUserData $data
     * @return \JR\Tracker\Entity\User\Contract\UserInterface
     * @author Jan Ribka
     */
    public function registerUser(RegisterUserData $data): UserInterface
    {
        $data->hashedPassword = $this->hashService->hash($data->hashedPassword);
        $user = $this->userRepository->createUser($data);

        $this->signUpEmail->send($user);

        return $user;
    }

    // /**
    //  * Attempt to login
    //  * @param array $credentials
    //  * @return \JR\ChefsDiary\Enums\AuthAttemptStatusEnum|array
    //  * @author Jan Ribka
    //  */
    // public function attemptLogin(array $credentials): AuthAttemptStatusEnum|array
    // {
    //     // web 
    //     //	accessToken 20-30 min
    //     //	refresh token 8 dnů s prodlužováním

    //     // Administraci
    //     //	accessToken 10 min
    //     //	refreshToken 1 hodina s prodlužování

    //     // Podle url budu rozlišovat jak nastavit token.
    //     // V cookie pude path /amin a /, podle toho jestli je administrace nebo web a budou mít různé názvy
    //     // Do session dat session_log_info a session_log_info_admin, kde bude název kukiny s tokenem. POkud ses zavolá refresh token?? tak se zkontroloje zda je platnost kukiny session a session nexistuje, tak se kukina smaže
    //     // Udělat Url helper, který bude zjištovat zda je v url admin
    //     $parseBoolean = BooleanHelper::parse();

    //     $login = $credentials['login'];
    //     $password = $credentials['password'];
    //     $persistLogin = $parseBoolean(($credentials['persistLogin'] ?? false));
    //     $user = $this->userRepository->getByLogin($login);

    //     if (!$user) {
    //         return AuthAttemptStatusEnum::FAILED;
    //     }

    //     if ($user->getIsDisabled()) {
    //         return AuthAttemptStatusEnum::DISABLED;
    //     }

    //     if (!$this->checkCredentials($user, $password)) {
    //         $this->userRepository->logLoginAttempt($user, false);

    //         return AuthAttemptStatusEnum::FAILED;
    //     }

    //     if ($user->getTwoFactor()) {
    //         $this->login2FA($user);

    //         return AuthAttemptStatusEnum::TWO_FACTOR;
    //     }

    //     return $this->login($user, $persistLogin, DomainEnum::WEB);
    // }

    // public function attemptTwoFactorLogin(array $data): bool
    // {
    //     // $userId = $this->session->get('2fa');

    //     // if (!$userId) {
    //     //     return false;
    //     // }

    //     // $user = $this->userRepository->getById($userId);

    //     // if (!$user || $user->getEmail() !== $data['email']) {
    //     //     return false;
    //     // }

    //     // if (!$this->userLoginCodeService->verify($user, $data['code'])) {
    //     //     return false;
    //     // }

    //     // $this->session->forget('2fa');

    //     // $this->logIn($user);

    //     // $this->userLoginCodeService->deactivateAllActiveCodes($user);

    //     // return true;

    //     return true;
    // }

    // public function attemptLogout(): LogoutAttemptStatusEnum
    // {
    //     $refreshToken = $this->cookieService->get($this->authCookieConfig->name);

    //     if (!$refreshToken) {
    //         return LogoutAttemptStatusEnum::NO_COOKIE;
    //     }

    //     $user = $this->userRepository->getByRefreshToken($refreshToken);

    //     if (!$user) {
    //         $this->cookieService->delete($this->authCookieConfig->name);

    //         return LogoutAttemptStatusEnum::NO_USER;
    //     }

    //     return $this->logout($user, DomainEnum::WEB);
    // }

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

    // #region Private methods
    // private function checkCredentials(UserInterface $user, string $password): bool
    // {
    //     return password_verify($password, $user->getPassword());
    // }

    // private function login(UserInterface $user, bool $persistLogin, DomainEnum $domain): array
    // {
    //     $userRoles = $this->userRepository->getUserRolesByUserId($user->getId());
    //     $roleValueArray = UserRoleHelper::getRoleValueArrayFromUserRoles($userRoles);
    //     $refreshToken = $this->tokenService->createRefreshToken($user);
    //     $tokenCookie = $this->cookieService->get($this->authCookieConfig->name);

    //     $this->userRepository->logLoginAttempt($user, true);

    //     if ($tokenCookie) {
    //         // Scenario added here: 
    //         // 1) User logs in but never uses RT and does not logout 
    //         // 2) RT is stolen
    //         // 3) If 1 & 2, reuse detection is needed to clear all RTs when user logs in

    //         $foundToken = $this->userRepository->refreshTokenExists($tokenCookie);

    //         // Detected refresh token reuse!
    //         if (!$foundToken) {
    //             // Clear out ALL previous refresh tokens
    //             $this->userRepository->deleteRefreshTokes($user->getId());
    //         }

    //         $this->cookieService->delete($this->authCookieConfig->name);
    //     }

    //     $config = new CookieConfigData(
    //         $this->authCookieConfig->secure,
    //         $this->authCookieConfig->httpOnly,
    //         $this->authCookieConfig->sameSite,
    //         $this->authCookieConfig->expires,
    //         $this->authCookieConfig->path
    //     );

    //     $this->cookieService->set(
    //         $this->authCookieConfig->name,
    //         $refreshToken,
    //         $config
    //     );
    //     $this->userRepository->createUpdateRefreshToken(
    //         $user,
    //         $refreshToken,
    //         $domain
    //     );

    //     // if ($persistLogin) {
    //     //     // TODO: Nazev bude z configu a buse se tvorit jenom pokud nen9 persist
    //     //     // $this->sessionService->put('session_log_info', uniqid());
    //     //     $this->sessionService->start();
    //     // } else {
    //     //     // $config = new CookieConfigData(
    //     //     //     $this->authCookieConfig->secure,
    //     //     //     $this->authCookieConfig->httpOnly,
    //     //     //     $this->authCookieConfig->sameSite,
    //     //     //     $this->authCookieConfig->expires,
    //     //     //     $this->authCookieConfig->path
    //     //     // );

    //     //     // $this->cookieService->set(
    //     //     //     $this->authCookieConfig->name,
    //     //     //     $refreshToken,
    //     //     //     $config
    //     //     // );

    //     //     $this->cookieService->start();
    //     // }

    //     $accessToken = $this->tokenService->createAccessToken($user, $roleValueArray);

    //     return [
    //         'login' => $user->getLogin(),
    //         'accessToken' => $accessToken
    //     ];
    // }

    // private function login2FA(UserInterface $user): void
    // {
    //     // TODO: POdobn2 jako u příhlášení

    //     $userInfo = $this->userRepository->getUserInfoByUserId($user->getId());

    //     $this->twoFactorAuthEmail->send($this->userLoginCodeService->generate($user), $userInfo);
    // }

    // private function logout(UserInterface $user, DomainEnum $domain): LogoutAttemptStatusEnum
    // {
    //     $this->userRepository->deleteRefreshTokenByUserIdAndDomain($user->getId(), $domain);

    //     $config = new CookieConfigData(
    //         $this->authCookieConfig->secure,
    //         $this->authCookieConfig->httpOnly,
    //         $this->authCookieConfig->sameSite,
    //         $this->authCookieConfig->expires,
    //         $this->authCookieConfig->path
    //     );

    //     $this->cookieService->delete($this->authCookieConfig->name, $config);

    //     return LogoutAttemptStatusEnum::LOGOUT_SUCCESS;
    // }

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
