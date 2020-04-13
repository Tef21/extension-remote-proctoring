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

namespace oat\remoteProctoring\model\signature;

use oat\oatbox\Configurable;
use oat\remoteProctoring\model\signature\exception\SignatureException;
use Psr\Http\Message\RequestInterface;

class Sha256Signature extends Configurable implements SignatureMethod
{
    public const OPTION_SECRET = 'secret';

    public function signUrl(string $url): string
    {
        $baseString = $url . $this->getOption(self::OPTION_SECRET);
        return $url . '&signature=' . hash('sha256', $baseString);
    }

    public function validateRequest(RequestInterface $request): void
    {
        $url = $this->rebuildOffloadedUrl($request);
        $pos = strrpos($url, '&signature');
        if ($pos === false) {
            throw new SignatureException('Missing Signature');
        }
        $baseString = substr($url, 0, $pos);
        if (!hash_equals($this->signUrl($baseString), $url)) {
            throw new SignatureException('Invalid Signature');
        }
    }

    private function rebuildOffloadedUrl(RequestInterface $request): string
    {
        $url = $request->getUri();

        if ('http' === $url->getScheme() && $this->wasRequestForwardedByHttps($request)) {
            $url = $url->withScheme('https');
        }

        return (string)$url;
    }

    private function wasRequestForwardedByHttps(RequestInterface $request): bool
    {
        $https = $request->hasHeader('x-forwarded-proto')
            && $request->getHeader('x-forwarded-proto')[0] === 'https';
        $https = $https || ($request->hasHeader('x-forwarded-ssl')
                && $request->getHeader('x-forwarded-ssl')[0] === 'on');

        return $https;
    }
}
