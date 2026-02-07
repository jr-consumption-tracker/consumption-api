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
    /** @phpstan-ignore-next-line */
    private int $idUserInfo;

    #[Column(length: 50, nullable: true)]
    private ?string $firstName;

    #[Column(length: 50, nullable: true)]
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


    // Getters
    public function getIdUserInfo(): int
    {
        return $this->idUserInfo;
    }
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }
    public function getLastName(): ?string
    {
        return $this->lastName;
    }
    public function getLocaleType(): ?LocaleType
    {
        return $this->localeType;
    }
    public function getTimezoneType(): ?TimezoneType
    {
        return $this->timezoneType;
    }
    public function getUser(): User
    {
        return $this->user;
    }


    // Setters
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }
    public function setLocaleType(?LocaleType $localeType): self
    {
        $this->localeType = $localeType;
        return $this;
    }
    public function setTimezoneType(?TimezoneType $timezoneType): self
    {
        $this->timezoneType = $timezoneType;
        return $this;
    }
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
}