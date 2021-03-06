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

namespace oat\remoteProctoring\test\unit\model\storage;

use common_persistence_KeyValuePersistence;
use core_kernel_classes_Resource;
use oat\generis\persistence\PersistenceManager;
use oat\generis\test\TestCase;
use oat\oatbox\log\LoggerService;
use oat\Proctorio\ProctorioService;
use oat\Proctorio\Response\ProctorioResponse;
use oat\remoteProctoring\model\LaunchService;
use oat\remoteProctoring\model\ProctorioApiService;
use oat\remoteProctoring\model\request\ProctorioRequestBuilder;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProctorioApiServiceTest extends TestCase
{
    /** * @var DeliveryExecutionInterface|MockBuilder */
    private $deliveryExecution;

    /** @var ProctorioApiService */
    private $subject;

    protected function setUp(): void
    {
        $persistenceManager = $this->getPersistance();

        $serviceLocatorMock = $this->setMockServices($persistenceManager);

        $this->deliveryExecution = $this->getDeliveryExecution();
        $this->subject = new ProctorioApiService();

        /** @var ProctorioService|MockObject $proctorioLibraryMock */
        $proctorioLibraryMock = $this->getMockBuilder(ProctorioService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $proctorioLibraryMock->method('callRemoteProctoring')
            ->willReturn(new ProctorioResponse('ttURL', 'reviewURL'));

        $this->subject->setProctorioService($proctorioLibraryMock);
        $this->subject->setServiceLocator($serviceLocatorMock);
    }

    public function testGetProctorioUrl(): void
    {
        $this->subject->setOption(ProctorioApiService::OPTION_OAUTH_KEY, 'key');
        $this->subject->setOption(ProctorioApiService::OPTION_OAUTH_SECRET, 'secret');
        $expected = new ProctorioResponse('ttURL', 'reviewURL');
        $proctorioResponse = $this->subject->getProctorioUrl($this->deliveryExecution);

        $this->assertInstanceOf(ProctorioResponse::class, $proctorioResponse);
        $this->assertEquals($expected, $proctorioResponse);
    }

    private function getDeliveryExecution()
    {
        $this->deliveryExecution = $this->getMockBuilder(DeliveryExecutionInterface::class)
            ->getMock();

        $delivery = $this->getMockBuilder(core_kernel_classes_Resource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $delivery->method('getLabel')->willReturn('test-Label');

        $this->deliveryExecution->method('getUserIdentifier')
            ->willReturn('test');
        $this->deliveryExecution->method('getDelivery')
            ->willReturn($delivery);

        $this->deliveryExecution->method('getIdentifier')
            ->willReturn('ttURL');

        return $this->deliveryExecution;
    }

    private function getPersistance(): MockObject
    {
        $persistance = $this->getMockBuilder(common_persistence_KeyValuePersistence::class)
            ->disableOriginalConstructor()
            ->getMock();

        $persistanceManager = $this->getMockBuilder(PersistenceManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $persistanceManager->method('getPersistenceById')->willReturn($persistance);
        return $persistanceManager;
    }


    private function setMockServices(MockObject $persistanceManager): ServiceLocatorInterface
    {
        $services = [
            PersistenceManager::SERVICE_ID => $persistanceManager,
            ProctorioRequestBuilder::SERVICE_ID => $this->createMock(ProctorioRequestBuilder::class),
            LaunchService::SERVICE_ID => $this->createMock(LaunchService::class),
            ProctorioRequestBuilder::class => $this->createMock(ProctorioRequestBuilder::class),
            LoggerService::SERVICE_ID => $this->createMock(LoggerInterface::class)
        ];

        return $this->getServiceLocatorMock($services);
    }
}
