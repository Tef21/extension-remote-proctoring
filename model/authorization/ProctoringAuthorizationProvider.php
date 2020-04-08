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
use oat\taoDelivery\model\authorization\AuthorizationProvider;
use oat\oatbox\user\User;
use oat\taoDelivery\model\authorization\UnAuthorizedException;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\remoteProctoring\model\ProctorioApiService;
use oat\remoteProctoring\model\response\ProctorioResponse;
use oat\oatbox\session\SessionService;

/**
 * Authorization Provider that verifies if the tes
 */
class ProctoringAuthorizationProvider extends ConfigurableService implements AuthorizationProvider
{
    public function verifyStartAuthorization($deliveryId, User $user)
    {
        // No verification on start required, validate on resume
    }

    public function verifyResumeAuthorization(DeliveryExecutionInterface $deliveryExecution, User $user)
    {
        if ($this->requiresRemoteProctoring($deliveryExecution) && !$this->isRemoteProctored()) {
            throw new UnAuthorizedException($this->getProctoringUrl($deliveryExecution)->getTestTakerUrl());
        }
    }

    /**
     * @param DeliveryExecutionInterface $deliveryExecution
     * @return ProctorioResponse
     */
    private function getProctoringUrl(DeliveryExecutionInterface $deliveryExecution) {
        $proctorioApiService = $this->getServiceLocator()->get(ProctorioApiService::class);
        return $proctorioApiService->getProctorioUrl($deliveryExecution);
    }

    /**
     * Whenever or not we are in a proctored context
     * @return boolean
     */
    private function isRemoteProctored() {
        $sessionService = $this->getServiceLocator()->get(SessionService::SERVICE_ID);
        $session = $sessionService->getCurrentSession();
        $proctoredContexts = $session->getContexts(RemoteProcotoredSessionContext::class);
        return count($proctoredContexts) == 1;
    }

    /**
     * Whenever or not the current context needs to be proctored
     * @param DeliveryExecutionInterface $deliveryExecution
     * @return boolean
     */
    protected function requiresRemoteProctoring(DeliveryExecutionInterface $deliveryExecution) {
        return true;
    }
    
}
