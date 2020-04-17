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

use oat\remoteProctoring\model\ProctorioApiService;
use oat\tao\model\http\Controller;
use tao_helpers_Uri;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class DeliveryReview extends Controller implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function review(): string
    {
        $requestBody = $this->getPsrRequest()->getParsedBody();
        $deliveryId = tao_helpers_Uri::decode($requestBody['uri']);

        return (string)$this->getProctorioApiService()->findReviewUrl($deliveryId);
    }

    private function getProctorioApiService(): ProctorioApiService
    {
        return $this->getServiceLocator()
            ->get(ProctorioApiService::SERVICE_ID);
    }
}
