<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Reference\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use JR\Tracker\Entity\User\Implementation\UserInfo;
use JR\Tracker\Entity\Reference\Contract\TimezoneTypeInterface;

#[Entity]
#[Table(name: 'timezoneType')]
class TimezoneType implements TimezoneTypeInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    private int $idTimezoneType;

    #[Column(unique: true, length: 25)]
    private string $code; // např. Europe/Prague, UTC, America/New_York

    #[Column(length: 50)]
    private string $name; // např. "Praha (CET)", "UTC", "New York (EST)"

    #[OneToMany(mappedBy: 'timezone', targetEntity: UserInfo::class)]
    private Collection $user;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }
}