<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Reference\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use JR\Tracker\Entity\Reference\Contract\LocaleTypeInterface;

#[Entity]
#[Table(name: 'localeType')]
class LocaleType implements LocaleTypeInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    private int $idLocaleType;

    #[Column(unique: true, length: 10)]
    private string $code; // cs_CZ

    #[Column(length: 50)]
    private string $name; // Čeština
}