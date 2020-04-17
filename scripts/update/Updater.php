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
 * Copyright (c) 2014-2017 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\remoteProctoring\scripts\update;

use common_Exception;
use oat\tao\scripts\update\OntologyUpdater;

/**
 *
 * @author Joel Bout <joel@taotesting.com>
 */
class Updater extends \common_ext_ExtensionUpdater
{

    /**
     *
     * @param $initialVersion
     * @return string $initialVersion
     * @throws \common_exception_Error
     * @throws \common_exception_InconsistentData
     * @throws \common_ext_ExtensionException
     * @throws common_Exception
     */
    public function update($initialVersion)
    {
        if ($this->isVersion('0.0.0')) {
            $this->setVersion('0.1.0');
        }
        
        if ($this->isVersion('0.1.0')) {
            OntologyUpdater::syncModels();
            $this->setVersion('0.2.0');
        }
    }
}
