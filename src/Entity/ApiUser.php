<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="api_users")
 * @ORM\Entity()
 */
class ApiUser implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $token;

    /**
     * @inheritdoc
     */
    public function getRoles()
    {
        return ['ROLE_API'];
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
        return $this->token;
    }

    /**
     * @inheritdoc
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials()
    {
        $this->token = null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token)
    {
        $this->token = $token;
    }
}