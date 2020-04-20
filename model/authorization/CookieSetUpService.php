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

namespace oat\remoteProctoring\model\authorization;

use oat\oatbox\service\ConfigurableService;

class CookieSetUpService extends ConfigurableService
{
    private const SAME_SITE_VALUE = 'samesite=none';

    public function setUp(): void
    {
        //@TODO @FIXME By some reason we are loosing the session context data. Without this the proctorio does not work. Check with @joel
        $cookieParams = session_get_cookie_params();

        if (strpos($cookieParams['path'] ?? '', self::SAME_SITE_VALUE) === false) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_commit();
            }

            session_set_cookie_params(
                $cookieParams['lifetime'],
                $cookieParams['path'] . '; ' . self::SAME_SITE_VALUE,
                $cookieParams['domain'],
                $cookieParams['secure'],
                $cookieParams['httponly']
            );

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        }
    }
}
