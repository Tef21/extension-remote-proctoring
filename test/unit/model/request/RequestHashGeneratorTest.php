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

use oat\generis\test\TestCase;
use oat\remoteProctoring\model\request\RequestHashGenerator;

class RequestHashGeneratorTest extends TestCase
{
    /** @var RequestHashGenerator */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new RequestHashGenerator();
    }

    public function testHash()
    {
        $testString = 'someString';
        $this->assertEquals((string)md5($testString), $this->subject->hash($testString));
    }

    public function testGet()
    {
        $this->assertContains('md5', $this->subject->getAlgorithms());
    }
}
