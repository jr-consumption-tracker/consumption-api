<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\User\Contract\UserTokenInterface;
use JR\Tracker\Enum\DomainContextEnum;

#[Entity]
#[Table('userToken')]
#[Index(columns: ['expiresAt'])]
class UserToken implements UserTokenInterface
{
  #[Id]
  #[GeneratedValue(strategy: "AUTO")]
  #[Column()]
  /** @phpstan-ignore-next-line */
  private int $idUserToken;

  #[Column(length: 10)]
  private string $domain;

  #[Column(length: 255, nullable: true)]
  private string|null $refreshToken;

  #[Column(type: 'datetime')]
  private \DateTime $expiresAt;

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

  public function getExpiresAt(): \DateTime
  {
    return $this->expiresAt;
  }

  // Setters
  public function setUser(UserInterface $user): self
  {
    /** @var User $user */
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

  public function setExpiresAt(\DateTime $expiresAt): self
  {
    $this->expiresAt = $expiresAt;

    return $this;
  }
}
