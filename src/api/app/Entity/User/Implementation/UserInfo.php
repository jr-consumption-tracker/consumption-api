<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\GeneratedValue;
use JR\Tracker\Entity\User\Contract\UserInfoInterface;
use JR\Tracker\Entity\Reference\Implementation\LocaleType;
use JR\Tracker\Entity\Reference\Implementation\TimezoneType;

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

    #[ManyToOne(targetEntity: LocaleType::class, inversedBy: 'user')]
    #[JoinColumn(name: "idLocaleType", referencedColumnName: "idLocaleType", nullable: true)]
    private ?LocaleType $localeType;

    #[ManyToOne(targetEntity: TimezoneType::class, inversedBy: 'user')]
    #[JoinColumn(name: "idTimezoneType", referencedColumnName: "idTimezoneType", nullable: true)]
    private ?TimezoneType $timezoneType = null;

    #[OneToOne(targetEntity: User::class, inversedBy: 'userInfo')]
    #[JoinColumn(name: 'idUser', referencedColumnName: 'idUser', nullable: false)]
    private User $user;

}