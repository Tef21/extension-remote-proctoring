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

use common_persistence_KeyValuePersistence;
use Exception;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\service\ConfigurableService;
use oat\Proctorio\ProctorioService;
use oat\remoteProctoring\request\ProctorioRequestBuilder;
use oat\remoteProctoring\response\ProctorioResponse;
use oat\remoteProctoring\response\ResponseValidator;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use Throwable;

/**
 * Class ProctorioApiService
 */
class ProctorioApiService extends ConfigurableService
{

    public const SERVICE_ID = 'remoteProctoring/ProctorioApiService';

    public const OPTION_PERSISTENCE = 'persistence';

    public const OPTION_OAUTH_KEY = 'oauthKey';

    public const OPTION_OAUTH_SECRET = 'oauthSecret';

    public const OPTION_EXAM_SETTINGS = 'exam_settings';

    private $repository;
    private $validator;

    /**
     * @param DeliveryExecutionInterface $deliveryExecutionId
     * @return ProctorioResponse|null
     * @throws Exception
     */
    public function getProctorioUrl(DeliveryExecutionInterface $deliveryExecutionId): ?ProctorioResponse
    {
        $proctorioUrls = $this->getProctorioUrlRepository()->findById($this->getUrlsId($deliveryExecutionId));
        if ($proctorioUrls === null) {
            $proctorioResponse = $this->requestProctorioUrls($deliveryExecutionId);
            if ($this->getValidator()->validate($proctorioResponse)) {
                $proctorioUrls = ProctorioResponse::fromJson($proctorioResponse);
                $this->getProctorioUrlRepository()->save($proctorioUrls, $this->getUrlsId($deliveryExecutionId));
            }
        }

        return $proctorioUrls;
    }

    /**
     * @param DeliveryExecutionInterface $deliveryExecution
     * @return string
     * @throws Exception
     */
    protected function requestProctorioUrls(DeliveryExecutionInterface $deliveryExecution): string
    {
        $proctorioService = new ProctorioService();
        $launchUrl = $this->getLaunchService()->generateLaunchUrl($deliveryExecution->getIdentifier());
        $config = $this->getRequestBuilder()->build($deliveryExecution, $launchUrl, $this->getOptions());
        $proctorioService->buildConfig($config);

        return $proctorioService->callRemoteProctoring($config, $this->getOption(self::OPTION_OAUTH_SECRET));
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

    public function getLaunchService()
    {
        return $this->getServiceLocator()->get(LaunchService::class);
    }

    /**
     * @return ProctorioUrlRepository
     */
    public function getProctorioUrlRepository(): ProctorioUrlRepository
    {
        if ($this->repository === null) {
            $this->repository = new ProctorioUrlRepository($this->getStorage());
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
                'Error generating url identifier at:' . $time . ' with message: ' . $exception->getMessage()
            );

            return ProctorioUrlRepository::PREFIX_KEY_VALUE . $time;
        }
    }

    private function getValidator()
    {
        if ($this->validator === null) {
            $this->validator = new ResponseValidator();
        }
        return $this->validator;
    }
}
