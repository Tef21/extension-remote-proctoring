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

namespace oat\remoteProctoring\test\unit\model\request;

use common_Exception;
use common_exception_Error;
use common_exception_NotFound;
use core_kernel_classes_Resource;
use oat\generis\test\TestCase;
use oat\oatbox\user\UserService;
use oat\remoteProctoring\model\request\ProctorioExamUrlFactory;
use oat\remoteProctoring\model\request\ProctorioRequestBuilder;
use oat\remoteProctoring\model\request\RequestHashGenerator;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use PHPUnit\Framework\MockObject\MockObject;

class ProctorioRequestBuilderTest extends TestCase
{

    /** @var MockObject|DeliveryExecutionInterface */
    private $deliveryExecution;

    /** @var string $lunchUrl */
    private $lunchUrl;

    /** @var ProctorioRequestBuilder $subject */
    private $subject;

    /** * @var UserService|MockObject */
    private $userMock;

    public function setUp(): void
    {
        $this->userMock = $this->createMock(UserService::class);
        $serviceLocatorMock = $this->getServiceLocatorMock([
            ProctorioRequestBuilder::OPTION_URL_EXAM_FACTORY => new ProctorioExamUrlFactory(),
            ProctorioRequestBuilder::OPTION_HASH_SERVICE => new RequestHashGenerator(),
            UserService::SERVICE_ID => $this->userMock,
        ]);

        $this->deliveryExecution = $this->createMock(DeliveryExecutionInterface::class);
        $this->lunchUrl = 'someLunchUrl.tld';

        $this->subject = new ProctorioRequestBuilder(
            ['requestHashGenerator' => $this->createMock(RequestHashGenerator::class)]
        );
        $this->subject->setServiceLocator($serviceLocatorMock);
    }

    /**
     * @throws common_Exception
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    public function testBuild(): void
    {
        $delivery = $this->getMockBuilder(core_kernel_classes_Resource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $delivery->method('getLabel')->willReturn('test-Label');

        $this->deliveryExecution->method('getUserIdentifier')
            ->willReturn('test');
        $this->deliveryExecution->method('getDelivery')
            ->willReturn($delivery);

        $buildData = $this->subject->build($this->deliveryExecution, $this->lunchUrl);

        $this->assertEquals([], $buildData);
    }
}
