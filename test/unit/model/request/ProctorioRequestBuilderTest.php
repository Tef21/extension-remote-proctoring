<?php

namespace oat\remoteProctoring\test\unit\model\request;

use oat\generis\test\TestCase;
use oat\remoteProctoring\request\ProctorioRequestBuilder;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;

class ProctorioRequestBuilderTest extends TestCase
{
    /** @var ProctorioRequestBuilder $subject */
    private $subject;
    private $deliveryExecution;
    private $lunchUrl;

    protected function setUp(): void
    {
        $this->subject = new ProctorioRequestBuilder();
        $this->deliveryExecution = $this->getMockBuilder(DeliveryExecutionInterface::class)
            ->getMock();
        $this->lunchUrl = 'someTestUrl.tld';

    }

    public function buildTest(): void
    {
        $options = [

        ];
        $buildData = $this->subject->build($this->deliveryExecution . $this->lunchUrl, $options);

        $this->assertEquals([], $buildData);
    }
}
