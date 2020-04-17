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
use core_kernel_classes_Container;
use core_kernel_classes_EmptyProperty;
use oat\generis\model\GenerisRdf;
use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\generis\model\OntologyAwareTrait;

class ProctorioDeliverySettingsRepository extends ConfigurableService
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'remoteProctoring/ProctorioDeliverySettingsRepository';

    /**
     * @throws common_Exception
     * @throws core_kernel_classes_EmptyProperty
     * @throws common_exception_NotFound
     */
    public function findByDeliveryExecution(DeliveryExecutionInterface $deliveryExecution): ProctorioDeliverySettings
    {
        /** @var core_kernel_classes_Container $enabled */
        $enabled = $deliveryExecution
            ->getDelivery()
            ->getUniquePropertyValue(
                $this->getProperty('http://www.tao.lu/Ontologies/TAODelivery.rdf#EnableRemoteProctoring')
            );

        return new ProctorioDeliverySettings($enabled->equals($this->getResource(GenerisRdf::GENERIS_TRUE)));
    }
}
