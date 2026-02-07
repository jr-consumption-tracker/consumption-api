<?php

declare(strict_types=1);

namespace JR\Tracker\RequestValidator\Auth;

use Valitron\Validator;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Exception\ValidationException;
use JR\Tracker\Entity\User\Implementation\User;
use JR\Tracker\Service\Contract\EntityManagerServiceInterface;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorInterface;

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
        $v->rule('required', 'email')->message('emailRequired');
        $v->rule('required', 'password')->message('passwordRequired');
        $v->rule('required', 'confirmPassword')->message('confirmPasswordRequired');

        // Validate email
        $v->rule('email', 'email')->message('emailInvalid');
        $v->rule('regex', 'email', '/' . EMAIL_END_REGEX . '/')->message('emailInvalid');
        $v->rule(
            fn($field, $value, $params, $fields) => !$this->entityManagerService->getRepository(User::class)->count(
                ['email' => $value]
            ),
            'email'
        )->message('emailExists');

        // Validate password
        $v->rule('regex', 'password', '/' . UPPERCASE_REGEX . '/')->message('passwordUpperCase');
        $v->rule('regex', 'password', '/' . LOWERCASE_REGEX . '/')->message('passwordLoweCase');
        $v->rule('regex', 'password', '/' . NUMBERS_REGEX . '/')->message('passwordNumbers');
        $v->rule('lengthMin', "password", 8)->message('passwordMinLength|8');
        $v->rule('lengthMax', "password", 24)->message('passwordMaxLength|24');

        // Validate confirm password
        $v->rule('equals', 'confirmPassword', 'password')->message('confirmPasswordOneOf');

        if (!$v->validate()) {
            throw new ValidationException($v->errors(), HttpStatusCode::BAD_REQUEST->value);
        }

        return $data;
    }
}