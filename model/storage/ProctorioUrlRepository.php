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

namespace oat\remoteProctoring\model\storage;

use common_persistence_KeyValuePersistence;
use oat\remoteProctoring\model\response\ProctorioResponse;
use Psr\Log\LoggerInterface;
use Throwable;

class ProctorioUrlRepository
{
    public const PREFIX_KEY_VALUE = 'proctorio::';

    /** @var common_persistence_KeyValuePersistence $persistence */
    private $persistence;

    /** @var LoggerInterface $logger */
    private $logger;

    public function __construct(common_persistence_KeyValuePersistence $persistence, LoggerInterface $logger)
    {
        $this->persistence = $persistence;
        $this->logger = $logger;
    }

    public function findById(string $id): ?ProctorioResponse
    {
        $urls = $this->persistence->get($id);
        if ($urls) {
            return ProctorioResponse::fromJson($urls);
        }

        return null;
    }

    public function save(ProctorioResponse $response, string $id): bool
    {
        try {
            return $this->persistence->set($id, $response->toJson());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }

        return false;
    }
}
