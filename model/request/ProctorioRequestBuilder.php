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

use common_Exception;
use common_exception_Error;
use common_exception_NotFound;
use oat\generis\Helper\UuidPrimaryKeyTrait;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\user\User;
use oat\Proctorio\ProctorioConfig;
use oat\remoteProctoring\model\ProctorioApiService;
use oat\tao\helpers\UserHelper;
use oat\tao\model\security\TokenGenerator;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\oatbox\service\ConfigurableService;

class ProctorioRequestBuilder extends ConfigurableService
{
    use UuidPrimaryKeyTrait;
    use LoggerAwareTrait;
    use TokenGenerator;

    /** * @var int */
    private $time;

    /** @var string */
    private $nonce;

    /** @var ProctorioExamUrlFactory */
    private $proctorioExamUrlFactory;

    /** * @var string */
    private $userFullName;

    public function __construct(
        int $time = null,
        string $userFullName = null,
        string $nonce = null,
        ProctorioExamUrlFactory $proctorioExamUrlFactory = null
    )
    {
        $this->time = $time;
        $this->userFullName = $userFullName;
        $this->nonce = $nonce;
        $this->proctorioExamUrlFactory = $proctorioExamUrlFactory ?? new ProctorioExamUrlFactory();
    }


    /**
     * @throws common_Exception
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    public function build(DeliveryExecutionInterface $deliveryExecution, string $launchUrl, array $options): array
    {
        $this->setOptions($options);
        return [
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
            ProctorioConfig::EXAM_TAG => (string)md5($deliveryExecution->getDelivery()->getUri())
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

    protected function getExamSettings(): array
    {
        return $this->getOption(ProctorioApiService::OPTION_EXAM_SETTINGS);
    }

    private function getNonce(): string
    {
        return $this->nonce ?? $this->getUniquePrimaryKey();
    }

    private function getTime(): int
    {
        return $this->time ?? time();
    }
}
