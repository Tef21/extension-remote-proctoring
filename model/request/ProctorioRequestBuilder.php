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
use tao_helpers_Uri;
use Throwable;

class ProctorioRequestBuilder
{
    use UuidPrimaryKeyTrait;
    use LoggerAwareTrait;
    use TokenGenerator;

    /** @var array $options */
    private $options;

    /**
     * @param DeliveryExecutionInterface $deliveryExecution
     * @param string $launchUrl
     * @param array $options
     * @return array
     * @throws common_Exception
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    public function build(DeliveryExecutionInterface $deliveryExecution, string $launchUrl, array $options): array
    {
        $this->options = $options;

        return
            [
                //delivery execution level
                ProctorioConfig::LAUNCH_URL => $launchUrl,
                ProctorioConfig::USER_ID => $deliveryExecution->getUserIdentifier(),

                //platform level
                ProctorioConfig::OAUTH_CONSUMER_KEY => $this->getOauthCredentials(),

                ProctorioConfig::EXAM_START => $launchUrl,
                ProctorioConfig::EXAM_TAKE => $this->getExamUrl(),
                ProctorioConfig::EXAM_END => $this->getExamUrl(),
                ProctorioConfig::EXAM_SETTINGS => $this->getExamSettings(),

                //delivery execution level
                ProctorioConfig::FULL_NAME => $this->getUserFullName($deliveryExecution),
                //Delivery level
                ProctorioConfig::EXAM_TAG => $deliveryExecution->getDelivery()->getLabel(),

                ProctorioConfig::OAUTH_TIMESTAMP => $this->getTime(),
                ProctorioConfig::OAUTH_NONCE => $this->getNonce(),
            ];
    }


    /**
     * @param $name
     * @return mixed|null
     */
    private function getOption($name)
    {
        return $this->options[$name] ?? null;
    }


    /**
     * @return string
     */
    protected function getExamUrl(): string
    {
        return tao_helpers_Uri::url(
            'runDeliveryExecution',
            'DeliveryRunner',
            null,
            []
        );
    }


    /**
     * @param DeliveryExecutionInterface $deliveryExecution
     * @return string
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    protected function getUserFullName(DeliveryExecutionInterface $deliveryExecution): string
    {
        /** @var User $user */
        $user = UserHelper::getUser($deliveryExecution->getUserIdentifier());
        $fullName = UserHelper::getUserFirstName($user) ?? '';
        $fullName .= ' ' . UserHelper::getUserLastName($user) ?? '';
        return $fullName;
    }

    /**
     * @return string
     */
    protected function getOauthCredentials(): ?string
    {
        return $this->getOption(ProctorioApiService::OPTION_OAUTH_KEY);
    }

    /**
     * @return string
     */
    protected function getExamSettings(): ?string
    {
        return $this->getOption(ProctorioApiService::OPTION_EXAM_SETTINGS);
    }

    /**
     * @return string
     * @throws common_Exception
     */
    protected function getNonce(): string
    {
        try {
            $nonce = $this->getUniquePrimaryKey();
        } catch (Throwable $exception) {
            $this->$this->logError('UUID assignation for proctorio nonce has failed');
            $nonce = (string)$this->generate();
        }

        return $nonce;
    }

    /**
     * @return int
     */
    protected function getTime(): int
    {
        return time();
    }
}
