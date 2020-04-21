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

namespace oat\remoteProctoring\model\authorization;

use oat\oatbox\session\SessionContext;
use oat\tao\model\security\SecurityException;

/**
 * Authorization Provider that verifies if the tes
 */
class RemoteProcotoredSessionContext implements SessionContext
{
    /** @var string */
    private $deliveryExecutionId;

    /**  @var bool */
    private $consumed = false;

    public function __construct(string $deliveryExecutionId)
    {
        $this->deliveryExecutionId = $deliveryExecutionId;
    }

    /**
     * Retuns the delivery execution id for which the authorization was granted
     */
    public function getDeliveryExecutionId(): string
    {
        return $this->deliveryExecutionId;
    }

    /**
     * @throws SecurityException
     */
    public function consume(): void
    {
        if ($this->consumed) {
            throw new SecurityException('Proctorio authorisation attempt has been already consumed');
        }
        $this->consumed = true;
    }

    public function isConsumed(): bool
    {
        return $this->consumed;
    }
}
