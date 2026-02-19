<?php

declare(strict_types=1);

namespace JR\Tracker\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use JR\Tracker\Shared\Interface\OwnableInterface;

class UserFilter extends SQLFilter
{
  public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
  {
    if (!$targetEntity->getReflectionClass()->implementsInterface(OwnableInterface::class)) {
      return '';
    }

    return $targetTableAlias . 'idUser = ' . $this->getParameter('idUser');
  }
}
