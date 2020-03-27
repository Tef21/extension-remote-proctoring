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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\remoteProctoring\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\taoDelivery\model\authorization\AuthorizationService;
use oat\taoDelivery\model\authorization\strategy\AuthorizationAggregator;
use oat\remoteProctoring\model\ProctoringAuthorizationProvider;

/**
 * Installation action that register the authorization.
 */
class RegisterAuthorizationProvider extends InstallAction
{
    /**
     * @param $params
     */
    public function __invoke($params)
    {
        $authService = $this->getServiceManager()->get(AuthorizationService::SERVICE_ID);
        if ($authService instanceof AuthorizationAggregator) {
            $authService->addProvider(new ProctoringAuthorizationProvider());
            $this->registerService(AuthorizationService::SERVICE_ID, $authService);
        } else {
            throw new \common_exception_Error('Incompatible AuthorizationService "'.get_class($authService).'" found.');
        }
    }
}
