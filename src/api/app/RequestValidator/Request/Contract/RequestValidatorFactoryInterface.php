<?php

declare(strict_types=1);

namespace JR\Tracker\RequestValidator\Request\Contract;

interface RequestValidatorFactoryInterface
{
    public function make(string $class): RequestValidatorInterface;
}