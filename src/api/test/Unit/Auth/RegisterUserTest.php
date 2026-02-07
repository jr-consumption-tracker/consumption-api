<?php

declare(strict_types=1);

namespace Test\Unit\Auth;

use PHPUnit\Framework\TestCase;
use JR\Tracker\Controllers\AuthController;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorFactoryInterface;
use JR\Tracker\Service\Contract\AuthServiceInterface;
use JR\Tracker\RequestValidator\Auth\RegisterUserRequestValidator;
use JR\Tracker\Service\Contract\EntityManagerServiceInterface;
use JR\Tracker\Exception\ValidationException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Entity\User\Contract\UserInterface;
use Doctrine\ORM\EntityRepository;
use JR\Tracker\Entity\User\Implementation\User;
use JR\Tracker\Entity\User\Implementation\UserInfo;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\DataProvider;

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

        $this->controller = new AuthController(
            $this->requestValidatorFactory,
            $this->authService
        );
    }

    #[TestDox('Success registration with valid data (201 Created)')]
    public function testRegisterUserSuccess(): void
    {
        $inputData = [
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'confirmPassword' => 'Password123!',
            'login' => 'testuser'
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

        $userInfoRepo = $this->createMock(EntityRepository::class);
        $userInfoRepo->method('count')->willReturn(0);

        $this->entityManagerService->method('getRepository')
            ->willReturnMap([
                [User::class, $userRepo],
                [UserInfo::class, $userInfoRepo]
            ]);

        // Mock AuthService
        $user = $this->createMock(UserInterface::class);
        $this->authService->expects($this->once())
            ->method('registerUser')
            ->with($this->callback(function (RegisterUserData $data) use ($inputData) {
                return $data->email === $inputData['email'] && $data->hashedPassword === $inputData['password'];
            }))
            ->willReturn($user);

        $result = $this->controller->registerUser($request, $response);

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

        // Mock Repository checks (for uniqueness)
        $userRepo = $this->createMock(EntityRepository::class);
        $userRepo->method('count')->willReturnCallback(function ($criteria) {
            // Match the property names used in RegisterUserRequestValidator.php
            if (isset($criteria['Login']) && $criteria['Login'] === 'existinguser') {
                return 1;
            }
            return 0;
        });

        $userInfoRepo = $this->createMock(EntityRepository::class);
        $userInfoRepo->method('count')->willReturnCallback(function ($criteria) {
            // Match the property names used in RegisterUserRequestValidator.php
            if (isset($criteria['Email']) && $criteria['Email'] === 'existing@example.com') {
                return 1;
            }
            return 0;
        });

        $this->entityManagerService->method('getRepository')
            ->willReturnMap([
                [User::class, $userRepo],
                [UserInfo::class, $userInfoRepo]
            ]);

        try {
            $this->controller->registerUser($request, $response);
            $this->fail('ValidationException was expected for ' . $description);
        } catch (ValidationException $e) {
            $errors = $e->errors;
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
                    'login' => ''
                ],
                'expectedErrors' => [
                    'email' => 'emailRequired',
                    'password' => 'passwordRequired',
                    'confirmPassword' => 'confirmPasswordRequired'
                ],
                'description' => 'Empty mandatory fields',
                'expectedErrorMessage' => 'emailRequired, passwordRequired, confirmPasswordRequired'
            ],
            'invalid_email_format' => [
                'invalidData' => [
                    'email' => 'invalid-email',
                    'password' => 'Password123!',
                    'confirmPassword' => 'Password123!',
                    'login' => 'testuser'
                ],
                'expectedErrors' => [
                    'email' => 'emailInvalid'
                ],
                'description' => 'Email without @ or domain',
                'expectedErrorMessage' => 'emailInvalid'
            ],
            'invalid_email_tld' => [
                'invalidData' => [
                    'email' => 'test@example.c', // Regex EMAIL_END_REGEX requires 2-4 chars
                    'password' => 'Password123!',
                    'confirmPassword' => 'Password123!',
                    'login' => 'testuser'
                ],
                'expectedErrors' => [
                    'email' => 'emailInvalid'
                ],
                'description' => 'Email with short TLD (.c)',
                'expectedErrorMessage' => 'emailInvalid'
            ],
            'email_already_exists' => [
                'invalidData' => [
                    'email' => 'existing@example.com',
                    'password' => 'Password123!',
                    'confirmPassword' => 'Password123!',
                    'login' => 'testuser'
                ],
                'expectedErrors' => [
                    'email' => 'emailExists'
                ],
                'description' => 'Using an already registered email',
                'expectedErrorMessage' => 'emailExists'
            ],
            'password_too_short' => [
                'invalidData' => [
                    'email' => 'test@example.com',
                    'password' => 'P1!',
                    'confirmPassword' => 'P1!',
                    'login' => 'testuser'
                ],
                'expectedErrors' => [
                    'password' => 'passwordMinLength|8'
                ],
                'description' => 'Password under 8 characters',
                'expectedErrorMessage' => 'passwordMinLength|8'
            ],
            'password_no_lowercase' => [
                'invalidData' => [
                    'email' => 'test@example.com',
                    'password' => 'PASSWORD123!',
                    'confirmPassword' => 'PASSWORD123!',
                    'login' => 'testuser'
                ],
                'expectedErrors' => [
                    'password' => 'passwordLoweCase'
                ],
                'description' => 'Password without lowercase letter',
                'expectedErrorMessage' => 'passwordLoweCase'
            ],
            'password_no_uppercase' => [
                'invalidData' => [
                    'email' => 'test@example.com',
                    'password' => 'password123!',
                    'confirmPassword' => 'password123!',
                    'login' => 'testuser'
                ],
                'expectedErrors' => [
                    'password' => 'passwordUpperCase'
                ],
                'description' => 'Password without uppercase letter',
                'expectedErrorMessage' => 'passwordUpperCase'
            ],
            'password_no_numbers' => [
                'invalidData' => [
                    'email' => 'test@example.com',
                    'password' => 'Password!',
                    'confirmPassword' => 'Password!',
                    'login' => 'testuser'
                ],
                'expectedErrors' => [
                    'password' => 'passwordNumbers'
                ],
                'description' => 'Password without numbers',
                'expectedErrorMessage' => 'passwordNumbers'
            ],
            'passwords_dont_match' => [
                'invalidData' => [
                    'email' => 'test@example.com',
                    'password' => 'Password123!',
                    'confirmPassword' => 'Mismatched123!',
                    'login' => 'testuser'
                ],
                'expectedErrors' => [
                    'confirmPassword' => 'confirmPasswordOneOf'
                ],
                'description' => 'Password and confirmation do not match',
                'expectedErrorMessage' => 'confirmPasswordOneOf'
            ],
            'login_too_short' => [
                'invalidData' => [
                    'email' => 'test@example.com',
                    'password' => 'Password123!',
                    'confirmPassword' => 'Password123!',
                    'login' => 'abc'
                ],
                'expectedErrors' => [
                    'login' => 'loginMinLength|4'
                ],
                'description' => 'Username under 4 characters',
                'expectedErrorMessage' => 'loginMinLength|4'
            ],
            'login_invalid_start' => [
                'invalidData' => [
                    'email' => 'test@example.com',
                    'password' => 'Password123!',
                    'confirmPassword' => 'Password123!',
                    'login' => '1user'
                ],
                'expectedErrors' => [
                    'login' => 'loginStartWithLetter'
                ],
                'description' => 'Username starting with a digit',
                'expectedErrorMessage' => 'loginStartWithLetter'
            ],
            'login_already_exists' => [
                'invalidData' => [
                    'email' => 'test@example.com',
                    'password' => 'Password123!',
                    'confirmPassword' => 'Password123!',
                    'login' => 'existinguser'
                ],
                'expectedErrors' => [
                    'login' => 'userNameExists'
                ],
                'description' => 'Using an already taken username',
                'expectedErrorMessage' => 'userNameExists'
            ]
        ];
    }
}