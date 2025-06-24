<?php

/**
 * This file is part of the Finanse project.
 *
 * @author Sofiya Hutsaylyuk
 */

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Wallet entity class.
 *
 * Represents a user's personal wallet.
 */
#[ORM\Entity(repositoryClass: WalletRepository::class)]
#[ORM\Table(name: 'wallet')]
class Wallet implements \Stringable
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Wallet name.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * Creation timestamp.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Update timestamp.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Wallet balance (as string).
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $balance = null;

    /**
     * Owner user.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * Get ID.
     *
     * @return int|null Wallet ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get wallet name.
     *
     * @return string|null Wallet name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set wallet name.
     *
     * @param string $name Wallet name
     *
     * @return static
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get creation timestamp.
     *
     * @return \DateTimeImmutable|null Creation time
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set creation timestamp.
     *
     * @param \DateTimeImmutable $createdAt Creation time
     *
     * @return static
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get update timestamp.
     *
     * @return \DateTimeImmutable|null Update time
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set update timestamp.
     *
     * @param \DateTimeImmutable $updatedAt Update time
     *
     * @return static
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get wallet balance.
     *
     * @return string|null Balance
     */
    public function getBalance(): ?string
    {
        return $this->balance;
    }

    /**
     * Set wallet balance.
     *
     * @param string $balance Wallet balance
     *
     * @return static
     */
    public function setBalance(string $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * Get wallet owner (user).
     *
     * @return User|null User entity
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set wallet owner (user).
     *
     * @param User|null $user Wallet owner
     *
     * @return static
     */
    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * String representation of the wallet.
     *
     * @return string Wallet name or empty string
     */
    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
