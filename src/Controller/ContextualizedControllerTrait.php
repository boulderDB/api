<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ContextService;

/**
 * @property ContextService $contextService
 * @method denyAccessUnlessGranted($attributes, $subject = null, string $message = 'Access Denied.')
 * @method isGranted($attribute, $subject = null): bool
 */
trait ContextualizedControllerTrait
{
    protected function denyUnlessLocationAdminOrSetter()
    {
        return $this->isGranted($this->contextService->getLocationRole(User::ROLE_ADMIN)) || $this->isGranted($this->contextService->getLocationRole(User::SETTER));
    }

    protected function denyUnlessLocationAdmin()
    {
        $this->denyAccessUnlessGranted($this->contextService->getLocationRole(User::ROLE_ADMIN));
    }

    protected function isLocationAdmin(): bool
    {
        return $this->isGranted($this->contextService->getLocationRole(User::ROLE_ADMIN));
    }

    protected function isLocationSetter(): bool
    {
        return $this->isGranted($this->contextService->getLocationRole(User::ROLE_SETTER));
    }

    protected function getLocationId(): int
    {
        return $this->contextService->getLocation()->getId();
    }
}