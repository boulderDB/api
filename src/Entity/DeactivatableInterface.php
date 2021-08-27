<?php

namespace App\Entity;

interface DeactivatableInterface
{
    public function setActive(bool $active): void;

    public function isActive(): bool;

    public function getName(): ?string;

    public function setName(?string $name): void;
}