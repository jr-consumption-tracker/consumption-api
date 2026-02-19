<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use JR\Tracker\Entity\User\Contract\UserVerifyEmailInterface;
use Symfony\Component\Uid\Ulid;

#[Entity]
#[Table(name: 'userVerifyEmail')]
#[Index(columns: ['email'])]
#[Index(columns: ['token'])]
#[Index(columns: ['expiresAt'])]
class UserVerifyEmail implements UserVerifyEmailInterface
{
  #[Id]
  #[GeneratedValue(strategy: 'AUTO')]
  #[Column]
  /** @phpstan-ignore-next-line */
  private int $idUserVerifyEmail;

  #[Column(length: 50)]
  private string $email;

  #[Column(length: 36, unique: true)]
  private string $token;

  #[Column]
  private \DateTime $expiresAt;

  #[Column(type: 'datetime_immutable')]
  private \DateTimeImmutable $createdAt;

  // Getters
  public function getId(): int
  {
    return $this->idUserVerifyEmail;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function getToken(): string
  {
    return $this->token;
  }

  public function getExpiresAt(): \DateTime
  {
    return $this->expiresAt;
  }

  public function getCreatedAt(): \DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function getIsExpired(): bool
  {
    $now = new \DateTime();

    return $this->expiresAt < $now;
  }

  // Setters
  public function setEmail(string $email): self
  {
    $this->email = $email;

    return $this;
  }

  public function setToken(): self
  {
    $ulidString = Ulid::generate();
    $ulidObject = Ulid::fromString($ulidString);
    $uuidString = (string) $ulidObject->toRfc4122();

    $this->token = $uuidString;

    return $this;
  }

  public function setExpiresAt(int $hours): self
  {
    $this->expiresAt = new \DateTime(sprintf('+%d hours', $hours));

    return $this;
  }

  public function setCreatedAt(): self
  {
    $this->createdAt = new \DateTimeImmutable();

    return $this;
  }
}
