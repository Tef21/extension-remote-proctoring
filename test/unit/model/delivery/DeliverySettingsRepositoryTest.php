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

namespace oat\remoteProctoring\test\unit\model\delivery;

use common_persistence_KeyValuePersistence;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\remoteProctoring\model\delivery\DeliverySettings;
use oat\remoteProctoring\model\delivery\DeliverySettingsRepository;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;

class DeliverySettingsRepositoryTest extends TestCase
{
    /** @var DeliverySettingsRepository */
    private $subject;

    /** @var common_persistence_KeyValuePersistence|MockObject */
    private $persistence;

    protected function setUp(): void
    {
        $this->persistence = $this->createMock(common_persistence_KeyValuePersistence::class);
        $this->subject = new DeliverySettingsRepository(
            [
                'persistence' => $this->persistence
            ]
        );
    }

    public function testIsDeliveryExecutionProctoredEnabled(): void
    {
        $deliveryExecution = $this->mockDeliveryExecution('_id');
        $this->mockDeliveryActivation('_id', true);

        $this->assertEquals(
            new DeliverySettings(true),
            $this->subject->findByDeliveryExecution($deliveryExecution)
        );
    }

    public function testIsDeliveryExecutionProctoredDisabled(): void
    {
        $deliveryExecution = $this->mockDeliveryExecution('_id');
        $this->mockDeliveryActivation('_id', false);

        $this->assertEquals(
            new DeliverySettings(false),
            $this->subject->findByDeliveryExecution($deliveryExecution)
        );
    }

    public function mockDeliveryExecution(string $deliveryId): DeliveryExecutionInterface
    {
        $deliveryExecution = $this->createMock(DeliveryExecutionInterface::class);
        $deliveryExecution->method('getIdentifier')
            ->willReturn($deliveryId);

        return $deliveryExecution;
    }

    private function mockDeliveryActivation(string $id, bool $isActivated): void
    {
        $this->persistence
            ->method('get')
            ->with(sprintf('proctorio:deliverySettings:%s', $id))
            ->willReturn(
                json_encode(
                    [
                        'enabled' => $isActivated
                    ]
                )
            );
    }
}
