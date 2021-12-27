<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

class ReadableIdentifierResource implements ReadableIdentifierInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    public function getReadableIdentifier(): ?ReadableIdentifier
    {
        // TODO: Implement getReadableIdentifier() method.
    }

    public function setReadableIdentifier(ReadableIdentifier $readableIdentifier = null): void
    {
        // TODO: Implement setReadableIdentifier() method.
    }
}