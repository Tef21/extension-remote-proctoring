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
use oat\generis\model\GenerisRdf;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\remoteProctoring\model\delivery\ProctorioDeliverySettings;
use oat\remoteProctoring\model\delivery\ProctorioDeliverySettingsRepository;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;

class ProctorioDeliverySettingsRepositoryTest extends TestCase
{
    /** @var ProctorioDeliverySettingsRepository */
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

        $this->subject = new ProctorioDeliverySettingsRepository();
        $this->subject->setModel($this->ontology);
    }

    public function testIsDeliveryExecutionProctoredEnabled(): void
    {
        $this->mockOntology(true);

        $this->assertEquals(
            new ProctorioDeliverySettings(true),
            $this->subject->findByDeliveryExecution($this->deliveryExecution)
        );
    }

    public function testIsDeliveryExecutionProctoredDisabled(): void
    {
        $this->mockOntology(false);

        $this->assertEquals(
            new ProctorioDeliverySettings(false),
            $this->subject->findByDeliveryExecution($this->deliveryExecution)
        );
    }

    private function mockOntology(bool $isActivated): void
    {
        $propertyMock = $this->createMock(core_kernel_classes_Property::class);
        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);
        $resourceMock->method('equals')
            ->willReturn($isActivated);

        $this->delivery
            ->method('getUniquePropertyValue')
            ->with($propertyMock)
            ->willReturn($resourceMock);

        $this->ontology
            ->method('getResource')
            ->with(GenerisRdf::GENERIS_TRUE)
            ->willReturn($resourceMock);

        $this->ontology
            ->method('getProperty')
            ->with('http://www.tao.lu/Ontologies/TAODelivery.rdf#EnableRemoteProctoring')
            ->willReturn($propertyMock);
    }
}
