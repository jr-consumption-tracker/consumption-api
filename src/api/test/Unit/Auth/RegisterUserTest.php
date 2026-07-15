<?php

declare(strict_types=1);

namespace Test\Unit\Auth;

use Doctrine\ORM\EntityRepository;
use JR\Tracker\Controller\Web\AuthController;
use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\User\Implementation\User;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Exception\ValidationException;
use JR\Tracker\RequestValidator\Auth\RegisterUserRequestValidator;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorFactoryInterface;
use JR\Tracker\Service\Contract\AuthServiceInterface;
use JR\Tracker\Service\Contract\EntityManagerServiceInterface;
use JR\Tracker\Shared\ResponseFormatter\ResponseFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class EntityManagerServiceMock implements EntityManagerServiceInterface
{
  abstract public function getRepository(string $entityName);
}

class RegisterUserTest extends TestCase
{
  private RequestValidatorFactoryInterface|MockObject $requestValidatorFactory;
  private AuthServiceInterface|MockObject $authService;
  private EntityManagerServiceMock|MockObject $entityManagerService;
  private AuthController $controller;

  protected function setUp(): void
  {
    $this->requestValidatorFactory = $this->createMock(RequestValidatorFactoryInterface::class);
    $this->authService = $this->createMock(AuthServiceInterface::class);
    $this->entityManagerService = $this->createMock(EntityManagerServiceMock::class);
    $responseFormatter = $this->createMock(ResponseFormatter::class);

    $this->controller = new AuthController(
      $this->requestValidatorFactory,
      $this->authService,
      $responseFormatter
    );
  }

  #[TestDox('Success registration with valid data (201 Created)')]
  public function testRegisterUserSuccess(): void
  {
    $inputData = [
      'email' => 'test@example.com',
      'password' => 'Password123!',
      'confirmPassword' => 'Password123!',
    ];

    $request = $this->createMock(Request::class);
    $request->method('getParsedBody')->willReturn($inputData);

    $response = $this->createMock(Response::class);
    $response->method('withStatus')->with(HttpStatusCode::CREATED->value)->willReturn($response);

    // Mock Validator
    $validator = new RegisterUserRequestValidator($this->entityManagerService);
    $this->requestValidatorFactory->method('make')
      ->with(RegisterUserRequestValidator::class)
      ->willReturn($validator);

    // Mock Repository checks in validator
    $userRepo = $this->createMock(EntityRepository::class);
    $userRepo->method('count')->willReturn(0);

    $this->entityManagerService->method('getRepository')
      ->willReturnMap([
        [User::class, $userRepo],
      ]);

    // Mock AuthService
    $user = $this->createMock(UserInterface::class);
    $this->authService->expects($this->once())
      ->method('register')
      ->with($this->callback(function (RegisterUserData $data) use ($inputData) {
        return $data->email === $inputData['email'] && $data->password === $inputData['password'];
      }))
      ->willReturn($user);

    $result = $this->controller->register($request, $response);

    $this->assertSame($response, $result);
  }

  #[DataProvider('invalidRegistrationDataProvider')]
  #[TestDox('Validation: $description (Expected error: $expectedErrorMessage)')]
  public function testRegisterUserValidationFailures(array $invalidData, array $expectedErrors, string $description, string $expectedErrorMessage): void
  {
    $request = $this->createMock(Request::class);
    $request->method('getParsedBody')->willReturn($invalidData);

    $response = $this->createMock(Response::class);

    // Mock Validator
    $validator = new RegisterUserRequestValidator($this->entityManagerService);
    $this->requestValidatorFactory->method('make')
      ->with(RegisterUserRequestValidator::class)
      ->willReturn($validator);

    // Mock Repository checks
    $userRepo = $this->createMock(EntityRepository::class);
    $userRepo->method('count')->willReturnCallback(function ($criteria) {
      if (isset($criteria['email']) && $criteria['email'] === 'existing@example.com') {
        return 1;
      }

      return 0;
    });

    $this->entityManagerService->method('getRepository')
      ->willReturnMap([
        [User::class, $userRepo],
      ]);

    try {
      $this->controller->register($request, $response);
      $this->fail('ValidationException was expected for ' . $description);
    } catch (ValidationException $e) {
      // Matches the shape read by the frontend's useRegisterForm.ts: data.validationError[0][field][0].
      // The 'general' => 'registrationFailed' case is thrown separately, outside the validationError
      // wrapper, and keeps the old flat shape - it was not affected by the error-shape migration.
      $errors = isset($e->errors['validationError']) ? $e->errors['validationError'][0] : $e->errors;
      foreach ($expectedErrors as $field => $message) {
        $this->assertArrayHasKey($field, $errors, "Field '$field' missing in errors for " . $description);
        $this->assertContains($message, $errors[$field], "Message '$message' not found for field '$field'");
      }
    }
  }

  public static function invalidRegistrationDataProvider(): array
  {
    return [
      'missing_fields' => [
        'invalidData' => [
          'email' => '',
          'password' => '',
          'confirmPassword' => '',
        ],
        'expectedErrors' => [
          'email' => 'required',
          'password' => 'required',
          'confirmPassword' => 'required',
        ],
        'description' => 'Empty mandatory fields',
        'expectedErrorMessage' => 'required, required, required',
      ],
      'invalid_email_format' => [
        'invalidData' => [
          'email' => 'invalid-email',
          'password' => 'Password123!',
          'confirmPassword' => 'Password123!',
        ],
        'expectedErrors' => [
          'email' => 'email.invalid',
        ],
        'description' => 'Email without @ or domain',
        'expectedErrorMessage' => 'email.invalid',
      ],
      'invalid_email_tld' => [
        'invalidData' => [
          'email' => 'test@example.c',
          'password' => 'Password123!',
          'confirmPassword' => 'Password123!',
        ],
        'expectedErrors' => [
          'email' => 'email.invalid',
        ],
        'description' => 'Email with short TLD (.c)',
        'expectedErrorMessage' => 'email.invalid',
      ],
      'email_already_exists' => [
        'invalidData' => [
          'email' => 'existing@example.com',
          'password' => 'Password123!',
          'confirmPassword' => 'Password123!',
        ],
        'expectedErrors' => [
          'general' => 'registrationFailed',
        ],
        'description' => 'Using an already registered email',
        'expectedErrorMessage' => 'registrationFailed',
      ],
      'password_too_short' => [
        'invalidData' => [
          'email' => 'test@example.com',
          'password' => 'P1!',
          'confirmPassword' => 'P1!',
        ],
        'expectedErrors' => [
          'password' => 'password.tooShort',
        ],
        'description' => 'Password under 8 characters',
        'expectedErrorMessage' => 'password.tooShort',
      ],
      'password_no_lowercase' => [
        'invalidData' => [
          'email' => 'test@example.com',
          'password' => 'PASSWORD123!',
          'confirmPassword' => 'PASSWORD123!',
        ],
        'expectedErrors' => [
          'password' => 'password.lowercase',
        ],
        'description' => 'Password without lowercase letter',
        'expectedErrorMessage' => 'password.lowercase',
      ],
      'password_no_uppercase' => [
        'invalidData' => [
          'email' => 'test@example.com',
          'password' => 'password123!',
          'confirmPassword' => 'password123!',
        ],
        'expectedErrors' => [
          'password' => 'password.uppercase',
        ],
        'description' => 'Password without uppercase letter',
        'expectedErrorMessage' => 'password.uppercase',
      ],
      'password_no_numbers' => [
        'invalidData' => [
          'email' => 'test@example.com',
          'password' => 'Password!',
          'confirmPassword' => 'Password!',
        ],
        'expectedErrors' => [
          'password' => 'password.number',
        ],
        'description' => 'Password without numbers',
        'expectedErrorMessage' => 'password.number',
      ],
      'passwords_dont_match' => [
        'invalidData' => [
          'email' => 'test@example.com',
          'password' => 'Password123!',
          'confirmPassword' => 'Mismatched123!',
        ],
        'expectedErrors' => [
          'confirmPassword' => 'password.mismatch',
        ],
        'description' => 'Password and confirmation do not match',
        'expectedErrorMessage' => 'password.mismatch',
      ],
    ];
  }
}
