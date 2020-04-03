<?php

namespace oat\remoteProctoring\test\unit\model\request;

use core_kernel_classes_Resource;
use oat\generis\test\TestCase;
use oat\remoteProctoring\model\request\ProctorioRequestBuilder;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use PHPUnit\Framework\MockObject\MockObject;

class ProctorioRequestBuilderTest extends TestCase
{
    /** @var ProctorioRequestBuilder $subject */
    private $subject;
    /** @var MockObject|DeliveryExecutionInterface */
    private $deliveryExecution;
    private $lunchUrl;

    protected function setUp(): void
    {
        $this->subject = new class extends ProctorioRequestBuilder {
            protected function getExamUrl(): string
            {
                return 'examURL';
            }

            protected function getUserFullName(): string
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
        $this->deliveryExecution = $this->getMockBuilder(DeliveryExecutionInterface::class)
            ->getMock();
        $this->lunchUrl = 'someTestUrl.tld';

    }

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

        ];
        $buildData = $this->subject->build($this->deliveryExecution, $this->lunchUrl, $options);

        $expected = [
            'launch_url' => 'someTestUrl.tld',
            'user_id' => 'test',
            'oauth_consumer_key' => null,
            'exam_start' => 'someTestUrl.tld',
            'exam_take' => 'test',
            'exam_end' => 'test',
            'exam_settings' => null,
            'fullname' => '',
            'exam_tag' => 'test-Label',
            'oauth_timestamp' => '',
            'oauth_nonce' => ''
        ];

        $this->assertEquals(array_keys($expected), array_keys($buildData));
        $this->assertEquals($expected['launch_url'], $buildData['launch_url']);
    }
}
