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

use oat\oatbox\service\ConfigurableService;
use Psr\Http\Message\RequestInterface;
use oat\remoteProctoring\model\launch\SignatureMethod;
use oat\remoteProctoring\model\launch\SignatureException;

/**
 * This controller aims at launching deliveries for a test-taker
 */
class LaunchService extends ConfigurableService
{
    const SERVICE_ID = 'remoteProctoring/LaunchService';

    const OPTION_SIGNATURE_METHOD = 'signer';

    public function generateUrl(string $deliveryExecutionId): string
    {
        $url = _url('launch', 'DeliveryLaunch', 'remoteProctoring', [
            'deId' => $deliveryExecutionId
        ]);
        return $this->getSignatureMethod()->signUrl($url);
    }

    /**
     * @throws SignatureException
     * @param RequestInterface $request
     */
    public function validateRequest(RequestInterface $request): void
    {
        $this->getSignatureMethod()->validateRequest($request);
    }

    protected function getSignatureMethod(): SignatureMethod
    {
        return $this->propagate($this->getOption(self::OPTION_SIGNATURE_METHOD));
    }
}
