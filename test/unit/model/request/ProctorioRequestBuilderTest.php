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

use common_exception_Error;
use common_exception_NotFound;
use core_kernel_classes_Resource;
use oat\generis\test\TestCase;
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

    protected function setUp(): void
    {
        $this->deliveryExecution = $this->getMockBuilder(DeliveryExecutionInterface::class)
            ->getMock();
        $this->lunchUrl = 'someTestUrl.tld';

        $this->subject = new class extends ProctorioRequestBuilder {
            protected function getExamUrl(): string
            {
                return 'examURL';
            }

            protected function getUserFullName(DeliveryExecutionInterface $deliveryExecution): string
            {
                return 'Username';
            }

            protected function getNonce(): string
            {
                return 'randomUUID';
            }

            protected function getTime(): int
            {
                return 123456;
            }
        };
    }

    /**
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

        $options = [];
        $buildData = $this->subject->build($this->deliveryExecution, $this->lunchUrl, $options);

        $expected = [
            'launch_url' => 'someTestUrl.tld',
            'user_id' => 'test',
            'oauth_consumer_key' => null,
            'exam_start' => 'someTestUrl.tld',
            'exam_take' => 'examURL',
            'exam_end' => 'examURL',
            'exam_settings' => null,
            'fullname' => 'Username',
            'exam_tag' => 'test-Label',
            'oauth_timestamp' => '',
            'oauth_nonce' => ''
        ];

        $this->assertEquals(array_keys($expected), array_keys($buildData));
        $this->assertEquals($expected['user_id'], $buildData['user_id']);
        $this->assertEquals($expected['oauth_consumer_key'], $buildData['oauth_consumer_key']);
        $this->assertEquals($expected['exam_start'], $buildData['exam_start']);
        $this->assertEquals($expected['exam_take'], $buildData['exam_take']);
        $this->assertEquals($expected['exam_end'], $buildData['exam_end']);
        $this->assertEquals($expected['exam_settings'], $buildData['exam_settings']);
        $this->assertEquals($expected['fullname'], $buildData['fullname']);
    }
}
