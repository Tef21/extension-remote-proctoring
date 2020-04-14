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

use common_Exception;
use common_exception_Error;
use common_exception_NotFound;
use common_persistence_KeyValuePersistence;
use GuzzleHttp\Exception\GuzzleException;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\Proctorio\Exception\ProctorioParameterException;
use oat\Proctorio\ProctorioService;
use oat\remoteProctoring\model\request\ProctorioRequestBuilder;
use oat\remoteProctoring\model\response\ProctorioResponse;
use oat\remoteProctoring\model\response\ProctorioResponseValidator;
use oat\remoteProctoring\model\storage\ProctorioUrlRepository;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;

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
    public const PREFIX_KEY_VALUE = 'proctorio::';

    /** @var ProctorioResponseValidator */
    private $validator;

    /** @var ProctorioService */
    private $proctorioUrlLibraryService;

    /**
     * @throws GuzzleException
     * @throws ProctorioParameterException
     * @throws common_Exception
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    public function getProctorioUrl(DeliveryExecutionInterface $deliveryExecution): ?ProctorioResponse
    {
            $providerJsonResponse = $this->requestProctorioUrls($deliveryExecution);
            if ($this->getValidator()->validate($providerJsonResponse)) {
                $proctorioUrls = ProctorioResponse::fromJson($providerJsonResponse);
                $this->getStorage()->set($this->getUrlsId($deliveryExecution), $proctorioUrls);
            }

        return $proctorioUrls;
    }

    public function setProctorioUrlLibraryService(ProctorioService $proctorioUrlLibraryService): void
    {
        $this->proctorioUrlLibraryService = $proctorioUrlLibraryService;
    }

    /**
     * @throws GuzzleException
     * @throws common_Exception
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     * @throws ProctorioParameterException
     */
    private function requestProctorioUrls(DeliveryExecutionInterface $deliveryExecution): string
    {
        $proctorioService = $this->getProctorioLibraryService();
        $launchUrl = $this->getLaunchService()->generateUrl($deliveryExecution->getIdentifier());
        $config = $this->getRequestBuilder()->build($deliveryExecution, $launchUrl);

        return $proctorioService->callRemoteProctoring(
            $config,
            $this->getOption(self::OPTION_OAUTH_KEY),
            $this->getOption(self::OPTION_OAUTH_SECRET)
        );
    }

    protected function getStorage(): common_persistence_KeyValuePersistence
    {
        return $this->getServiceLocator()
            ->get(PersistenceManager::SERVICE_ID)
            ->getPersistenceById($this->getOption(self::OPTION_PERSISTENCE));
    }

    private function getLaunchService(): LaunchService
    {
        return $this->getServiceLocator()->get(LaunchService::SERVICE_ID);
    }

    private function getRequestBuilder(): ProctorioRequestBuilder
    {
        return $this->getServiceLocator()->get(ProctorioRequestBuilder::SERVICE_ID);
    }

    private function getUrlsId(DeliveryExecutionInterface $deliveryExecution): string
    {
        return self::PREFIX_KEY_VALUE . $deliveryExecution->getIdentifier();
    }

    private function getValidator(): ProctorioResponseValidator
    {
        return $this->getServiceLocator()->get(ProctorioResponseValidator::class);
    }

    private function getProctorioLibraryService(): ProctorioService
    {
        if ($this->proctorioUrlLibraryService === null) {
            return new ProctorioService();
        }

        return $this->proctorioUrlLibraryService;
    }
}
