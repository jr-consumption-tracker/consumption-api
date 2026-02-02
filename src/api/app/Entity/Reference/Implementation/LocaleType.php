<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Reference\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\DBAL\Exception\RetryableException;
use JR\Tracker\Entity\Reference\Contract\LocaleTypeInterface;

#[Entity]
#[Table(name: 'localeType')]
class LocaleType implements LocaleTypeInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    /** @phpstan-ignore-next-line */
    private int $idLocaleType;

    #[Column(unique: true, length: 10)]
    private string $code; // cs_CZ

    #[Column(length: 50)]
    private string $name; // Čeština


    // Getters
    public function getIdLocaleType(): int
    {
        return $this->idLocaleType;
    }
    public function getCode(): string
    {
        return $this->code;
    }
    public function getName(): string
    {
        return $this->name;
    }


    // Setters
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}