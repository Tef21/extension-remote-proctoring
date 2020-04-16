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

namespace oat\remoteProctoring\model\delivery;

use common_exception_NotFound;
use common_persistence_KeyValuePersistence;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;

class DeliveryChecker
{
    private const STORAGE_KEY_PATTERN = 'proctorio::deliveryId::%s';

    /** @var common_persistence_KeyValuePersistence */
    private $persistence;

    public function __construct(common_persistence_KeyValuePersistence $persistence)
    {
        $this->persistence = $persistence;
    }

    /**
     * @throws common_exception_NotFound
     */
    public function isDeliveryExecutionProctored(DeliveryExecutionInterface $deliveryExecution): bool
    {
        $storedValue = $this->persistence->get(
            sprintf(
                self::STORAGE_KEY_PATTERN,
                $deliveryExecution->getIdentifier() //@TODO Must be the DeliveryId
            )
        );

        return !empty($storedValue);
    }
}
