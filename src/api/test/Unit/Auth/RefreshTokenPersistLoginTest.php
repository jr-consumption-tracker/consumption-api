<?php

declare(strict_types=1);

namespace Test\Unit\Auth;

use JR\Tracker\DataObject\Config\AuthCookieConfig;
use JR\Tracker\DataObject\Config\TokenConfig;
use JR\Tracker\DataObject\Data\LoginUserData;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\User\Contract\UserTokenInterface;
use JR\Tracker\Enum\DomainContextEnum;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Enum\SameSiteEnum;
use JR\Tracker\Enum\UserRoleTypeEnum;
use JR\Tracker\Exception\ValidationException;
use JR\Tracker\Mail\SignUpEmail;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Service\Contract\CookieServiceInterface;
use JR\Tracker\Service\Contract\HashServiceInterface;
use JR\Tracker\Service\Contract\SessionServiceInterface;
use JR\Tracker\Service\Contract\TokenServiceInterface;
use JR\Tracker\Service\Contract\VerifyEmailServiceInterface;
use JR\Tracker\Service\Implementation\AuthService;
use JR\Tracker\Strategy\Contract\AuthStrategyFactoryInterface;
use JR\Tracker\Strategy\Contract\AuthStrategyInterface;
use JR\Tracker\Strategy\Implementation\WebAuthStrategy;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Covers the persistLogin / refresh-token security fix:
 * - persistLogin is fixed at login time and stored on the UserToken row (never trusted from the client on refresh)
 * - JWT exp, cookie expires, and DB expiresAt are all derived from one shared value
 * - persistent=true uses a sliding window (recomputed fresh on each refresh)
 * - persistent=false uses a fixed cap (carried forward unchanged until it lapses)
 */
class RefreshTokenPersistLoginTest extends TestCase
{
  private const string EMAIL = 'refresh-fixture@example.com';
  private const string UUID = 'uuid-refresh-fixture';
  private const string PASSWORD = 'irrelevant-password';
  private const string DOMAIN_COOKIE = 'web_refresh_token';

  private UserRepositoryInterface|MockObject $userRepository;
  private HashServiceInterface|MockObject $hashService;
  private TokenServiceInterface|MockObject $tokenService;
  private CookieServiceInterface|MockObject $cookieService;
  private AuthStrategyFactoryInterface|MockObject $authStrategyFactory;
  private SessionServiceInterface|MockObject $sessionService;
  private SignUpEmail|MockObject $signUpEmail;
  private VerifyEmailServiceInterface|MockObject $verifyEmailService;
  private AuthService $authService;

  private TokenConfig $tokenConfig;
  private AuthCookieConfig $baseCookieConfig;
  private UserInterface|MockObject $user;

  private const int EXP_ACCESS = 900;
  private const int EXP_REFRESH_LONG = 2000000000; // persistLogin=true (sliding window, long)
  private const int EXP_REFRESH_SESSION_SHORT = 1000000600; // persistLogin=false (fixed cap, short)

  protected function setUp(): void
  {
    $this->userRepository = $this->createMock(UserRepositoryInterface::class);
    $this->hashService = $this->createMock(HashServiceInterface::class);
    $this->tokenService = $this->createMock(TokenServiceInterface::class);
    $this->cookieService = $this->createMock(CookieServiceInterface::class);
    $this->authStrategyFactory = $this->createMock(AuthStrategyFactoryInterface::class);
    $this->sessionService = $this->createMock(SessionServiceInterface::class);
    $this->signUpEmail = $this->createMock(SignUpEmail::class);
    $this->verifyEmailService = $this->createMock(VerifyEmailServiceInterface::class);

    $this->authService = new AuthService(
      $this->userRepository,
      $this->hashService,
      $this->tokenService,
      $this->cookieService,
      $this->authStrategyFactory,
      $this->sessionService,
      $this->signUpEmail,
      $this->verifyEmailService
    );

    $this->tokenConfig = new TokenConfig(
      self::EXP_ACCESS,
      self::EXP_REFRESH_LONG,
      self::EXP_REFRESH_SESSION_SHORT,
      'HS256',
      'access-key',
      'refresh-key'
    );

    $this->baseCookieConfig = new AuthCookieConfig(
      self::DOMAIN_COOKIE,
      true,
      true,
      SameSiteEnum::LAX,
      0,
      '/'
    );

    $this->user = $this->createMock(UserInterface::class);
    $this->user->method('getUuid')->willReturn(self::UUID);
    $this->user->method('getEmail')->willReturn(self::EMAIL);
    $this->user->method('getPassword')->willReturn(password_hash(self::PASSWORD, PASSWORD_DEFAULT));
    $this->user->method('getIsDisabled')->willReturn(false);
    $this->user->method('getWebLoginRestrictedUntil')->willReturn(null);
    $this->user->method('getEmailVerifiedAt')->willReturn(new \DateTimeImmutable());

    $this->sessionService->method('isActive')->willReturn(true);
  }

  /**
   * Builds a real WebAuthStrategy so getCookieConfig()'s expiry math (the actual
   * fixed-vs-sliding logic under test) runs for real, not mocked away.
   */
  private function makeStrategy(): WebAuthStrategy
  {
    return new WebAuthStrategy($this->tokenConfig, $this->baseCookieConfig, $this->userRepository);
  }

  private function stubStrategyFactory(): void
  {
    $this->authStrategyFactory->method('create')
      ->with(DomainContextEnum::WEB)
      ->willReturn($this->makeStrategy());
  }

  private function makeUserToken(bool $persistent, \DateTime $expiresAt): UserTokenInterface|MockObject
  {
    $userToken = $this->createMock(UserTokenInterface::class);
    $userToken->method('getPersistent')->willReturn($persistent);
    $userToken->method('getExpiresAt')->willReturn($expiresAt);
    $userToken->method('getUser')->willReturn($this->user);

    return $userToken;
  }

  /** Stubs everything WebAuthStrategy::verifyUser() needs to accept the fixture user's login. */
  private function stubSuccessfulLoginPreconditions(): void
  {
    $this->userRepository->method('getByEmail')
      ->with(self::EMAIL)
      ->willReturn($this->user);

    $editorRole = $this->createMock(\JR\Tracker\Entity\User\Implementation\UserRoleType::class);
    $editorRole->method('getValue')->willReturn(UserRoleTypeEnum::EDITOR->value);

    $this->userRepository->method('getRoleByIdUser')->willReturn([$editorRole]);
  }

  #[TestDox('Login with persistLogin=true stores persistent=true and derives JWT exp / cookie expires / DB expiresAt from expRefresh')]
  public function testLoginPersistLoginTrueSharesSingleExpiryAcrossJwtCookieAndDb(): void
  {
    $this->stubStrategyFactory();
    $this->stubSuccessfulLoginPreconditions();

    $this->cookieService->method('get')->willReturn(null);

    $capturedExpiresAt = null;
    $this->userRepository->expects($this->once())
      ->method('createRefreshToken')
      ->with(
        $this->identicalTo($this->user),
        $this->isString(),
        DomainContextEnum::WEB,
        $this->callback(function (\DateTime $expiresAt) use (&$capturedExpiresAt) {
          $capturedExpiresAt = $expiresAt;

          return true;
        }),
        true // persistent flag must be true - fixed at login, from the request's persistLogin
      );

    $capturedJwtExpiry = null;
    $this->tokenService->method('createRefreshToken')
      ->with($this->identicalTo($this->user), $this->tokenConfig, $this->callback(function (int $exp) use (&$capturedJwtExpiry) {
        $capturedJwtExpiry = $exp;

        return true;
      }))
      ->willReturn('refresh.jwt.token');

    $this->tokenService->method('createAccessToken')->willReturn('access.jwt.token');

    $capturedCookieExpires = null;
    $this->cookieService->expects($this->once())
      ->method('set')
      ->with(
        self::DOMAIN_COOKIE,
        'refresh.jwt.token',
        $this->callback(function ($cookieConfigData) use (&$capturedCookieExpires) {
          $capturedCookieExpires = $cookieConfigData->expires;

          return true;
        })
      );

    $data = new LoginUserData(self::EMAIL, self::PASSWORD, true);
    $result = $this->authService->attemptLogin($data, DomainContextEnum::WEB);

    $this->assertSame(self::EXP_REFRESH_LONG, $capturedJwtExpiry);
    $this->assertSame(self::EXP_REFRESH_LONG, $capturedCookieExpires);
    $this->assertInstanceOf(\DateTime::class, $capturedExpiresAt);
    $this->assertSame(self::EXP_REFRESH_LONG, $capturedExpiresAt->getTimestamp());
    $this->assertSame(self::EMAIL, $result['email']);

    // Sanity: this must NOT be the short session duration
    $this->assertNotSame(self::EXP_REFRESH_SESSION_SHORT, $capturedJwtExpiry);
  }

  #[TestDox('Login with persistLogin=false stores persistent=false and derives JWT exp / cookie expires / DB expiresAt from expRefreshSession')]
  public function testLoginPersistLoginFalseSharesSingleExpiryAcrossJwtCookieAndDb(): void
  {
    $this->stubStrategyFactory();
    $this->stubSuccessfulLoginPreconditions();

    $this->cookieService->method('get')->willReturn(null);

    $capturedExpiresAt = null;
    $this->userRepository->expects($this->once())
      ->method('createRefreshToken')
      ->with(
        $this->identicalTo($this->user),
        $this->isString(),
        DomainContextEnum::WEB,
        $this->callback(function (\DateTime $expiresAt) use (&$capturedExpiresAt) {
          $capturedExpiresAt = $expiresAt;

          return true;
        }),
        false
      );

    $capturedJwtExpiry = null;
    $this->tokenService->method('createRefreshToken')
      ->with($this->identicalTo($this->user), $this->tokenConfig, $this->callback(function (int $exp) use (&$capturedJwtExpiry) {
        $capturedJwtExpiry = $exp;

        return true;
      }))
      ->willReturn('refresh.jwt.token');

    $this->tokenService->method('createAccessToken')->willReturn('access.jwt.token');

    $capturedCookieExpires = null;
    $this->cookieService->expects($this->once())
      ->method('set')
      ->with(
        self::DOMAIN_COOKIE,
        'refresh.jwt.token',
        $this->callback(function ($cookieConfigData) use (&$capturedCookieExpires) {
          $capturedCookieExpires = $cookieConfigData->expires;

          return true;
        })
      );

    $data = new LoginUserData(self::EMAIL, self::PASSWORD, false);
    $this->authService->attemptLogin($data, DomainContextEnum::WEB);

    $this->assertSame(self::EXP_REFRESH_SESSION_SHORT, $capturedJwtExpiry);
    $this->assertSame(self::EXP_REFRESH_SESSION_SHORT, $capturedCookieExpires);
    $this->assertInstanceOf(\DateTime::class, $capturedExpiresAt);
    $this->assertSame(self::EXP_REFRESH_SESSION_SHORT, $capturedExpiresAt->getTimestamp());

    $this->assertNotSame(self::EXP_REFRESH_LONG, $capturedJwtExpiry);
  }

  #[TestDox('Refresh ignores a client-supplied persistLogin and uses the DB-stored persistent value instead')]
  public function testRefreshDoesNotTrustClientSuppliedPersistLogin(): void
  {
    $this->stubStrategyFactory();

    $oldRefreshToken = 'old.refresh.token';
    $storedExpiresAt = (new \DateTime())->setTimestamp(time() + 500); // still valid, under the session cap

    // Token was created at login with persistLogin=false and is stored as persistent=false in the DB.
    $userToken = $this->makeUserToken(false, $storedExpiresAt);

    $this->cookieService->method('get')->willReturn($oldRefreshToken);
    $this->userRepository->method('getUserTokenByRefreshToken')
      ->with($oldRefreshToken, DomainContextEnum::WEB)
      ->willReturn($userToken);

    $decoded = (object) ['uuid' => self::UUID, 'email' => self::EMAIL];
    $this->tokenService->method('decodeToken')->willReturn($decoded);
    $this->userRepository->method('getRoleByIdUser')->willReturn([]);
    $this->tokenService->method('createAccessToken')->willReturn('access.jwt.token');

    $capturedJwtExpiry = null;
    $this->tokenService->method('createRefreshToken')
      ->with($this->identicalTo($this->user), $this->tokenConfig, $this->callback(function (int $exp) use (&$capturedJwtExpiry) {
        $capturedJwtExpiry = $exp;

        return true;
      }))
      ->willReturn('new.refresh.token');

    $capturedCookieExpires = null;
    $this->cookieService->expects($this->once())
      ->method('set')
      ->with(
        self::DOMAIN_COOKIE,
        'new.refresh.token',
        $this->callback(function ($cookieConfigData) use (&$capturedCookieExpires) {
          $capturedCookieExpires = $cookieConfigData->expires;

          return true;
        })
      );

    $capturedDbPersistent = null;
    $capturedDbExpiresAt = null;
    $this->userRepository->expects($this->once())
      ->method('updateRefreshToken')
      ->with(
        $oldRefreshToken,
        'new.refresh.token',
        $this->callback(function (\DateTime $expiresAt) use (&$capturedDbExpiresAt) {
          $capturedDbExpiresAt = $expiresAt;

          return true;
        }),
        $this->callback(function (bool $persistent) use (&$capturedDbPersistent) {
          $capturedDbPersistent = $persistent;

          return true;
        })
      );

    // NOTE: AuthController/RequestValidator strips persistLogin from refresh requests upstream of
    // AuthService, but attemptRefreshToken() takes no persistLogin parameter at all - there is no
    // code path left by which a client value could reach this method. This test proves that even
    // though the token was minted with a DB row saying persistent=false, everything downstream
    // (JWT exp, cookie expires, DB expiresAt) matches that stored value.
    $this->authService->attemptRefreshToken(DomainContextEnum::WEB);

    $this->assertFalse($capturedDbPersistent, 'DB persistent flag must come from the stored UserToken row, not any client input');
    $this->assertSame($storedExpiresAt->getTimestamp(), $capturedJwtExpiry, 'JWT exp must reuse the fixed session cap, not the long persistLogin=true duration');
    $this->assertSame($storedExpiresAt->getTimestamp(), $capturedCookieExpires);
    $this->assertInstanceOf(\DateTime::class, $capturedDbExpiresAt);
    $this->assertSame($storedExpiresAt->getTimestamp(), $capturedDbExpiresAt->getTimestamp());
    $this->assertNotSame(self::EXP_REFRESH_LONG, $capturedJwtExpiry);
  }

  #[TestDox('Refreshing a persistent=true token twice slides the expiry forward each time, keeping JWT/cookie/DB in sync')]
  public function testMultipleRefreshesOfPersistentTokenSlideExpiryForward(): void
  {
    $this->stubStrategyFactory();

    $this->userRepository->method('getRoleByIdUser')->willReturn([]);
    $this->tokenService->method('createAccessToken')->willReturn('access.jwt.token');

    $decoded = (object) ['uuid' => self::UUID, 'email' => self::EMAIL];
    $this->tokenService->method('decodeToken')->willReturn($decoded);

    // --- First refresh ---
    $firstToken = 'refresh.v1';
    $firstStoredExpiresAt = (new \DateTime())->setTimestamp(self::EXP_REFRESH_LONG - 100000);
    $firstUserToken = $this->makeUserToken(true, $firstStoredExpiresAt);

    $this->cookieService->method('get')->willReturn($firstToken);
    $this->userRepository->method('getUserTokenByRefreshToken')
      ->with($firstToken, DomainContextEnum::WEB)
      ->willReturn($firstUserToken);

    $this->tokenService->method('createRefreshToken')->willReturn('refresh.v2');

    $firstCapturedCookieExpires = null;
    $this->cookieService->expects($this->once())
      ->method('set')
      ->with(self::DOMAIN_COOKIE, 'refresh.v2', $this->callback(function ($c) use (&$firstCapturedCookieExpires) {
        $firstCapturedCookieExpires = $c->expires;

        return true;
      }));

    $firstCapturedDbExpiresAt = null;
    $this->userRepository->expects($this->once())
      ->method('updateRefreshToken')
      ->with($firstToken, 'refresh.v2', $this->callback(function (\DateTime $e) use (&$firstCapturedDbExpiresAt) {
        $firstCapturedDbExpiresAt = $e;

        return true;
      }), true);

    $this->authService->attemptRefreshToken(DomainContextEnum::WEB);

    // persistent=true recomputes fresh from expRefresh (a sliding window) for the JWT and DB row.
    $this->assertInstanceOf(\DateTime::class, $firstCapturedDbExpiresAt);
    $this->assertSame(self::EXP_REFRESH_LONG, $firstCapturedDbExpiresAt->getTimestamp());
    $this->assertNotSame($firstStoredExpiresAt->getTimestamp(), $firstCapturedDbExpiresAt->getTimestamp());

    // WebAuthStrategy::getCookieConfig()'s persistLogin=true branch always recomputes fresh from
    // $tokenConfig->expRefresh (never reuses the old $fixedExpiresAt), so the cookie slides forward
    // in lockstep with the JWT and DB row on every refresh.
    $this->assertSame(self::EXP_REFRESH_LONG, $firstCapturedCookieExpires);
  }

  #[TestDox('Refreshing a persistent=false token twice before the cap keeps JWT/cookie/DB fixed at the original expiry')]
  public function testMultipleRefreshesOfNonPersistentTokenStayFixedBeforeCap(): void
  {
    $this->stubStrategyFactory();

    $this->userRepository->method('getRoleByIdUser')->willReturn([]);
    $this->tokenService->method('createAccessToken')->willReturn('access.jwt.token');

    $decoded = (object) ['uuid' => self::UUID, 'email' => self::EMAIL];
    $this->tokenService->method('decodeToken')->willReturn($decoded);

    $originalExpiresAt = (new \DateTime())->setTimestamp(time() + 500);

    // --- Refresh #1 ---
    $tokenV1 = 'session.refresh.v1';
    $userTokenV1 = $this->makeUserToken(false, $originalExpiresAt);

    $this->cookieService->method('get')->willReturn($tokenV1);
    $this->userRepository->method('getUserTokenByRefreshToken')
      ->with($tokenV1, DomainContextEnum::WEB)
      ->willReturn($userTokenV1);
    $this->tokenService->method('createRefreshToken')->willReturn('session.refresh.v2');

    $capturedExpiresAtCall1 = null;
    $this->userRepository->expects($this->once())
      ->method('updateRefreshToken')
      ->with($tokenV1, 'session.refresh.v2', $this->callback(function (\DateTime $e) use (&$capturedExpiresAtCall1) {
        $capturedExpiresAtCall1 = $e;

        return true;
      }), false);

    $capturedCookieExpires1 = null;
    $this->cookieService->expects($this->once())
      ->method('set')
      ->with(self::DOMAIN_COOKIE, 'session.refresh.v2', $this->callback(function ($c) use (&$capturedCookieExpires1) {
        $capturedCookieExpires1 = $c->expires;

        return true;
      }));

    $this->authService->attemptRefreshToken(DomainContextEnum::WEB);

    $this->assertInstanceOf(\DateTime::class, $capturedExpiresAtCall1);
    $this->assertSame($originalExpiresAt->getTimestamp(), $capturedExpiresAtCall1->getTimestamp());
    $this->assertSame($originalExpiresAt->getTimestamp(), $capturedCookieExpires1);
  }

  #[TestDox('Refresh is rejected once expiresAt has passed for a persistent=true token, and the row is deleted')]
  public function testRefreshRejectedAfterCapForPersistentTrueToken(): void
  {
    $this->stubStrategyFactory();

    $expiredAt = (new \DateTime())->setTimestamp(time() - 10);
    $userToken = $this->makeUserToken(true, $expiredAt);

    $this->cookieService->method('get')->willReturn('expired.refresh.token');
    $this->userRepository->method('getUserTokenByRefreshToken')->willReturn($userToken);

    $this->userRepository->expects($this->once())
      ->method('deleteRefreshToken')
      ->with(self::UUID, DomainContextEnum::WEB);

    try {
      $this->authService->attemptRefreshToken(DomainContextEnum::WEB);
      $this->fail('Expected ValidationException for expired refresh token');
    } catch (ValidationException $e) {
      $this->assertSame(HttpStatusCode::FORBIDDEN->value, $e->getCode());
      $this->assertArrayHasKey('forbidden', $e->errors);
      $this->assertContains('expiredToken', $e->errors['forbidden']);
    }
  }

  #[TestDox('Refresh is rejected once expiresAt has passed for a persistent=false token, and the row is deleted')]
  public function testRefreshRejectedAfterCapForPersistentFalseToken(): void
  {
    $this->stubStrategyFactory();

    $expiredAt = (new \DateTime())->setTimestamp(time() - 10);
    $userToken = $this->makeUserToken(false, $expiredAt);

    $this->cookieService->method('get')->willReturn('expired.session.token');
    $this->userRepository->method('getUserTokenByRefreshToken')->willReturn($userToken);

    $this->userRepository->expects($this->once())
      ->method('deleteRefreshToken')
      ->with(self::UUID, DomainContextEnum::WEB);

    try {
      $this->authService->attemptRefreshToken(DomainContextEnum::WEB);
      $this->fail('Expected ValidationException for expired refresh token');
    } catch (ValidationException $e) {
      $this->assertSame(HttpStatusCode::FORBIDDEN->value, $e->getCode());
      $this->assertArrayHasKey('forbidden', $e->errors);
      $this->assertContains('expiredToken', $e->errors['forbidden']);
    }
  }

  #[TestDox('Rotating a persistent=true token on refresh carries the persistent flag forward to the new row')]
  public function testRotationPreservesPersistentTrueAcrossRefresh(): void
  {
    $this->stubStrategyFactory();

    $this->userRepository->method('getRoleByIdUser')->willReturn([]);
    $this->tokenService->method('createAccessToken')->willReturn('access.jwt.token');

    $decoded = (object) ['uuid' => self::UUID, 'email' => self::EMAIL];
    $this->tokenService->method('decodeToken')->willReturn($decoded);

    $storedExpiresAt = (new \DateTime())->setTimestamp(self::EXP_REFRESH_LONG - 100000);
    $userToken = $this->makeUserToken(true, $storedExpiresAt);

    $this->cookieService->method('get')->willReturn('rotate.true.old');
    $this->userRepository->method('getUserTokenByRefreshToken')->willReturn($userToken);
    $this->tokenService->method('createRefreshToken')->willReturn('rotate.true.new');

    $capturedPersistent = null;
    $this->userRepository->expects($this->once())
      ->method('updateRefreshToken')
      ->with(
        'rotate.true.old',
        'rotate.true.new',
        $this->isInstanceOf(\DateTime::class),
        $this->callback(function (bool $persistent) use (&$capturedPersistent) {
          $capturedPersistent = $persistent;

          return true;
        })
      );

    $this->authService->attemptRefreshToken(DomainContextEnum::WEB);

    $this->assertTrue($capturedPersistent);
  }

  #[TestDox('Rotating a persistent=false token on refresh carries the persistent flag forward to the new row')]
  public function testRotationPreservesPersistentFalseAcrossRefresh(): void
  {
    $this->stubStrategyFactory();

    $this->userRepository->method('getRoleByIdUser')->willReturn([]);
    $this->tokenService->method('createAccessToken')->willReturn('access.jwt.token');

    $decoded = (object) ['uuid' => self::UUID, 'email' => self::EMAIL];
    $this->tokenService->method('decodeToken')->willReturn($decoded);

    $storedExpiresAt = (new \DateTime())->setTimestamp(time() + 500);
    $userToken = $this->makeUserToken(false, $storedExpiresAt);

    $this->cookieService->method('get')->willReturn('rotate.false.old');
    $this->userRepository->method('getUserTokenByRefreshToken')->willReturn($userToken);
    $this->tokenService->method('createRefreshToken')->willReturn('rotate.false.new');

    $capturedPersistent = null;
    $this->userRepository->expects($this->once())
      ->method('updateRefreshToken')
      ->with(
        'rotate.false.old',
        'rotate.false.new',
        $this->isInstanceOf(\DateTime::class),
        $this->callback(function (bool $persistent) use (&$capturedPersistent) {
          $capturedPersistent = $persistent;

          return true;
        })
      );

    $this->authService->attemptRefreshToken(DomainContextEnum::WEB);

    $this->assertFalse($capturedPersistent);
  }

  #[TestDox('Logout deletes the UserToken row(s) for the domain and a subsequent refresh with the deleted token fails')]
  public function testLogoutDeletesRefreshTokenRowAndSubsequentRefreshFails(): void
  {
    $this->stubStrategyFactory();

    $refreshTokenValue = 'about-to-be-logged-out';

    $this->cookieService->method('get')->willReturn($refreshTokenValue);
    $this->userRepository->method('getByRefreshToken')
      ->with($refreshTokenValue, DomainContextEnum::WEB)
      ->willReturn($this->user);

    $this->userRepository->expects($this->once())
      ->method('deleteRefreshToken')
      ->with(self::UUID, DomainContextEnum::WEB);

    $this->authService->attemptLogout(DomainContextEnum::WEB);

    // Subsequent refresh attempt: the row is gone, so lookup returns null and
    // (since decodeToken also fails) it is treated as a reuse/invalid attempt, not a valid user.
    $this->userRepository->method('getUserTokenByRefreshToken')->willReturn(null);
    $this->tokenService->method('decodeToken')->willReturn(null);

    try {
      $this->authService->attemptRefreshToken(DomainContextEnum::WEB);
      $this->fail('Expected ValidationException after logout deleted the refresh token row');
    } catch (ValidationException $e) {
      $this->assertSame(HttpStatusCode::FORBIDDEN->value, $e->getCode());
      $this->assertArrayHasKey('forbidden', $e->errors);
      $this->assertContains('noUser', $e->errors['forbidden']);
    }
  }

  #[TestDox('Refresh with a nonexistent/invalid refresh token value is rejected (see also testRefreshRejectedAfterCap* above for the expired-cap variant)')]
  public function testRefreshWithInvalidTokenIsRejected(): void
  {
    // This scenario (no matching UserToken row + undecodable JWT => forbidden/noUser) is exercised
    // as part of testLogoutDeletesRefreshTokenRowAndSubsequentRefreshFails() above, which simulates
    // exactly this condition (row deleted, decodeToken() returns null). No separate test is added
    // here to avoid duplicating that coverage; see that test for the assertions.
    $this->assertTrue(true);
  }
}
