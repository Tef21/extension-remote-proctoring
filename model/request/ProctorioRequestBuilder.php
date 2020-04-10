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

namespace oat\remoteProctoring\model\request;

use common_exception_NotFound;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;
use oat\Proctorio\ProctorioConfig;
use oat\remoteProctoring\model\ProctorioApiService;
use oat\tao\helpers\UserHelper;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;

class ProctorioRequestBuilder extends ConfigurableService
{
    /** @var ProctorioExamUrlFactory */
    private $proctorioExamUrlFactory;

    /** * @var string */
    private $userFullName;

    /**
     * @throws common_exception_NotFound
     */
    public function build(DeliveryExecutionInterface $deliveryExecution, string $launchUrl): array
    {
        return
            [
                //delivery execution level
                ProctorioConfig::LAUNCH_URL => $launchUrl,
                ProctorioConfig::USER_ID => (string)md5($deliveryExecution->getUserIdentifier()),
                ProctorioConfig::FULL_NAME => $this->getUserFullName($deliveryExecution),

                //platform level
                ProctorioConfig::EXAM_START => $this->proctorioExamUrlFactory->createExamStartUrl(),
                ProctorioConfig::EXAM_TAKE => $this->proctorioExamUrlFactory->createExamTakeUrl(),
                ProctorioConfig::EXAM_END => $this->proctorioExamUrlFactory->createExamEndUrl(),
                ProctorioConfig::EXAM_SETTINGS => $this->getExamSettings(),

                //Delivery level
                ProctorioConfig::EXAM_TAG => $deliveryExecution->getDelivery()->getLabel(),
            ];
    }

    /**
     * @throws common_exception_NotFound
     */
    private function getUserFullName(DeliveryExecutionInterface $deliveryExecution): string
    {
        if ($this->userFullName === null) {
            /** @var User $user */
            $user = UserHelper::getUser($deliveryExecution->getUserIdentifier());
            return UserHelper::getUserName($user);
        }

        return $this->userFullName;
    }

    private function getExamSettings(): array
    {
        return $this->getOption(ProctorioApiService::OPTION_EXAM_SETTINGS);
    }
}
