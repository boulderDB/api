<?php

namespace App\Components\Controller;

use App\Entity\User;
use BlocBeta\Service\ContextService;

/**
 * @property ContextService $contextService
 * @method denyAccessUnlessGranted($attributes, $subject = null, string $message = 'Access Denied.')
 *
 */
trait ContextualizedControllerTrait
{
    protected function denyUnlessLocationAdmin()
    {
        $this->denyAccessUnlessGranted($this->contextService->getLocationRole(User::ROLE_ADMIN));
    }
}