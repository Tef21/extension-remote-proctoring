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

namespace oat\remoteProctoring\test\unit\model\signature;

use oat\generis\test\TestCase;
use oat\remoteProctoring\model\signature\Sha256Signature;
use oat\remoteProctoring\model\signature\exception\SignatureException;
use Psr\Http\Message\RequestInterface;

class Sha256SignatureTest extends TestCase
{

    /**
     * @var Sha256Signature
     */
    private $subject;

    public function urlsProvider(): array
    {
        return [
            [
                'https://tao.lu',
                'https://tao.lu&signature=ae0d9df0a858f22a5ef4cab17b8f5b215c552867af0880ade4fa39311e7383d7'
            ],
        ];
    }

    protected function setUp(): void
    {
        $this->subject = new Sha256Signature([Sha256Signature::OPTION_SECRET => 'a']);
    }

    /**
     * @dataProvider urlsProvider
     */
    public function testSignUrl(string $original, string $signed): void
    {
        $this->assertEquals($signed, $this->subject->signUrl($original));
    }

    public function testValidateRequest(): void
    {
        $this->assertNull(
            $this->subject->validateRequest(
                $this->getMockRequest(
                    'https://tao.lu&signature=ae0d9df0a858f22a5ef4cab17b8f5b215c552867af0880ade4fa39311e7383d7'
                )
            )
        );
    }

    public function testInvalidSignature(): void
    {
        $this->expectException(SignatureException::class);
        $this->expectExceptionMessage('Invalid Signature');
        $this->assertNull($this->subject->validateRequest($this->getMockRequest('https://tao.lu&signature=invalid')));
    }

    public function testMissingSignature(): void
    {
        $this->expectException(SignatureException::class);
        $this->expectExceptionMessage('Missing Signature');
        $this->assertNull($this->subject->validateRequest($this->getMockRequest('https://tao.lu&pamssss&asda')));
    }

    protected function getMockRequest($uri): RequestInterface
    {
        $mock = $this->createMock(RequestInterface::class);
        $mock->method('getUri')->willReturn($uri);
        return $mock;
    }
}
