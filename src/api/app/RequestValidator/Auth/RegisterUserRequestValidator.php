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
        if (!empty($data['email'])) {
            $v->rule('email', 'email')->message('emailInvalid');
            $v->rule('regex', 'email', '/' . EMAIL_END_REGEX . '/')->message('emailInvalid');

            $exists = $this->entityManagerService->getRepository(User::class)->count(['email' => $data['email']]);
            if ($exists) {
                throw new ValidationException(['general' => ['registrationFailed']], HttpStatusCode::BAD_REQUEST->value);
            }
        }
        // Validate password
        if (!empty($data['password'])) {
            $v->rule('regex', 'password', '/' . UPPERCASE_REGEX . '/')->message('passwordUpperCase');
            $v->rule('regex', 'password', '/' . LOWERCASE_REGEX . '/')->message('passwordLoweCase');
            $v->rule('regex', 'password', '/' . NUMBERS_REGEX . '/')->message('passwordNumbers');
            $v->rule('lengthMin', "password", 8)->message('passwordMinLength|8');
            $v->rule('lengthMax', "password", 24)->message('passwordMaxLength|24');
        }

        // Validate confirm password
        if (!empty($data['confirmPassword'])) {
            $v->rule('equals', 'confirmPassword', 'password')->message('confirmPasswordOneOf');
        }

        if (!$v->validate()) {
            throw new ValidationException($v->errors(), HttpStatusCode::BAD_REQUEST->value);
        }

        return $data;
    }
}