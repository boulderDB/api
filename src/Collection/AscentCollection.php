<?php

namespace App\Collection;

use App\Entity\Ascent;
use App\Entity\Boulder;
use Doctrine\Common\Collections\ArrayCollection;

class AscentCollection extends ArrayCollection
{
    public function findForBoulder(Boulder $boulder): AscentCollection
    {
        return $this->filter(function ($item) use ($boulder) {
            /* @var Ascent $item */
            return $item->getBoulder()->getId() === $boulder->getId();
        });
    }
}