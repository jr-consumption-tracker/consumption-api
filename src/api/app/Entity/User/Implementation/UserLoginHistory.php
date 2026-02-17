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
use JR\Tracker\Entity\User\Implementation\User;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\User\Contract\UserLoginHistoryInterface;

#[Entity]
#[Table(name: 'userLoginHistory')]
class UserLoginHistory implements UserLoginHistoryInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    /** @phpstan-ignore-next-line */
    private int $idUserLoginHistory;

    #[Column(length: 10)]
    private string $context;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $loginAttemptAt;

    #[Column]
    private bool $isSuccessful;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'userLoginHistory')]
    #[JoinColumn(name: 'idUser', referencedColumnName: 'idUser', nullable: false)]
    private User $user;


    // Getters
    public function getIdUserLoginHistory(): int
    {
        return $this->idUserLoginHistory;
    }
    public function getContext(): DomainContextEnum
    {
        return DomainContextEnum::from($this->context);
    }
    public function getLoginAttemptAt(): \DateTimeImmutable
    {
        return $this->loginAttemptAt;
    }
    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    // Setters
    public function setContext(DomainContextEnum $context): self
    {
        $this->context = $context->value;
        return $this;
    }
    public function setLoginAttemptAt(\DateTimeImmutable $loginAttemptAt): self
    {
        $this->loginAttemptAt = $loginAttemptAt;
        return $this;
    }
    public function setIsSuccessful(bool $isSuccessful): self
    {
        $this->isSuccessful = $isSuccessful;
        return $this;
    }
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
}