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

/**
 * Class ProctorioApiService
 */
class ProctorioApiService extends ConfigurableService
{
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
        $proctorioUrls = $this->getProctorioUrlRepository()->findById($this->getUrlsId($deliveryExecution));
        if ($proctorioUrls === null) {
            $proctorioResponse = $this->requestProctorioUrls($deliveryExecution);
            if ($this->getValidator()->validate($proctorioResponse)) {
                $proctorioUrls = ProctorioResponse::fromJson($proctorioResponse);
                $this->getProctorioUrlRepository()->save($proctorioUrls, $this->getUrlsId($deliveryExecution));
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
        $proctorioService = $this->getProctorioLibraryService();
        $launchUrl = $this->getLaunchService()->generateUrl($deliveryExecution->getIdentifier());
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

    /**
     * @return LaunchService
     */
    public function getLaunchService(): LaunchService
    {
        return $this->getServiceLocator()->get(LaunchService::class);
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
