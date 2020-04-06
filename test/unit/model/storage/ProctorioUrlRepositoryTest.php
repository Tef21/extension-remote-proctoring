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

namespace oat\remoteProctoring\test\unit\model\storage;

use common_persistence_KeyValuePersistence;
use oat\generis\test\TestCase;
use oat\oatbox\log\LoggerService;
use oat\remoteProctoring\model\response\ProctorioResponse;
use oat\remoteProctoring\model\storage\ProctorioUrlRepository;
use PHPUnit\Framework\MockObject\MockObject;

class ProctorioUrlRepositoryTest extends TestCase
{

    /** @var common_persistence_KeyValuePersistence|MockObject */
    private $persistence;

    /** @var LoggerService|MockObject */
    private $loggerMock;

    /** @var ProctorioUrlRepository $subject */
    private $subject;

    protected function setup(): void
    {
        $this->persistence = $this->createMock(common_persistence_KeyValuePersistence::class);
        $this->loggerMock = $this->createMock(LoggerService::class);
        $this->subject = new ProctorioUrlRepository($this->persistence, $this->loggerMock);
    }

    public function testFindById(): void
    {
        $this->persistence->method('get')->willReturn('["ttURL","reviewURL"]');

        $expected =  new ProctorioResponse('ttURL', 'reviewURL');

        $data = $this->subject->findById('someString');
        $this->assertInstanceOf(ProctorioResponse::class, $data);
        $this->assertEquals($expected, $data);
    }

    public function testSave(): void
    {
        $id = 'someId';
        $response = new ProctorioResponse('test', 'review');
        $this->persistence
            ->method('set')
            ->with($id, $response->toJson())
            ->willReturn(true);

        $this->assertTrue($this->subject->save($response, $id));
    }
}
