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

namespace oat\remoteProctoring\test\unit\model\response;

use oat\generis\test\TestCase;
use oat\remoteProctoring\model\response\ProctorioResponse;

class ProctorioResponseTest extends TestCase
{
    const TESTTAKER_URL = 'testtakerURL';
    const REVIEW_URL = 'reviewURL';
    /** * @var string */
    private $json;

    /** * @var string */
    private $testTakerUrl;

    /** * @var string */
    private $reviewURL;

    /** * @var ProctorioResponse */
    private $subject;

    protected function setUp(): void
    {
        $this->testTakerUrl = self::TESTTAKER_URL;
        $this->reviewURL = self::REVIEW_URL;
        $this->json = sprintf('["%s","%s"]', $this->testTakerUrl, $this->reviewURL);
        $this->subject = new ProctorioResponse($this->testTakerUrl, $this->reviewURL);
    }

    public function testFromJson(): void
    {
        $localSubject = ProctorioResponse::fromJson($this->json);
        $this->assertEquals($this->subject, $localSubject);
        $this->assertInstanceOf(ProctorioResponse::class, $localSubject);
    }

    public function testGetTestTakerUrl(): void
    {
        $this->assertEquals($this->testTakerUrl, $this->subject->getTestTakerUrl());
    }

    public function testGetTestReviewerUrl(): void
    {
        $this->assertEquals($this->reviewURL, $this->subject->getTestReviewerUrl());
    }

    public function testToJson(): void
    {
        $this->assertEquals($this->json, $this->subject->toJson());
    }
}
