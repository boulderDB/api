<?php

namespace App\Entity;

use App\Components\Entity\TenantResourceInterface;
use App\Components\Entity\TenantTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Value implements TenantResourceInterface
{
    use TenantTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $keyId;

    /**
     * save json formats
     *
     * @var string
     * @ORM\Column(type="string")
     */
    private $value;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $resourceId;

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
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getKeyId(): string
    {
        return $this->keyId;
    }

    /**
     * @param string $keyId
     */
    public function setKeyId(string $keyId)
    {
        $this->keyId = $keyId;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    /**
     * @param int $resourceId
     */
    public function setResourceId(int $resourceId = null)
    {
        $this->resourceId = $resourceId;
    }

    /**
     * @return int
     */
    public function getTenantId()
    {
        return $this->tenant->getId();
    }
}