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
use oat\remoteProctoring\model\request\ProctorioExamUrlFactory;
use oat\remoteProctoring\model\request\ProctorioRequestBuilder;
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

    /** * @var int */
    private $time;

    /** * @var string */
    private $userFullName;

    /** @var string */
    private $nonce;

    /** * @var ProctorioExamUrlFactory|MockObject */
    private $proctorioExamUrlFactory;

    protected function setUp(): void
    {
        $this->deliveryExecution = $this->getMockBuilder(DeliveryExecutionInterface::class)
            ->getMock();
        $this->lunchUrl = 'someTestUrl.tld';

        $this->time = time();
        $this->userFullName = 'userFull Name';
        $this->nonce = 'abc1234';
        $this->proctorioExamUrlFactory = $this->createMock(ProctorioExamUrlFactory::class);

        $this->subject = new ProctorioRequestBuilder(
            $this->time,
            $this->userFullName,
            $this->nonce,
            $this->proctorioExamUrlFactory
        );
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

        $options = [
            'exam_settings' => ['webtraffic'],
        ];
        $buildData = $this->subject->build($this->deliveryExecution, $this->lunchUrl);

        $expected = [
            'launch_url' => 'someTestUrl.tld',
            'user_id' => 'test',
            'fullname' => 'userFull Name',
            'exam_start' => '',
            'exam_take' => '',
            'exam_end' => '',
            'exam_settings' => ['webtraffic'],
            'exam_tag' => 'test-Label',
            'oauth_timestamp' => '',
            'oauth_nonce' => ''
        ];

        $this->assertEquals(array_keys($expected), array_keys($buildData));
        $this->assertEquals($expected['user_id'], $buildData['user_id']);
        $this->assertEquals($expected['exam_start'], $buildData['exam_start']);
        $this->assertEquals($expected['exam_take'], $buildData['exam_take']);
        $this->assertEquals($expected['exam_end'], $buildData['exam_end']);
        $this->assertEquals($expected['exam_settings'], $buildData['exam_settings']);
        $this->assertEquals($expected['fullname'], $buildData['fullname']);
    }
}
