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
 *
 */
namespace oat\remoteProctoring\model;


use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\authorization\AuthorizationProvider;
use oat\taoClientRestrict\model\requirements\RequirementsServiceInterface;
use oat\oatbox\user\User;
use oat\taoDelivery\model\authorization\UnAuthorizedException;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;

class ProctoringAuthorizationProvider extends ConfigurableService implements AuthorizationProvider
{
    protected function validateClient($deliveryId)
    {
    }

    public function verifyStartAuthorization($deliveryId, User $user)
    {
        // always allow
    }

    public function verifyResumeAuthorization(DeliveryExecutionInterface $deliveryExecution, User $user)
    {
//        $this->logInfo(var_export(getallheaders(), true));
        $proctorioApiService = $this->getServiceLocator()->get(ProctorioApiService::class);
        [$tt, $proctor] = $proctorioApiService->getProctorioUrl($deliveryExecution->getIdentifier());
        throw new UnAuthorizedException($tt);
    }
}
