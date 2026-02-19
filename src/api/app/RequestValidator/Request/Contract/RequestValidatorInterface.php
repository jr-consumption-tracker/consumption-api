<?php

declare(strict_types=1);

namespace JR\Tracker\RequestValidator\Request\Contract;

interface RequestValidatorInterface
{
  /**
   * Summary of validate
   * @param array $data
   * @return array
   * @author Jan Ribka
   */
  public function validate(array $data): array;
}
