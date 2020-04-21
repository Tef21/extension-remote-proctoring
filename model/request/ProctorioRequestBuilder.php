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
use oat\oatbox\user\UserService;
use oat\Proctorio\ProctorioConfig;
use oat\tao\helpers\UserHelper;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;

class ProctorioRequestBuilder extends ConfigurableService
{
    public const SERVICE_ID = 'remoteProctoring/ProctorioRequestBuilder';
    public const OPTION_EXAM_SETTINGS = 'exam_settings';
    public const OPTION_URL_EXAM_FACTORY = 'proctorioExamUrlFactory';
    public const OPTION_HASH_SERVICE = 'requestHashGenerator';

    /**
     * @throws common_exception_NotFound
     */
    public function build(DeliveryExecutionInterface $deliveryExecution, string $launchUrl): array
    {
        return [
            //delivery execution level
            ProctorioConfig::LAUNCH_URL => $launchUrl,
            ProctorioConfig::USER_ID => $this->getHashGenerator()->hash($deliveryExecution->getUserIdentifier()),
            ProctorioConfig::FULL_NAME => $this->getUserFullName($deliveryExecution),

            //platform level
            ProctorioConfig::EXAM_START => $this->getProctorioExamUrlFactory()->createExamStartUrl(),
            ProctorioConfig::EXAM_TAKE => $this->getProctorioExamUrlFactory()->createExamTakeUrl(),
            ProctorioConfig::EXAM_END => $this->getProctorioExamUrlFactory()->createExamEndUrl(),
            ProctorioConfig::EXAM_SETTINGS => $this->getExamSettings(),

            //Delivery level
            ProctorioConfig::EXAM_TAG => $this->getHashGenerator()->hash($deliveryExecution->getDelivery()->getUri()),
        ];
    }

    /**
     * @throws common_exception_NotFound
     */
    private function getUserFullName(DeliveryExecutionInterface $deliveryExecution): string
    {
        /** @var UserService $userService */
        $userService = $this->getServiceLocator()->get(UserService::SERVICE_ID);
        $user = $userService->getUser($deliveryExecution->getUserIdentifier());

        return UserHelper::getUserName($user);
    }

    private function getProctorioExamUrlFactory(): ProctorioExamUrlFactory
    {
        return $this->getSubService(self::OPTION_URL_EXAM_FACTORY);
    }

    private function getHashGenerator(): RequestHashGenerator
    {
        return $this->getSubService(self::OPTION_HASH_SERVICE);
    }

    private function getExamSettings(): array
    {
        return $this->getOption(self::OPTION_EXAM_SETTINGS);
    }
}
