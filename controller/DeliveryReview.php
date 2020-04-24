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

namespace oat\remoteProctoring\controller;

use common_Exception;
use oat\oatbox\log\LoggerAwareTrait;
use oat\remoteProctoring\model\ProctorioApiService;
use oat\tao\model\http\Controller;
use Psr\Http\Message\ResponseInterface;
use tao_helpers_Uri;
use Throwable;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

use function GuzzleHttp\Psr7\stream_for;

class DeliveryReview extends Controller implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use LoggerAwareTrait;

    public function review(): ResponseInterface
    {
        try {
            $requestBody = $this->getPsrRequest()->getParsedBody();
            $deliveryId = tao_helpers_Uri::decode($requestBody['uri']);
            $reviewUrl = $this->getProctorioApiService()->findReviewUrl($deliveryId);

            return $this->createResponse(200, $reviewUrl);
        } catch (Throwable $exception) {
            $this->logError($exception->getMessage());

            return $this->createResponse(500);
        }
    }

    private function createResponse(int $code, string $reviewUrl = null): ResponseInterface
    {
        $response = [
            'data' => ['url' => $reviewUrl],
            'success' => $code === 200
        ];
        return $this->getPsrResponse()
            ->withBody(stream_for(json_encode($response)))
            ->withStatus($code);
    }

    private function getProctorioApiService(): ProctorioApiService
    {
        return $this->getServiceLocator()
            ->get(ProctorioApiService::SERVICE_ID);
    }
}
