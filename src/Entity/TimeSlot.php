<?php

namespace App\Entity;

use App\Helper\TimeHelper;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="timeslot")
 * @ORM\Entity()
 */
class TimeSlot
{
    public const DATE_FORMAT_DATE = "Y-m-d";
    public const DATE_FORMAT_DATETIME = "Y-m-d H:i:s";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $dayName = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $startTime = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $endTime = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $capacity = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Room")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     */
    private ?Room $room = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $allowQuantity = 1;

    private ?Collection $reservations;

    private ?int $available = null;

    private ?int $blocked = null;

    private ?Carbon $startDate;

    private ?Carbon $endDate;

    private ?string $hashId;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDayName(): ?string
    {
        return $this->dayName;
    }

    public function setDayName(?string $dayName): void
    {
        $this->dayName = $dayName;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function setStartTime(?string $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    public function setEndTime(?string $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): void
    {
        $this->room = $room;
    }

    public function getAllowQuantity(): ?int
    {
        return $this->allowQuantity;
    }

    public function setAllowQuantity(?int $allowQuantity): void
    {
        $this->allowQuantity = $allowQuantity;
    }

    public function getReservations(): ?Collection
    {
        return $this->reservations;
    }

    public function initReservations(array $reservations): void
    {
        $this->reservations = new ArrayCollection($reservations);
    }

    public function setReservations(Collection $reservations): void
    {
        $this->reservations = $reservations;
    }

    public function buildReservationHash(Carbon $scheduleDate): string
    {
        return Reservation::buildHash(
            $this->getRoom()->getId(),
            $this->getRoom()->getLocation()->getId(),
            $this->startTime,
            $this->endTime,
            $scheduleDate->format(TimeHelper::DATE_FORMAT_DATE)
        );
    }

    public function getAvailable(): ?int
    {
        return $this->available;
    }

    public function setAvailable(?int $available): void
    {
        $this->available = $available;
    }

    public function getBlocked(): ?int
    {
        return $this->blocked;
    }

    public function setBlocked(?int $blocked): void
    {
        $this->blocked = $blocked;
    }

    public function getStartDate(): ?Carbon
    {
        return $this->startDate;
    }

    public function buildStartDate(string $ymd): void
    {
        $this->startDate = TimeHelper::convertToCarbonDate($ymd, $this->startTime);
    }

    public function getEndDate(): ?Carbon
    {
        return $this->endDate;
    }

    public function buildEndDate(string $ymd): void
    {
        $this->endDate = TimeHelper::convertToCarbonDate($ymd, $this->endTime);
    }

    public function getHashId(): ?string
    {
        return $this->hashId;
    }

    public function setHashId(?string $hashId): void
    {
        $this->hashId = $hashId;
    }
}