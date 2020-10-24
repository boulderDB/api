<?php

namespace App\Collection;

use App\Entity\TimeSlot;
use App\Helper\TimeHelper;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;

class TimeSlotCollection extends ArrayCollection
{
    public static function orderByDayAndTime(array $items): array
    {
        $collection = new self($items);
        $iterator = $collection->getIterator();

        $iterator->uasort(function ($a, $b) {
            /**
             * @var TimeSlot $a
             * @var TimeSlot $b
             */
            $dateA = Carbon::now()
                ->setTimeFromTimeString($a->getStartTime())
                ->setDay(TimeHelper::getDayNumber($a->getDayName()));

            $dateB = Carbon::now()
                ->setTimeFromTimeString($b->getStartTime())
                ->setDay(TimeHelper::getDayNumber($b->getDayName()));

            return $dateA->isBefore($dateB) ? -1 : 1;
        });

        return iterator_to_array($iterator, false);
    }
}
