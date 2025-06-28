<?php

/**
 * Category Entity.
 */

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Category entity class.
 *
 * Represents a transaction category.
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'category')]
class Category implements \Stringable
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Category title.
     */
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * Creation timestamp.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Last update timestamp.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Getter for ID.
     *
     * @return int|null ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for title.
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Setter for title.
     *
     * @param string $title Title
     *
     * @return self
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Getter for createdAt.
     *
     * @return \DateTimeImmutable|null Created at
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for createdAt.
     *
     * @param \DateTimeImmutable $createdAt Created at
     *
     * @return self
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Getter for updatedAt.
     *
     * @return \DateTimeImmutable|null Updated at
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Setter for updatedAt.
     *
     * @param DateTimeInterface $updatedAt Updated At
     *
     * @return self
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * String representation of the category.
     *
     * @return string Category title or empty string
     */
    public function __toString(): string
    {
        return $this->title ?? '';
    }
}
