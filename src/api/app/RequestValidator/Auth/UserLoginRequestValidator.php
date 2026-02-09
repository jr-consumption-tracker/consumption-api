<?php

declare(strict_types=1);

namespace JR\Tracker\RequestValidator\Auth;

use Valitron\Validator;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Exception\ValidationException;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorInterface;

class UserLoginRequestValidator implements RequestValidatorInterface
{
    public function __construct(
    ) {
    }

    public function validate(array $data): array
    {
        $v = new Validator($data);

        // Validate mandatory fields
        $v->rule('required', 'email')->message('emailRequired');
        $v->rule('required', 'password')->message('passwordRequired');

        if (!$v->validate()) {
            throw new ValidationException($v->errors(), HttpStatusCode::BAD_REQUEST->value);
        }

        return $data;
    }
}