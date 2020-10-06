<?php

namespace App\Entity;

use App\Helper\TimeHelper;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class TimeSlotExclusion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $startDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $endDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $quantity = null;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private ?string $hashId = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Room")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     */
    private ?Room $room = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $note = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getHashId(): ?string
    {
        return $this->hashId;
    }

    public function setHashId(?string $hashId): void
    {
        $this->hashId = $hashId;
    }

    public function generateHashId(): void
    {
        $this->hashId = self::buildHash(
            $this->getRoom()->getId(),
            $this->getStartDate(),
            $this->getEndDate(),
            $this->getQuantity()
        );
    }

    public static function buildHash(string $roomId, \DateTimeInterface $starTime, \DateTimeInterface $endTime, ?int $quantity): string
    {
        return md5($roomId . $starTime->getTimestamp() . $endTime->getTimestamp() . $quantity);
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): void
    {
        $this->room = $room;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    public function intersectsTimeSlot(TimeSlot $timeSlot): bool
    {
        $exclusionStart = Carbon::instance($this->startDate);
        $exclusionEnd = Carbon::instance($this->endDate);

        return $timeSlot->getStartDate()->between($exclusionStart, $exclusionEnd) || $timeSlot->getEndDate()->between($exclusionStart, $exclusionEnd);
    }
}