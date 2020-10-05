<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\UserResourceInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="reservation")
 * @ORM\Entity()
 */
class Reservation implements UserResourceInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private ?string $hashId = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $date = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $startTime = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $endTime = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Room")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     */
    private ?Room $room;

    /**
     * @ORM\Column(type="boolean", options={"default": false}, nullable=true)
     */
    private ?bool $appeared = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private ?bool $guest = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $guestFirstName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $guestLastName = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private ?string $guestEmail = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHashId(): ?string
    {
        return $this->hashId;
    }

    public function generateHashId(): void
    {
        $this->hashId = self::buildHash(
            $this->getRoom()->getId(),
            $this->getRoom()->getLocation()->getId(),
            $this->getStartTime(),
            $this->getEndTime(),
            $this->getDate()->format('Y-m-d')
        );
    }

    public static function buildHash(string $roomId, string $locationId, string $startTime, string $endTime, string $dateYMD): string
    {
        return md5($roomId . $locationId . $startTime . $endTime . $dateYMD);
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

    public function getAppeared(): ?bool
    {
        return $this->appeared;
    }

    public function setAppeared(?bool $appeared): void
    {
        $this->appeared = $appeared;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): void
    {
        $this->date = $date;
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

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): void
    {
        $this->room = $room;
    }

    public function getGuest(): ?bool
    {
        return $this->guest;
    }

    public function setGuest(?bool $guest): void
    {
        $this->guest = $guest;
    }

    public function getGuestFirstName(): ?string
    {
        return $this->guestFirstName;
    }

    public function setGuestFirstName(?string $guestFirstName): void
    {
        $this->guestFirstName = $guestFirstName;
    }

    public function getGuestLastName(): ?string
    {
        return $this->guestLastName;
    }

    public function setGuestLastName(?string $guestLastName): void
    {
        $this->guestLastName = $guestLastName;
    }

    public function getGuestEmail(): ?string
    {
        return $this->guestEmail;
    }

    public function setGuestEmail(?string $guestEmail): void
    {
        $this->guestEmail = $guestEmail;
    }
}