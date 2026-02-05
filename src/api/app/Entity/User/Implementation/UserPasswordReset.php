<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use JR\Tracker\Entity\User\Contract\UserPasswordResetInterface;

#[Entity]
#[Table(name: 'userPasswordReset')]
#[Index(columns: ['email'])]
#[Index(columns: ['token'])]
#[Index(columns: ['expiresAt'])]
class UserPasswordReset implements UserPasswordResetInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    /** @phpstan-ignore-next-line */
    private int $idUserPasswordReset;

    #[Column(length: 50)]
    private string $email;

    #[Column(length: 36, unique: true)]
    private string $token;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $expiresAt;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $usedAt;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;


    // Getters
    public function getIdPasswordReset(): int
    {
        return $this->idUserPasswordReset;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getToken(): string
    {
        return $this->token;
    }
    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }
    public function getUsedAt(): ?\DateTimeImmutable
    {
        return $this->usedAt;
    }
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    // Setters
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }
    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }
    public function setExpiresAt(\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }
    public function setUsedAt(?\DateTimeImmutable $usedAt): self
    {
        $this->usedAt = $usedAt;
        return $this;
    }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}