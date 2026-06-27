<?php

declare(strict_types=1);

namespace JR\Tracker\RequestValidator\PasswordReset;

use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Exception\ValidationException;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorInterface;
use Valitron\Validator;

class ResetPasswordRequestValidator implements RequestValidatorInterface
{
  public function __construct(
  ) {
  }

  public function validate(array $data): array
  {
    $v = new Validator($data);

    // Validate mandatory fields
    $v->rule('required', 'password')->message('required');
    $v->rule('required', 'confirmPassword')->message('required');
    $v->rule('required', 'token')->message('required');

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
