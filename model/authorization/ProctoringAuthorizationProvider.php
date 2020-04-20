<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\remoteProctoring\model\authorization;

use oat\oatbox\service\ConfigurableService;
use oat\Proctorio\Response\ProctorioResponse;
use oat\remoteProctoring\model\delivery\RemoteProctoringDeliverySettingsRepository;
use oat\taoDelivery\model\authorization\AuthorizationProvider;
use oat\oatbox\user\User;
use oat\taoDelivery\model\authorization\UnAuthorizedException;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\remoteProctoring\model\ProctorioApiService;
use oat\oatbox\session\SessionService;

/**
 * Authorization Provider that verifies if the tes
 */
class ProctoringAuthorizationProvider extends ConfigurableService implements AuthorizationProvider
{
     public const OPTION_COOKIE_SETUP_SERVICE = 'cookieSetupService';

    /**
     * {@inheritDoc}
     * @see AuthorizationProvider::verifyStartAuthorization()
     */
    public function verifyStartAuthorization($deliveryId, User $user)
    {
        // No verification on start required, validate on resume
        $this->getCookieSetUpService()->setUp();
    }

    /**
     * {@inheritDoc}
     * @see AuthorizationProvider::verifyResumeAuthorization()
     */
    public function verifyResumeAuthorization(DeliveryExecutionInterface $deliveryExecution, User $user)
    {
        if ($this->requiresRemoteProctoring($deliveryExecution) && !$this->isRemoteProctored($deliveryExecution)) {
            throw new UnAuthorizedException($this->getProctoringUrl($deliveryExecution)->getTestTakerUrl());
        }
    }

    private function getProctoringUrl(DeliveryExecutionInterface $deliveryExecution): ProctorioResponse
    {
        $proctorioApiService = $this->getServiceLocator()->get(ProctorioApiService::SERVICE_ID);
        return $proctorioApiService->getProctorioUrl($deliveryExecution);
    }

    /**
     * Whenever or not we are in a proctored context
     */
    private function isRemoteProctored(DeliveryExecutionInterface $deliveryExecution): bool
    {
        $sessionService = $this->getServiceLocator()->get(SessionService::SERVICE_ID);
        $session = $sessionService->getCurrentSession();
        $proctoredContexts = $session->getContexts(RemoteProcotoredSessionContext::class);
        $proctored = false;
        foreach ($proctoredContexts as $context) {
            if ($context->getDeliveryExecutionId() == $deliveryExecution->getIdentifier()) {
                $proctored = true;
                break;
            }
        }
        return $proctored;
    }

    /**
     * Whenever or not the current context needs to be proctored
     */
    private function requiresRemoteProctoring(DeliveryExecutionInterface $deliveryExecution): bool
    {
        return $this->getDeliverySettingsRepository()
            ->findByDeliveryExecution($deliveryExecution)
            ->isRemoteProctoringEnabled();
    }

    private function getDeliverySettingsRepository(): RemoteProctoringDeliverySettingsRepository
    {
        return $this->getServiceLocator()->get(RemoteProctoringDeliverySettingsRepository::SERVICE_ID);
    }

    private function getCookieSetUpService(): CookieSetUpService
    {
        return $this->getOption(self::OPTION_COOKIE_SETUP_SERVICE);
    }
}
