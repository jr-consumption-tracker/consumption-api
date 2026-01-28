<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Measurement\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\GeneratedValue;
use JR\Tracker\Entity\User\Implementation\User;
use JR\Tracker\Entity\Measurement\Contract\ConsumptionPlaceInterface;

#[Entity]
#[Table(name: 'consumptionPlace')]
class ConsumptionPlace implements ConsumptionPlaceInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    private int $idConsumptionPlace;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'consumptionPlace')]
    #[JoinColumn(name: "idUser", referencedColumnName: 'idUser', nullable: false)]
    private User $user;

    #[Column(length: 50)]
    private string $name;
}