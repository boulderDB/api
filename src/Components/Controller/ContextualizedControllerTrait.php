<?php

namespace App\Components\Controller;

use App\Components\Constants;
use App\Service\ContextService;

/**
 * @property ContextService $contextService
 * @method denyAccessUnlessGranted($attributes, $subject = null, string $message = 'Access Denied.')
 *
 */
trait ContextualizedControllerTrait
{
    protected function denyUnlessLocationAdmin()
    {
        $this->denyAccessUnlessGranted($this->contextService->getLocationRole(Constants::ROLE_ADMIN));
    }
}