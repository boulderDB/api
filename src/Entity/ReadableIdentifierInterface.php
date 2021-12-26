<?php

namespace App\Entity;

interface ReadableIdentifierInterface
{
    public function getReadableIdentifier(): ?ReadableIdentifier;

    public function setReadableIdentifier(ReadableIdentifier $readableIdentifier): void;
}