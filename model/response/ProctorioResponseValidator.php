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

namespace oat\remoteProctoring\model\response;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class ProctorioResponseValidator
{

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

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function validate(string $response): bool
    {
        try {
            $data = json_decode($response, true);
            if (count($data) === 2 && !in_array(current($data), self::RESPONSE_CODES, true)) {
                return true;
            }
            throw new RuntimeException('Proctorio response contains an error');
        } catch (Throwable $exception) {
            $this->logger->error('Proctorio response contains an error'
                . filter_var($response, FILTER_SANITIZE_STRING));
        }

        return false;
    }
}
