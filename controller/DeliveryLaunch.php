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

namespace oat\remoteProctoring\controller;

use common_exception_NotFound;
use common_exception_Unauthorized;
use common_session_DefaultSession as DefaultSession;
use InterruptedActionException;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\UserService;
use oat\remoteProctoring\model\LaunchService;
use oat\remoteProctoring\model\signature\exception\SignatureException;
use oat\tao\model\http\Controller;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\model\execution\ServiceProxy;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class DeliveryLaunch extends Controller implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @throws InterruptedActionException
     * @throws common_exception_NotFound
     * @throws common_exception_Unauthorized
     */
    public function launch(): void
    {
        try {
            $this->getLaunchService()->validateRequest($this->getPsrRequest());
        } catch (SignatureException $e) {
            throw new common_exception_Unauthorized('The provided link is not valid', 403);
        }
        $deliveryExecutionId = (string)$this->getGetParameter(LaunchService::URI_PARAM_EXECUTION);
        $deliveryExecution = $this->getDeliveryExecution($deliveryExecutionId);

        $this->initSession($deliveryExecution->getUserIdentifier());
        $this->redirect($this->getRedirectUrl($deliveryExecutionId));
    }

    private function initSession(string $userId): void
    {
        /** @var UserService $userService */
        $userService = $this->getServiceLocator()->get(UserService::SERVICE_ID);
        $user = $userService->getUser($userId);
        /** @var SessionService $sessionService */
        $sessionService = $this->getServiceLocator()->get(SessionService::SERVICE_ID);
        $sessionService->setSession(new DefaultSession($user));
    }

    private function getDeliveryExecution($deliveryExecutionId): DeliveryExecutionInterface
    {
        /** @var ServiceProxy $deliveryExecutionService */
        $deliveryExecutionService = $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
        return $deliveryExecutionService->getDeliveryExecution($deliveryExecutionId);
    }

    private function getRedirectUrl(string $deliveryExecutionId): string
    {
        return _url(
            'runDeliveryExecution',
            'DeliveryServer',
            'taoDelivery',
            ['deliveryExecution' => $deliveryExecutionId]
        );
    }

    private function getLaunchService(): LaunchService
    {
        return $this->getServiceLocator()->get(LaunchService::SERVICE_ID);
    }
}
