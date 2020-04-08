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

namespace oat\remoteProctoring\model;

use common_persistence_KeyValuePersistence;
use Exception;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\Proctorio\ProctorioService;
use oat\remoteProctoring\model\request\ProctorioRequestBuilder;
use oat\remoteProctoring\model\response\ProctorioResponse;
use oat\remoteProctoring\model\response\ProctorioResponseValidator;
use oat\remoteProctoring\model\storage\ProctorioUrlRepository;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use Throwable;
use oat\generis\Helper\UuidPrimaryKeyTrait;
use oat\Proctorio\ProctorioConfig;

/**
 * Class ProctorioApiService
 */
class ProctorioApiService extends ConfigurableService
{
    use UuidPrimaryKeyTrait;
    use LoggerAwareTrait;

    public const SERVICE_ID = 'remoteProctoring/ProctorioApiService';

    public const OPTION_PERSISTENCE = 'persistence';

    public const OPTION_OAUTH_KEY = 'oauthKey';

    public const OPTION_OAUTH_SECRET = 'oauthSecret';

    public const OPTION_EXAM_SETTINGS = 'exam_settings';

    /** @var ProctorioUrlRepository $repository */
    private $repository;

    /** @var ProctorioResponseValidator $validator */
    private $validator;

    /** @var ProctorioService */
    private $proctorioUrlLibraryService;

    /**
     * @param DeliveryExecutionInterface $deliveryExecution
     * @return ProctorioResponse|null
     * @throws Exception
     */
    public function getProctorioUrl(DeliveryExecutionInterface $deliveryExecution): ?ProctorioResponse
    {
            $deliveryExecutionId = $deliveryExecution->getIdentifier();
            $proctorioUrls = $this->requestProctorioUrls($deliveryExecutionId);
            return ProctorioResponse::fromJson(json_encode($proctorioUrls));
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
     * @return ProctorioUrlRepository
     */
    public function getProctorioUrlRepository(): ProctorioUrlRepository
    {
        if ($this->repository === null) {
            $this->repository = new ProctorioUrlRepository($this->getStorage(), $this->getLogger());
        }
        return $this->repository;
    }

    /**
     * @return ProctorioRequestBuilder
     */
    private function getRequestBuilder(): ProctorioRequestBuilder
    {
        return $this->getServiceLocator()->get(ProctorioRequestBuilder::class);
    }

    /**
     * @param DeliveryExecutionInterface $deliveryExecutionId
     * @return string
     */
    private function getUrlsId(DeliveryExecutionInterface $deliveryExecutionId): string
    {
        try {
            return ProctorioUrlRepository::PREFIX_KEY_VALUE . $deliveryExecutionId->getUserIdentifier();
        } catch (Throwable $exception) {
            $time = time();
            $this->logError(
                'Error generating url identifier, failback index created: '
                . ProctorioUrlRepository::PREFIX_KEY_VALUE . $time
                . ' Error message: ' . $exception->getMessage()
            );

            return ProctorioUrlRepository::PREFIX_KEY_VALUE . $time;
        }
    }

    private function getValidator(): ProctorioResponseValidator
    {
        if ($this->validator === null) {
            $this->validator = new ProctorioResponseValidator($this->getLogger());
        }
        return $this->validator;
    }

    /**
     * @return ProctorioService
     */
    protected function getProctorioLibraryService(): ProctorioService
    {
        if ($this->proctorioUrlLibraryService === null) {
            return new ProctorioService();
        }

        return $this->proctorioUrlLibraryService;
    }

    /**
     * @param ProctorioService $proctorioUrlLibraryService
     */
    public function setProctorioUrlLibraryService(ProctorioService $proctorioUrlLibraryService): void
    {
        $this->proctorioUrlLibraryService = $proctorioUrlLibraryService;
    }
}
