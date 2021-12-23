<?php

namespace App\Collection;

use App\Entity\Notification;
use Doctrine\Common\Collections\ArrayCollection;

class NotificationCollection extends ArrayCollection
{
    public function find(string $identifier): ?Notification
    {
        $match = $this->filter(function ($item) use ($identifier) {
            if (!$item) {
                return false;
            }

            /* @var Notification $item */
            return $item->getIdentifier() === $identifier;
        })->first();

        return $match ?: null;
    }
}