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

use oat\generis\Helper\UuidPrimaryKeyTrait;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\service\ConfigurableService;
use oat\Proctorio\ProctorioConfig;
use oat\Proctorio\ProctorioService;
use Throwable;
use common_persistence_KeyValuePersistence;

/**
 * This controller aims at launching deliveries for a test-taker
 */
class ProctorioApiService extends ConfigurableService
{
    use UuidPrimaryKeyTrait;

    const SERVICE_ID = 'remoteProctoring/ProctorioApiService';

    const OPTION_PERSISTENCE = 'persistence';

    const OPTION_OAUTH_KEY = 'oauthKey';

    const OPTION_OAUTH_SECRET = 'oauthSecret';

    const PREFIX_KEYVALUE = 'proctorio::';

    private $proctorioUrls;

    public function getProctorioUrl(string $deliveryExecutionId): array
    {
        if ($this->proctorioUrls === null) {
            $proctorioUrls = $this->loadProctorioUrls($deliveryExecutionId);
            if (empty($proctorioUrls)) {
                $proctorioUrls = $this->requestProctorioUrls($deliveryExecutionId);
                $this->storeProctorioUrls($deliveryExecutionId, $proctorioUrls);
            }
        }

        return $proctorioUrls;
    }

    /**
     * @return mixed
     */
    public function getLaunchService()
    {
        return $this->getServiceLocator()->get(LaunchService::class);
    }

    protected function getOauthCredentials(): string
    {
        return $this->getOption(self::OPTION_OAUTH_KEY);
    }

    protected function requestProctorioUrls($deliveryExecutionId): array
    {
        $proctorioService = new ProctorioService();

        $launchUrl = $this->getLaunchService()->generateLaunchUrl($deliveryExecutionId);
        $configDetails =
            [
                ProctorioConfig::LAUNCH_URL => $launchUrl,
                ProctorioConfig::USER_ID => 'mike' . time(),
                ProctorioConfig::OAUTH_CONSUMER_KEY => $this->getOauthCredentials(),

                ProctorioConfig::EXAM_START => $launchUrl,
                ProctorioConfig::EXAM_TAKE => 'https:\/\/tao33\.bout\/.*',
                ProctorioConfig::EXAM_END => 'https:\/\/google\.com\/.*',
                ProctorioConfig::EXAM_SETTINGS => 'webtraffic',

                ProctorioConfig::FULL_NAME => 'name',
                ProctorioConfig::EXAM_TAG => 'tag',
                ProctorioConfig::OAUTH_TIMESTAMP => time(),
                ProctorioConfig::OAUTH_NONCE => $this->getUniquePrimaryKey(),
            ];

        $config = $proctorioService->buildConfig($configDetails);

        $urls = $proctorioService->callRemoteProctoring($config, $this->getOption(self::OPTION_OAUTH_SECRET));

        if ($urls = json_decode($urls, true)) {
            return $urls;
        }

        throw new \RuntimeException(json_last_error());
    }

    protected function storeProctorioUrls($deliveryExecutionId, $proctorioUrls)
    {
        try {
            return $this->getStorage()->set(self::PREFIX_KEYVALUE.$deliveryExecutionId, json_encode($proctorioUrls));
        } catch (Throwable $exception) {
            $this->logError($exception->getMessage());
        }
        return false;
    }

    protected function loadProctorioUrls($deliveryExecutionId): array
    {
        $urls = $this->getStorage()->get(self::PREFIX_KEYVALUE.$deliveryExecutionId);
        if ($urls != false) {
            $urls = json_decode($urls, true);
        }
        return is_array($urls) ? $urls : [];
    }

    /**
     * @return common_persistence_KeyValuePersistence
     */
    protected function getStorage()
    {
        return $this->getServiceLocator()
            ->get(PersistenceManager::SERVICE_ID)
            ->getPersistenceById($this->getOption(self::OPTION_PERSISTENCE));
    }
}
