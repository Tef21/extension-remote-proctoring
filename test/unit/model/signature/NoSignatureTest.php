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

use GuzzleHttp\Psr7\Request;
use oat\generis\test\TestCase;
use oat\remoteProctoring\model\signature\NoSignature;
use Psr\Http\Message\RequestInterface;

class NoSignatureTest extends TestCase
{
    /**
     * @var NoSignature
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new NoSignature([]);
    }

    public function testSignUrl(): void
    {
        $this->assertEquals('https://tao.lu', $this->subject->signUrl('https://tao.lu'));
        $this->assertNotEquals('https://tao.lu&signed', $this->subject->signUrl('https://tao.lu'));
    }

    public function testValidateRequest(): void
    {
        $this->assertNull($this->subject->validateRequest($this->getRequest('https://tao.lu')));
    }

    private function getRequest(string $uri): RequestInterface
    {
        return new Request('get', $uri);
    }
}
