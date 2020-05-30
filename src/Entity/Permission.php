<?php

namespace App\Entity;

use App\Components\Entity\LocationResourceInterface;
use App\Components\Entity\LocationTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Permission
 * @package App\Entity
 *
 * @ORM\Entity(repositoryClass="App\Repository\PermissionRepository")
 */
class Permission implements LocationResourceInterface
{
    use LocationTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var array
     * @ORM\Column(type="string")
     */
    private $context;

    /**
     * @var string
     * @ORM\Column(type="array", nullable=true)
     */
    private $permissions;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $role;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $context
     */
    public function setContext(string $context)
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role)
    {
        $this->role = $role;
    }
}