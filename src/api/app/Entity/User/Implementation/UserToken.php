<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use JR\Tracker\Enum\DomainContextEnum;
use Doctrine\ORM\Mapping\GeneratedValue;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\User\Contract\UserTokenInterface;

#[Entity]
#[Table('userToken')]
class UserToken implements UserTokenInterface
{
    #[Id]
    #[GeneratedValue(strategy: "AUTO")]
    #[Column()]
    private int $idUserToken;

    #[Column(length: 10)]
    private string $domain;

    #[Column(length: 255, nullable: true)]
    private string|null $refreshToken;

    #[ManyToOne(inversedBy: 'idUser', targetEntity: User::class)]
    #[JoinColumn(name: 'idUser', referencedColumnName: 'idUser', nullable: false)]
    private User $user;



    // Getters
    public function getId(): int
    {
        return $this->idUserToken;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getDomain(): DomainContextEnum
    {
        return DomainContextEnum::from($this->domain);
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }


    // Setters
    public function setUser(UserInterface $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function setDomain(DomainContextEnum $domain): self
    {
        $this->domain = $domain->value;
        return $this;
    }

    public function setRefreshToken(string|null $refreshToken): self
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }
}