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

namespace oat\remoteProctoring\scripts\install;

use common_report_Report;
use oat\oatbox\extension\InstallAction;
use oat\remoteProctoring\model\SecurityPolicyConfigurator;

class SetupSecurityPolicy extends InstallAction
{
    public function __invoke($params)
    {
        /** @var  SecurityPolicyConfigurator $policyConfigurator */
        $policyConfigurator = $this->getServiceManager()->get(SecurityPolicyConfigurator::class);
        $policyConfigurator->configureIFramePolicy();

        return common_report_Report::createSuccess('IFrame policy successfully set to allow Proctorio communication');
    }
}
