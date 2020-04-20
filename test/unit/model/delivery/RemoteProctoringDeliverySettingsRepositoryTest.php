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

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\remoteProctoring\model\delivery\RemoteProctoringDeliverySettings;
use oat\remoteProctoring\model\delivery\RemoteProctoringDeliverySettingsRepository;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;

class RemoteProctoringDeliverySettingsRepositoryTest extends TestCase
{
    /** @var RemoteProctoringDeliverySettingsRepository */
    private $subject;

    /** @var Ontology|MockObject */
    private $ontology;

    /** @var DeliveryExecutionInterface|MockObject */
    private $deliveryExecution;

    /** @var core_kernel_classes_Resource|MockObject */
    private $delivery;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);

        $this->delivery = $this->createMock(core_kernel_classes_Resource::class);
        $this->deliveryExecution = $this->createMock(DeliveryExecutionInterface::class);
        $this->deliveryExecution
            ->method('getDelivery')
            ->willReturn($this->delivery);

        $this->subject = new RemoteProctoringDeliverySettingsRepository();
        $this->subject->setModel($this->ontology);
    }

    public function testIsDeliveryExecutionProctoredEnabled(): void
    {
        $this->mockOntology(true);

        $this->assertEquals(
            new RemoteProctoringDeliverySettings(true),
            $this->subject->findByDeliveryExecution($this->deliveryExecution)
        );
    }

    public function testIsDeliveryExecutionProctoredDisabled(): void
    {
        $this->mockOntology(false);

        $this->assertEquals(
            new RemoteProctoringDeliverySettings(false),
            $this->subject->findByDeliveryExecution($this->deliveryExecution)
        );
    }

    private function mockOntology(bool $isActivated): void
    {
        $property = new core_kernel_classes_Property(RemoteProctoringDeliverySettingsRepository::ONTOLOGY_REMOTE_PROCTORING_DELIVERY_SETTINGS);

        $this->ontology
            ->method('getProperty')
            ->willReturn($property);

        $this->delivery
            ->method('getPropertyValues')
            ->with($property)
            ->willReturn($isActivated ? [RemoteProctoringDeliverySettingsRepository::ONTOLOGY_REQUIRES_REMOTE_PROCTORING] : []);
    }
}
