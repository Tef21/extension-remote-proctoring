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
use oat\oatbox\log\LoggerService;
use oat\remoteProctoring\model\response\ProctorioResponseValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class ProctorioResponseValidatorTest extends TestCase
{

    /** * @var LoggerService|MockObject */
    private $loggerMock;

    /** * @var ProctorioResponseValidator */
    private $subject;

    protected function setUp(): void
    {
        $serviceLocator = $this->getServiceLocatorMock([
            LoggerService::SERVICE_ID => $this->createMock(LoggerInterface::class)
        ]);

        $this->subject = new ProctorioResponseValidator();
        $this->subject->setServiceLocator($serviceLocator);
    }

    public function testValidate(): void
    {
        $testTakerUrl = 'testtakerURL';
        $reviewURL = 'reviewURL';
        $testString = sprintf('["%s","%s"]', $testTakerUrl, $reviewURL);
        $this->assertTrue($this->subject->validate($testString));
    }

    /**
     * @dataProvider dataProviderFailedCases
     * @param string $testString
     */
    public function testValidateFailed(string $testString): void
    {
        $this->assertFalse($this->subject->validate($testString));
    }

    public function dataProviderFailedCases(): array
    {
        return [
            ['[]'],
            ['[2653]'],
            ['[2654]'],
            ['[2655]'],
            ['[2656]'],
            ['[2657]'],
            ['[2658]'],
            ['[2659]'],
        ];
    }
}