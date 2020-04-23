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
use oat\remoteProctoring\model\request\ProctorioExamUrlFactory;

class ProctorioExamUrlFactoryTest extends TestCase
{
    private const ROOT_URL = 'http://TEST_./+*?[^]$(){}=!<>|:-#';
    private const ROOT_URL_CONVERTED = 'http\:\/\/TEST_\.\/\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:\-\#';

    /** @var ProctorioExamUrlFactory */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ProctorioExamUrlFactory(['base_url' => self::ROOT_URL]);
    }

    public function testCreateExamStartUrl(): void
    {
        $this->assertSame(
            self::ROOT_URL_CONVERTED . '\/remoteProctoring.*',
            $this->subject->createExamStartUrl()
        );
    }

    public function testCreateExamEndUrl(): void
    {
        $this->assertSame(

            $this->subject->createExamEndUrl()
        );
    }

    public function testCreateExamTakeUrl(): void
    {
        $this->assertSame(
            self::ROOT_URL_CONVERTED . '\/taoDelivery\/DeliveryServer\/runDeliveryExecution.*',
            $this->subject->createExamTakeUrl()
        );
    }
}
