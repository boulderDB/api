<?php

namespace App\Entity;

use App\Components\Entity\TimestampTrait;
use App\Components\Entity\LocationResourceInterface;
use App\Components\Entity\LocationTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Event implements  LocationResourceInterface
{
    use TimestampTrait;
    use LocationTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     */
    private $creator;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateFrom;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateTo;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\User", fetch="LAZY", cascade={"persist"}, inversedBy="events")
     * @ORM\JoinTable(name="event_participant")
     */
    private $participants;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Boulder", fetch="LAZY", inversedBy="events")
     * @ORM\JoinTable(name="event_boulder")
     */
    private $boulders;

    /**
     * @var UploadedFile
     * @ORM\Column(type="string", nullable=true)
     */
    private $media;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $scoringSystem;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $scoringOrder;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $publicEvent;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $visible;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->boulders = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description = null)
    {
        $this->description = $description;
    }

    /**
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param User $creator
     */
    public function setCreator(User $creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return \DateTime
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param \DateTime $dateFrom
     */
    public function setDateFrom(\DateTime $dateFrom = null)
    {
        $this->dateFrom = $dateFrom;
    }

    /**
     * @return \DateTime
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    public function isActive(): bool
    {
        return $this->dateTo > new \DateTime();
    }

    public function setDateTo(\DateTime $dateTo = null)
    {
        $this->dateTo = $dateTo;
    }

    /**
     * @param User $user
     */
    public function addParticipant(User $user)
    {
        $this->participants->add($user);
    }

    /**
     * @param User $user
     */
    public function removeParticipant(User $user)
    {
        $this->participants->remove($user);
    }

    /**
     * @return ArrayCollection
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @param ArrayCollection $participants
     */
    public function setParticipants(ArrayCollection $participants)
    {
        $this->participants = $participants;
    }

    /**
     * @param Boulder $boulder
     */
    public function addBoulder(Boulder $boulder)
    {
        $this->boulders->add($boulder);
    }

    /**
     * @param Boulder $boulder
     */
    public function removeBoulder(Boulder $boulder)
    {
        $this->boulders->remove($boulder);
    }

    /**
     * @return ArrayCollection
     */
    public function getBoulders()
    {
        return $this->boulders;
    }

    /**
     * @param ArrayCollection $boulders
     */
    public function setBoulders(ArrayCollection $boulders)
    {
        $this->boulders = $boulders;
    }

    /**
     * @return UploadedFile
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param UploadedFile $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * @return string
     */
    public function getScoringSystem()
    {
        return $this->scoringSystem;
    }

    /**
     * @param string $scoringSystem
     */
    public function setScoringSystem(string $scoringSystem)
    {
        $this->scoringSystem = $scoringSystem;
    }

    /**
     * @return string
     */
    public function getScoringOrder()
    {
        return $this->scoringOrder;
    }

    /**
     * @param string $scoringOrder
     */
    public function setScoringOrder(string $scoringOrder)
    {
        $this->scoringOrder = $scoringOrder;
    }

    public function isPublicEvent()
    {
        return $this->publicEvent;
    }

    public function setPublicEvent(bool $publicEvent)
    {
        $this->publicEvent = $publicEvent;
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }
}