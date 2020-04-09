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

use oat\oatbox\service\ConfigurableService;
use oat\remoteProctoring\model\signature\SignatureException;
use oat\remoteProctoring\model\signature\SignatureMethod;
use Psr\Http\Message\RequestInterface;

class LaunchService extends ConfigurableService
{
    public const SERVICE_ID = 'remoteProctoring/LaunchService';
    public const OPTION_SIGNATURE_METHOD = 'signer';

    private const URI_PARAM_EXECUTION = 'deid';

    public function generateUrl(string $deliveryExecutionId): string
    {
        $url = _url(
            'launch',
            'DeliveryLaunch',
            [
                $this->getExecutionParamName() => $deliveryExecutionId
            ]
        );
        return $this->getSignatureMethod()->signUrl($url);
    }

    /**
     * @throws SignatureException
     */
    public function validateRequest(RequestInterface $request): void
    {
        $this->getSignatureMethod()->validateRequest($request);
    }

    private function getSignatureMethod(): SignatureMethod
    {
        return $this->propagate($this->getOption(self::OPTION_SIGNATURE_METHOD));
    }

    public function getExecutionParamName(): string
    {
        return self::URI_PARAM_EXECUTION;

}
