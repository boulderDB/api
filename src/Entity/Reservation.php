<?php

namespace App\Entity;

use App\Helper\TimeHelper;
use Carbon\Carbon;
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
     * @ORM\Column(type="string")
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
     * @ORM\Column(type="boolean", options={"default": false}, nullable=true)
     */
    private ?bool $checkedIn = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private ?bool $guest = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $firstName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $lastName = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $username = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $quantity = 1;

    private ?Carbon $startDate;

    private ?Carbon $endDate;

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
        /**
         * @var User $user
         */
        $this->user = $user;

        $this->firstName = $user->getFirstName();
        $this->lastName = $user->getLastName();
        $this->username = $user->getUsername();
        $this->email = $user->getEmail();
    }

    public function getAppeared(): ?bool
    {
        return $this->appeared;
    }

    public function setAppeared(?bool $appeared): void
    {
        $this->appeared = $appeared;
    }

    public function getCheckedIn(): ?bool
    {
        return $this->checkedIn;
    }

    public function setCheckedIn(?bool $checkedIn): void
    {
        $this->checkedIn = $checkedIn;

        if ($checkedIn === true) {
            $this->setAppeared(true);
        }
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    public function getDayName(): string
    {
        return strtolower($this->date->format("l"));
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void
    {
        if ($quantity) {
            $this->quantity = $quantity;
        }
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
}
