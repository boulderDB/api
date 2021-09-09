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
    public const RESOURCE_NAME = "TimeSlot";

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
     * @ORM\Column(type="integer", options={"default": 1})
     */
    private ?int $maxQuantity = 1;

    /**
     * @ORM\Column(type="integer", options={"default": 1})
     */
    private ?int $minQuantity = 1;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $enabled = true;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $autoDestroy = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $enableAfter = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $disableAfter = null;

    private ?Collection $reservations;

    private ?int $available = null;

    private ?int $blocked = null;

    private ?Carbon $startDate;

    private ?Carbon $endDate;

    private ?string $hashId = null;

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

    public function getMaxQuantity(): ?int
    {
        return $this->maxQuantity;
    }

    public function setMaxQuantity(?int $maxQuantity): void
    {
        $this->maxQuantity = $maxQuantity;
    }

    public function getMinQuantity(): ?int
    {
        return $this->minQuantity;
    }

    public function setMinQuantity(?int $minQuantity): void
    {
        $this->minQuantity = $minQuantity;
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

    public function getUserReservation(int $userId): ?Reservation
    {
        $reservation = $this->getReservations()->filter(function ($reservation) use ($userId) {

            /**
             * @var Reservation $reservation
             */
            if (!$reservation->getOwner()) {
                return null;
            }

            return $reservation->getOwner()->getId() === $userId;

        })->first();

        if (!$reservation) {
            return null;
        }

        return $reservation;
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


    public function generateHashId(Carbon $scheduleDate): void
    {
        $this->hashId = $this->buildReservationHash($scheduleDate);
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

    public function isPending(string $ymd): bool
    {
        $current = Carbon::createFromFormat(TimeHelper::DATE_FORMAT_DATE, $ymd);
        $current->modify($_ENV["SERVER_TIME_OFFSET"]);

        $this->buildStartDate($current->format(TimeHelper::DATE_FORMAT_DATE));
        $this->buildEndDate($current->format(TimeHelper::DATE_FORMAT_DATE));

        return $this->getStartDate() < $current && $this->getEndDate() > $current;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getEnableAfter(): ?\DateTimeInterface
    {
        return $this->enableAfter;
    }

    public function setEnableAfter(?\DateTimeInterface $enableAfter): void
    {
        $this->enableAfter = $enableAfter;
    }

    public function isAutoDestroy(): bool
    {
        return $this->autoDestroy;
    }

    public function setAutoDestroy(bool $autoDestroy): void
    {
        $this->autoDestroy = $autoDestroy;
    }

    public function getDisableAfter(): ?\DateTimeInterface
    {
        return $this->disableAfter;
    }

    public function setDisableAfter(?\DateTimeInterface $disableAfter): void
    {
        $this->disableAfter = $disableAfter;
    }
}
