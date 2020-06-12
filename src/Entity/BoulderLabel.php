<?php


namespace App\Entity;


class BoulderLabel
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $boulder;

    /**
     * @var string
     */
    private $title;

    public static function createKey(string $user, string $boulder, string $title = null): string
    {
        return "user={$user}:boulder={$boulder}:label={$title}";
    }

    public function toKey(): string
    {
        return self::createKey($this->user, $this->boulder, $this->title);
    }

    public static function fromKey(string $key)
    {
        $label = new self();
        $label->setKey($key);

        // user=1:boulder=2:label=foo
        $data = [];
        $parts = explode(":", $key);

        foreach ($parts as $part) {
            $value = explode("=", $part);
            $data[$value[0]] = $value[1];
        }

        $label->setBoulder($data['boulder']);
        $label->setUser($data['user']);
        $label->setTitle($data['label']);

        return $label;
    }


    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser($user): void
    {
        if ($user instanceof User) {
            $this->user = $user->getId();
        } else {
            $this->user = $user;
        }
    }

    public function getBoulder(): ?string
    {
        return $this->boulder;
    }

    public function setBoulder($boulder): void
    {
        if ($boulder instanceof Boulder) {
            $this->boulder = $boulder->getId();
        } else {
            $this->boulder = $boulder;
        }
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = preg_replace("/[^a-z0-9.]+/i", "", $title);
    }
}