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

namespace oat\remoteProctoring\test\unit\model\authorization;

use oat\generis\test\TestCase;
use oat\remoteProctoring\model\authorization\CookieSetUpService;

class CookieSetupServiceTest extends TestCase
{
    /** @var CookieSetUpService */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new CookieSetUpService();
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetUpWillAddSameSiteOptionToPath(): void
    {
        session_start();

        $this->subject->setUp();

        $cookieParams = session_get_cookie_params();

        if (!$this->isPhpVersionGreaterThan72()) {
            $this->assertSame('/; samesite=none', $cookieParams['path']);
        }

        if ($this->isPhpVersionGreaterThan72()) {
            $this->assertSame('none', $cookieParams['samesite']);
        }

        $this->assertSame(session_status(), PHP_SESSION_ACTIVE);
    }

    private function isPhpVersionGreaterThan72(): bool
    {
        return phpversion() >= '7.3.0';
    }
}
