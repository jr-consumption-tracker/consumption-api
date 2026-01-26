<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\GeneratedValue;
use JR\Tracker\Entity\User\Contract\UserInfoInterface;


#[Entity]
#[Table(name: 'userInfo')]
class UserInfo implements UserInfoInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    private int $idUserInfo;

    #[Column(length: 50)]
    private ?string $firstName;

    #[Column(length: 50)]
    private ?string $lastName;

    #[OneToOne(targetEntity: User::class, inversedBy: 'userInfo')]
    #[JoinColumn(name: 'idUser', referencedColumnName: 'idUser', nullable: false)]
    private User $user;

}