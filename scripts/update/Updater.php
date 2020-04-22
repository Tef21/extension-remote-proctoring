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

namespace oat\remoteProctoring\scripts\update;

use common_ext_ExtensionUpdater;
use oat\tao\model\security\Business\Contract\SecuritySettingsRepositoryInterface;

class Updater extends common_ext_ExtensionUpdater
{
    /**
     * @inheritDoc
     */
    public function update($initialVersion)
    {
        $this->skip('1.0.0', '1.0.2');

        if ($this->isVersion('1.0.2')) {
            /** @var SecuritySettingsRepositoryInterface $settingsRepository */
            $settingsRepository = $this->getServiceManager()->get(SecuritySettingsRepositoryInterface::SERVICE_ID);
            $securitySettings = $settingsRepository->findAll();
            $values = explode(PHP_EOL, $securitySettings->findContentSecurityPolicyWhitelist()->getValue());
            $values = array_merge($values, ['https://getproctorio.com', ROOT_URL]);
            $securitySettings->findContentSecurityPolicy()->setValue('list');
            $securitySettings->findContentSecurityPolicyWhitelist()->setValue(implode(PHP_EOL, array_unique($values)));
            $settingsRepository->persist($securitySettings);
            $this->setVersion('1.0.3');
        }
    }
}
