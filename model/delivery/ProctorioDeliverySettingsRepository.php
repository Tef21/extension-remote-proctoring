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
use core_kernel_classes_Resource;
use core_kernel_classes_Property;
use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\generis\model\OntologyAwareTrait;

class ProctorioDeliverySettingsRepository extends ConfigurableService
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'remoteProctoring/ProctorioDeliverySettingsRepository';
    public const ONTOLOGY_DELIVERY_SETTINGS = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ProctorioDeliverySettings';
    public const ONTOLOGY_ENABLE_SETTING = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ProctorioDeliverySettings';

    /**
     * @throws common_Exception
     * @throws common_exception_NotFound
     */
    public function findByDeliveryExecution(DeliveryExecutionInterface $deliveryExecution): ProctorioDeliverySettings
    {
        /** @var core_kernel_classes_Resource $delivery */
        $delivery = $deliveryExecution
            ->getDelivery();

        $properties = $delivery->getPropertiesValues(
            [
                new core_kernel_classes_Property(self::ONTOLOGY_DELIVERY_SETTINGS),
            ]
        );

        $isEnabled = false;

        /** @var core_kernel_classes_Resource $resource */
        foreach ($properties[self::ONTOLOGY_DELIVERY_SETTINGS] ?? [] as $resource) {
            $isEnabled = $resource->getUri() === self::ONTOLOGY_ENABLE_SETTING;
        }

        return new ProctorioDeliverySettings($isEnabled);
    }
}
