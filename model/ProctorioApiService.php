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

namespace oat\remoteProctoring\model;

use common_exception_Error;
use common_exception_NotFound;
use core_kernel_classes_Resource;
use Exception;
use oat\generis\Helper\UuidPrimaryKeyTrait;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;
use oat\Proctorio\ProctorioConfig;
use oat\Proctorio\ProctorioService;
use oat\tao\helpers\UserHelper;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use RuntimeException;
use Throwable;
use common_persistence_KeyValuePersistence;

/**
 * This controller aims at launching deliveries for a test-taker
 */
class ProctorioApiService extends ConfigurableService
{
    use UuidPrimaryKeyTrait;

    public const SERVICE_ID = 'remoteProctoring/ProctorioApiService';

    public const OPTION_PERSISTENCE = 'persistence';

    public const OPTION_OAUTH_KEY = 'oauthKey';

    public const OPTION_OAUTH_SECRET = 'oauthSecret';

    public const OPTION_EXAM_SETTINGS = 'exam_settings';

    public const PREFIX_KEY_VALUE = 'proctorio::';

    /**
     * @var
     */
    private $proctorioUrls;

    public function getProctorioUrl(DeliveryExecutionInterface $deliveryExecutionId): array
    {
        if ($this->proctorioUrls === null) {
            $proctorioUrls = $this->loadProctorioUrls($deliveryExecutionId->getIdentifier());
            if (empty($proctorioUrls)) {
                $proctorioUrls = $this->requestProctorioUrls($deliveryExecutionId);
                $this->storeProctorioUrls($deliveryExecutionId, $proctorioUrls);
            }
        }

        return $proctorioUrls;
    }


    public function getLaunchService()
    {
        return $this->getServiceLocator()->get(LaunchService::class);
    }

    /**
     * @return string
     */
    private function getOauthCredentials(): string
    {
        return $this->getOption(self::OPTION_OAUTH_KEY);
    }

    /**
     * @return string
     */
    private function getExamSettings(): string
    {
        return $this->getOption(self::OPTION_EXAM_SETTINGS);
    }

    /**
     * @param string $deliveryExecutionId
     * @return array
     * @throws Exception
     */
    protected function requestProctorioUrls(string $deliveryExecutionId): array
    {
        $proctorioService = new ProctorioService();

        $config = $this->buildRequestPayload($deliveryExecutionId, $proctorioService);

        $urls = $proctorioService->callRemoteProctoring($config, $this->getOption(self::OPTION_OAUTH_SECRET));

        if ($urls = json_decode($urls, true)) {
            return $urls;
        }

        throw new RuntimeException(json_last_error());
    }

    /**
     * @param $deliveryExecutionId
     * @param $proctorioUrls
     * @return bool
     */
    protected function storeProctorioUrls($deliveryExecutionId, $proctorioUrls): bool
    {
        try {
            return $this->getStorage()->set(self::PREFIX_KEY_VALUE . $deliveryExecutionId, json_encode($proctorioUrls));
        } catch (Throwable $exception) {
            $this->logError($exception->getMessage());
        }
        return false;
    }

    /**
     * @param string $deliveryExecutionId
     * @return array
     */
    protected function loadProctorioUrls(string $deliveryExecutionId): array
    {
        $urls = $this->getStorage()->get(self::PREFIX_KEY_VALUE . $deliveryExecutionId);
        if ($urls !== false) {
            $urls = json_decode($urls, true);
        }
        return is_array($urls) ? $urls : [];
    }

    /**
     * @return common_persistence_KeyValuePersistence
     */
    protected function getStorage(): common_persistence_KeyValuePersistence
    {
        return $this->getServiceLocator()
            ->get(PersistenceManager::SERVICE_ID)
            ->getPersistenceById($this->getOption(self::OPTION_PERSISTENCE));
    }

    /**
     * @param DeliveryExecutionInterface $deliveryExecution
     * @param ProctorioService $proctorioService
     * @return array
     * @throws Exception
     */
    private function buildRequestPayload(DeliveryExecutionInterface $deliveryExecution, ProctorioService $proctorioService): array
    {
        $launchUrl = $this->getLaunchService()->generateLaunchUrl($deliveryExecution->getIdentifier());
        $configDetails =
            [
                //delivery execution level
                ProctorioConfig::LAUNCH_URL => $launchUrl,
                ProctorioConfig::USER_ID => $deliveryExecution->getUserIdentifier(),

                //platform level
                ProctorioConfig::OAUTH_CONSUMER_KEY => $this->getOauthCredentials(),

                ProctorioConfig::EXAM_START => $launchUrl,
                ProctorioConfig::EXAM_TAKE => $this->getExamUrl($deliveryExecution),
                ProctorioConfig::EXAM_END => $this->getExamUrl($deliveryExecution),
                ProctorioConfig::EXAM_SETTINGS => $this->getExamSettings(),

                //delivery execution level
                ProctorioConfig::FULL_NAME => $this->getUserFullName($deliveryExecution),
                //Delivery level
                ProctorioConfig::EXAM_TAG => $deliveryExecution->getDelivery()->getLabel(),

                ProctorioConfig::OAUTH_TIMESTAMP => time(),
                ProctorioConfig::OAUTH_NONCE => $this->getUniquePrimaryKey(),
            ];

        return $proctorioService->buildConfig($configDetails);
    }

    /**
     * @param DeliveryExecutionInterface $deliveryExecution
     * @return string
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    private function getUserFullName(DeliveryExecutionInterface $deliveryExecution): string
    {
        /** @var User $user */
        $user = new core_kernel_classes_Resource($deliveryExecution->getUserIdentifier());
        $fullName = UserHelper::getUserFirstName($user) ?? '';
        $fullName .= ' ' . UserHelper::getUserLastName($user) ?? '';
        return $fullName;
    }

    /**
     * @param DeliveryExecutionInterface $activeExecution
     * @return string
     * @throws common_exception_NotFound
     */
    private function getExamUrl(DeliveryExecutionInterface $activeExecution)
    {
        return _url(
            'runDeliveryExecution',
            'DeliveryRunner',
            null,
            ['deliveryExecution' => $activeExecution->getIdentifier()]
        );
    }
}
