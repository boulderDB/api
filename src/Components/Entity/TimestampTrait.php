<?php

namespace App\Components\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\HasLifecycleCallbacks()
 */
trait TimestampTrait
{
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface|null $createdAt
     */
    public function setCreatedAt(\DateTimeInterface $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }


    public function setUpdatedAt(\DateTimeInterface $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @ORM\PrePersist()
     */
    public function updatePrePersist()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * @ORM\PreUpdate()
     */
    public function updatePreUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
    }
}