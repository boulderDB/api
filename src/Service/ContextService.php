<?php

namespace App\Service;

use App\Entity\Location;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ContextService
{
    private ?Location $location = null;
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }

    public function getSettings()
    {
        try {
            $data = file_get_contents($this->parameterBag->get('kernel.project_dir') . "/settings/{$this->getLocation()->getUrl()}.json");
            return json_decode($data);
        } catch (\Exception $exception) {
            return null;
        }
    }

    public static function isLocationRole(string $role, int $locationId): bool
    {
        return self::getLocationIdFromRoleName($role) === $locationId;
    }

    public static function getLocationIdFromRoleName(string $role): ?int
    {
        if (strpos($role, "@") === false) {
            return null;
        }

        return (int)explode("@", $role)[1];
    }

    public static function filterLocationRoles(array $roles, int $locationId): array
    {
        return array_filter($roles, function ($role) use ($locationId) {
            return self::isLocationRole($role, $locationId);
        });
    }

    public static function getPlainRoleName(string $role): ?string
    {
        $role = explode("@", $role)[0];
        $role = str_replace("ROLE_", "", $role);

        return strtolower($role);
    }

    public static function getLocationRoleName(string $role, int $locationId, bool $prefix = false): string
    {
        if ($prefix) {
            return "ROLE_{$role}@{$locationId}";
        }

        return "{$role}@{$locationId}";
    }

    public function getLocationRole(string $role): ?string
    {
        if (!$this->getLocation()) {
            return null;
        }

        return self::getLocationRoleName($role, $this->getLocation()?->getId());
    }
}