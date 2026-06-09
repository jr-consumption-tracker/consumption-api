<?php

declare(strict_types=1);

namespace JR\Tracker\RequestValidator\Auth;

use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Exception\ValidationException;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorInterface;
use Valitron\Validator;

class UserLoginRequestValidator implements RequestValidatorInterface
{
  public function __construct(
  ) {
  }

  public function validate(array $data): array
  {
    $v = new Validator($data);

    // Validate mandatory fields
    $v->rule('required', 'email')->message('required');
    $v->rule('required', 'password')->message('required');

    if (!$v->validate()) {
      throw new ValidationException(['validationError' => [$v->errors()]], HttpStatusCode::BAD_REQUEST->value);
    }

    return $data;
  }
}
