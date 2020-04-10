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
use oat\oatbox\log\LoggerService;
use oat\oatbox\user\User;
use oat\oatbox\user\UserService;
use oat\remoteProctoring\model\request\ProctorioExamUrlFactory;
use oat\remoteProctoring\model\request\ProctorioRequestBuilder;
use oat\remoteProctoring\model\request\RequestHashGenerator;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class ProctorioRequestBuilderTest extends TestCase
{

    /** @var MockObject|DeliveryExecutionInterface */
    private $deliveryExecution;

    /** @var string */
    private $lunchUrl;

    /** @var ProctorioRequestBuilder */
    private $subject;

    /** * @var UserService|MockObject */
    private $userSerice;

    public function setUp(): void
    {
        $this->userSerice = $this->createMock(UserService::class);
        $this->userSerice->method('getUser')
            ->willReturn($this->createMock(User::class));

        $requestHashGenerator = $this->createMock(RequestHashGenerator::class);
        $proctorioExamUrlFactory = $this->createMock(ProctorioExamUrlFactory::class);
        $this->deliveryExecution = $this->createMock(DeliveryExecutionInterface::class);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            UserService::SERVICE_ID => $this->userSerice,
            RequestHashGenerator::SERVICE_ID => $requestHashGenerator,
            ProctorioExamUrlFactory::SERVICE_ID => $proctorioExamUrlFactory,
            LoggerService::SERVICE_ID => $this->createMock(LoggerInterface::class)
        ]);

        $this->lunchUrl = 'someLunchUrl.tld';

        $this->subject = new ProctorioRequestBuilder(
            [
                'requestHashGenerator' => $requestHashGenerator,
                'proctorioExamUrlFactory' => $proctorioExamUrlFactory,
                'exam_settings' => ['someSetting']
            ]
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
        $expectedData = [
            'launch_url' => 'someLunchUrl.tld',
            'user_id' => '',
            'fullname' => '',
            'exam_start' => '',
            'exam_take' => '',
            'exam_end' => '',
            'exam_settings' => ['someSetting'],
            'exam_tag' => '',
        ];

        $this->assertEquals($expectedData, $buildData);
    }
}
