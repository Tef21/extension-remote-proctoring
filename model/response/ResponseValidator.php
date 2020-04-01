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

namespace oat\remoteProctoring\response;

use oat\oatbox\log\LoggerAwareTrait;
use RuntimeException;

class ResponseValidator
{
    use LoggerAwareTrait;

    public const RESPONSE_CODES = [
        2653, // Missing required parameters
        2654, // Invalid parameter 
        2655, // Incorrect consumer key
        2656, // Signature is invalid
        2657, // The used timestamp is out of range
        2658, // Invalid exam tag ID
        2659, // Invalid settings
        2660, // Unknown
    ];

    /**
     * @param string $response
     * @return bool
     * @throws RuntimeException
     */
    public function validate(string $response): bool
    {
        $data = json_decode($response, true);
        if (count($data) === 2 && !in_array(current($data), self::RESPONSE_CODES, true)) {
            return true;
        }
        $this->logError('The Proctorio response contains an error' . $response);

        throw new RuntimeException('The Proctorio response contains an error');
    }
}
