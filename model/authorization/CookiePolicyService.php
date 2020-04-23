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

/**
 * This class is necessary to override cookie policy required by remote proctoring to run the test inside an iFrame.
 */
class CookiePolicyService extends ConfigurableService
{
    private const SAME_SITE = 'samesite';
    public const SAME_SITE_NONE = 'none';

    public function setSameSitePolicy(string $policy): void
    {
        $cookieParams = session_get_cookie_params();

        if ($this->requiresUpdatePolicy($cookieParams, self::SAME_SITE, $policy)) {
            $this->commitSessionIfNeeded();
            $this->setPolicy($cookieParams, self::SAME_SITE, $policy);
            $this->startSessionIfNeeded();
        }
    }

    private function setPolicy(array $cookieParams, string $policyName, string $policy): void
    {
        if ($this->isPhpVersionGreaterThan72()) {
            $cookieParams[$policyName] = $policy;

            session_set_cookie_params($cookieParams);

            return;
        }

        session_set_cookie_params(
            $cookieParams['lifetime'],
            $cookieParams['path'] . $this->getPathPolicy($policyName, $policy),
            $cookieParams['domain'],
            $cookieParams['secure'],
            $cookieParams['httponly']
        );
    }

    private function isPhpVersionGreaterThan72(): bool
    {
        return phpversion() >= '7.3.0';
    }

    private function commitSessionIfNeeded(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_commit();
        }
    }

    private function startSessionIfNeeded(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function requiresUpdatePolicy(array $cookieParams, string $policyName, string $policy): bool
    {
        if ($this->isPhpVersionGreaterThan72()) {
            return ($cookieParams[$policyName] ?? '') !== $policy;
        }

        return strpos($cookieParams['path'], $this->getPathPolicy($policyName, $policy)) === false;
    }

    private function getPathPolicy(string $policyName, string $policy): string
    {
        return sprintf('; %s=%s', $policyName, $policy);
    }
}
