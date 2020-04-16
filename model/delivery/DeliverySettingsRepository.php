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

namespace oat\remoteProctoring\model\delivery;

use common_exception_NotFound;
use common_persistence_KeyValuePersistence;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;

class DeliverySettingsRepository extends ConfigurableService
{
    private const STORAGE_KEY_PATTERN = 'proctorio:delivery:%s';

    /**
     * @throws common_exception_NotFound
     */
    public function findByDeliveryExecution(DeliveryExecutionInterface $deliveryExecution): DeliverySettings
    {
        $settings = json_decode(
            $this->getPersistence()->get($this->getStorageSettingKey($deliveryExecution)),
            true
        );

        return new DeliverySettings(!empty($settings['enabled']));
    }

    /**
     * @throws common_exception_NotFound
     */
    private function getStorageSettingKey(DeliveryExecutionInterface $deliveryExecution): string
    {
        return sprintf(
            self::STORAGE_KEY_PATTERN,
            $deliveryExecution->getIdentifier() //@TODO Must be the DeliveryId, considering this for test purposes
        );
    }

    private function getPersistence(): common_persistence_KeyValuePersistence
    {
        $persistence = $this->getOption('persistence');

        if ($persistence instanceof common_persistence_KeyValuePersistence) {
            return $persistence;
        }

        return $this->getServiceLocator()
            ->get(PersistenceManager::SERVICE_ID)
            ->getPersistenceById($persistence);
    }
}
