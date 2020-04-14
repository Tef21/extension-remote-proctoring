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

namespace oat\remoteProctoring\test\unit\model;

use GuzzleHttp\Psr7\Request;
use oat\generis\test\TestCase;
use oat\remoteProctoring\model\LaunchService;
use oat\remoteProctoring\model\signature\exception\SignatureException;
use oat\remoteProctoring\model\signature\SignatureMethod;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;

class LaunchServiceTest extends TestCase
{
    /**
     * @var LaunchService
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new LaunchService([LaunchService::OPTION_SIGNATURE_METHOD => $this->getSigner()]);
    }

    public function testValidateRequest()
    {
        $this->assertNull($this->subject->validateRequest($this->getRequest('https://google.com&validSignature')));

        $this->expectException(SignatureException::class);
        $this->subject->validateRequest($this->getRequest('https://google.com&invalidSignature'));
    }

    public function testGenerateUrl()
    {
        $this->assertEquals('signed', $this->subject->generateUrl('ss', 'id'));
    }

    private function getRequest(string $uri): RequestInterface
    {
        return new Request('get', $uri);
    }

    protected function getSigner(): SignatureMethod
    {
        $signerProphecy = $this->prophesize(SignatureMethod::class);

        $signerProphecy->signUrl(Argument::any())->willReturn('signed');

        $signerProphecy->validateRequest($this->getRequest('https://google.com&validSignature'));
        $signerProphecy->validateRequest($this->getRequest('https://google.com&invalidSignature'))->willThrow(
            new SignatureException('signature')
        );

        return $signerProphecy->reveal();
    }
}
