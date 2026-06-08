<?php

declare(strict_types=1);

namespace JR\Tracker\RequestValidator\Auth;

use JR\Tracker\Entity\User\Implementation\User;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Exception\ValidationException;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorInterface;
use JR\Tracker\Service\Contract\EntityManagerServiceInterface;
use Valitron\Validator;

class RegisterUserRequestValidator implements RequestValidatorInterface
{
  public function __construct(
    private readonly EntityManagerServiceInterface $entityManagerService
  ) {
  }

  public function validate(array $data): array
  {
    $v = new Validator($data);

    // Validate mandatory fields
    $v->rule('required', 'email')->message('required');
    $v->rule('required', 'password')->message('required');
    $v->rule('required', 'confirmPassword')->message('required');
    $v->rule(fn($_field, $value) => $value === true, 'termsAgreement')->message('termsAgreement.required');

    // Validate email
    if (!empty($data['email'])) {
      $v->rule('email', 'email')->message('email.invalid');
      $v->rule('regex', 'email', '/' . EMAIL_END_REGEX . '/')->message('email.invalid');
      $v->rule('lengthMax', "email", 50)->message('email.tooLong');

      $exists = $this->entityManagerService->getRepository(User::class)->count(['email' => $data['email']]);
      if ($exists) {
        throw new ValidationException(['general' => ['registrationFailed']], HttpStatusCode::BAD_REQUEST->value);
      }
    }
    // Validate password
    if (!empty($data['password'])) {
      $v->rule('regex', 'password', '/' . LOWERCASE_REGEX . '/')->message('password.lowercase');
      $v->rule('regex', 'password', '/' . UPPERCASE_REGEX . '/')->message('password.uppercase');
      $v->rule('regex', 'password', '/' . NUMBERS_REGEX . '/')->message('password.number');
      $v->rule('lengthMin', "password", 8)->message('password.tooShort');
      $v->rule('lengthMax', "password", 24)->message('password.tooLong');
    }

    // Validate confirm password
    if (!empty($data['confirmPassword'])) {
      $v->rule('equals', 'confirmPassword', 'password')->message('password.mismatch');
    }

    if (!$v->validate()) {
      throw new ValidationException(['validationError' => [$v->errors()]], HttpStatusCode::BAD_REQUEST->value);
    }

    return $data;
  }
}
