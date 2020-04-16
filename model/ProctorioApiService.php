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
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\Proctorio\Exception\InvalidProctorioResponseException;
use oat\Proctorio\Exception\ProctorioParameterException;
use oat\Proctorio\ProctorioService;
use oat\Proctorio\Response\ProctorioResponse;
use oat\remoteProctoring\model\request\ProctorioRequestBuilder;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use Throwable;

/**
 * Class ProctorioApiService
 */
class ProctorioApiService extends ConfigurableService
{
    use LoggerAwareTrait;

    public const SERVICE_ID = 'remoteProctoring/ProctorioApiService';

    //OPTIONS
    public const OPTION_PERSISTENCE = 'persistence';
    public const OPTION_OAUTH_KEY = 'oauthKey';
    public const OPTION_OAUTH_SECRET = 'oauthSecret';

    //Prefix
    public const PREFIX_KEY_VALUE = 'proctorio::';

    /** @var ProctorioService */
    private $proctorioService;

    /**
     * @param DeliveryExecutionInterface $deliveryExecution
     * @return ProctorioResponse
     * @throws InvalidProctorioResponseException
     * @throws ProctorioParameterException
     * @throws common_Exception
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     * @throws Throwable
     */
    public function getProctorioUrl(DeliveryExecutionInterface $deliveryExecution): ?ProctorioResponse
    {
        try {
            $response = $this->requestProctorioUrls($deliveryExecution);
        } catch (Throwable $exception) {
            $this->logError(
                sprintf(
                    'Proctorio response contains an error: %s',
                    filter_var($exception->getMessage(), FILTER_SANITIZE_STRING)
                )
            );

            throw $exception;
        }

        $this->getStorage()
            ->set(
                $this->getUrlsId($deliveryExecution),
                json_encode(
                    [
                        $response->getTestTakerUrl(),
                        $response->getTestReviewerUrl(),
                    ]
                )
            );

        return $response;
    }

    public function setProctorioService(ProctorioService $proctorioUrlLibraryService): void
    {
        $this->proctorioService = $proctorioUrlLibraryService;
    }

    /**
     * @throws common_Exception
     * @throws common_exception_NotFound
     * @throws ProctorioParameterException
     * @throws InvalidProctorioResponseException
     */
    private function requestProctorioUrls(DeliveryExecutionInterface $deliveryExecution): ProctorioResponse
    {
        $proctorioService = $this->getProctorioLibraryService();
        $launchUrl = $this->getLaunchService()->generateUrl(
            LaunchService::URI_PARAM_EXECUTION,
            $deliveryExecution->getIdentifier()
        );
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

    private function getProctorioLibraryService(): ProctorioService
    {
        if ($this->proctorioService === null) {
            return new ProctorioService();
        }

        return $this->proctorioService;
    }
}
