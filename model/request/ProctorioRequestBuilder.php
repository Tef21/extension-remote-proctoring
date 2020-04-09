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

class ProctorioRequestBuilder
{
    use UuidPrimaryKeyTrait;
    use LoggerAwareTrait;
    use TokenGenerator;

    /** * @var int */
    private $time;

    /** @var array $options */
    private $options;

    /** @var string */
    private $nonce;

    /** @var ProctorioExamUrlFactory */
    private $proctorioExamUrlFactory;

    /** * @var string */
    private $userFullName;

    public function __construct(
        int $time = null,
        string $nonce = null,
        string $userFullName = null,
        array $options = [],
        ProctorioExamUrlFactory $proctorioExamUrlFactory = null
    )
    {
        $this->time = $time;
        $this->nonce = $nonce;
        $this->userFullName = $userFullName;
        $this->options = $options;
        $this->proctorioExamUrlFactory = $proctorioExamUrlFactory ?? new ProctorioExamUrlFactory();
    }


    /**
     * @throws common_Exception
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    public function build(DeliveryExecutionInterface $deliveryExecution, string $launchUrl): array
    {
        return
            [
                //delivery execution level
                ProctorioConfig::LAUNCH_URL => $launchUrl,
                ProctorioConfig::USER_ID => $deliveryExecution->getUserIdentifier(),
                ProctorioConfig::FULL_NAME => $this->getUserFullName($deliveryExecution),

                //platform level
                ProctorioConfig::EXAM_START => $this->proctorioExamUrlFactory->createExamStartUrl(),
                ProctorioConfig::EXAM_TAKE => $this->proctorioExamUrlFactory->createExamTakeUrl(),
                ProctorioConfig::EXAM_END => $this->proctorioExamUrlFactory->createExamEndUrl(),
                ProctorioConfig::EXAM_SETTINGS => $this->getExamSettings(),

                //Delivery level
                ProctorioConfig::EXAM_TAG => $deliveryExecution->getDelivery()->getLabel(),
                ProctorioConfig::OAUTH_TIMESTAMP => $this->getTime(),
                ProctorioConfig::OAUTH_NONCE => $this->getNonce(),
            ];
    }

    /**
     * @return mixed|null
     */
    private function getOption(string $name)
    {
        return $this->options[$name] ?? null;
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
