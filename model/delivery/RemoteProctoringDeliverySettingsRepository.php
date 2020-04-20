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

use common_Exception;
use common_exception_NotFound;
use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\generis\model\OntologyAwareTrait;

class RemoteProctoringDeliverySettingsRepository extends ConfigurableService
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'remoteProctoring/RemoteProctoringDeliverySettingsRepository';
    public const ONTOLOGY_DELIVERY_SETTINGS = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#RemoteProctoringDeliverySettings';
    public const ONTOLOGY_PROCTORING_ENABLED = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#EnableRemoteProctoring';

    /**
     * @throws common_Exception
     * @throws common_exception_NotFound
     */
    public function findByDeliveryExecution(DeliveryExecutionInterface $deliveryExecution): RemoteProctoringDeliverySettings
    {
        $settings = $deliveryExecution
            ->getDelivery()
            ->getPropertyValues($this->getProperty(self::ONTOLOGY_DELIVERY_SETTINGS));

        return new RemoteProctoringDeliverySettings(
            in_array(
                self::ONTOLOGY_PROCTORING_ENABLED,
                $settings
            )
        );
    }
}
