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

namespace oat\remoteProctoring\controller;

use oat\tao\model\http\Controller;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\UserService;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * This controller aims at launching deliveries for a test-taker
 */
class DeliveryLaunch extends Controller implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function launch()
    {
        // retrieve the delivery execution to launch from the parameters
        $deliveryExecution = $this->getDeliveryExecution();
        // init session from delivery execution user
        $this->initSession($deliveryExecution->getUserIdentifier());
        // redirect users to taoDelivery
        $this->redirect($this->getRedirectUrl($deliveryExecution->getIdentifier()));
    }

    protected function initSession($userId)
    {
        $userService = $this->getServiceLocator()->get(UserService::SERVICE_ID);
        $user = $userService->getUser($userId);
        $session = $this->getServiceLocator()->get(SessionService::SERVICE_ID);
        $session->setSession(new \common_session_DefaultSession($user));
        return $user;
    }

    /**
     * @return DeliveryExecutionInterface
     */
    protected function getDeliveryExecution()
    {
        $service = $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
        return $service->getDeliveryExecution($this->getGetParameter('deId'));
    }

    protected function getRedirectUrl($deliveryExecutionId)
    {
        return _url('runDeliveryExecution', 'DeliveryServer', 'taoDelivery', ['deliveryExecution' => $deliveryExecutionId]);
    }

}
