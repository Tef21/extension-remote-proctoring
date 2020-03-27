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

namespace oat\remoteProctoring\scripts;

use oat\oatbox\extension\AbstractAction;
use oat\remoteProctoring\model\ProctorioApiService;
use \common_report_Report as Report;

class ProctorioBuildUrlAction extends AbstractAction
{

    public function __invoke($params)
    {
        /** @var ProctorioApiService $proctorioApiService */
        $proctorioApiService = $this->getServiceLocator()->get(ProctorioApiService::class);
        foreach ($params as $param) {
            $deliveryExecutionId = strpos($param, '%23') !== false
                ? urldecode($param)
                : $param;
            $this->logInfo('Run get Proctorio Url for '.$deliveryExecutionId);
            [$tt, $proctor] = $proctorioApiService->getProctorioUrl($deliveryExecutionId);
            echo 'URLS for ' . $deliveryExecutionId.PHP_EOL;
            echo '   Testtaker: '.$tt.PHP_EOL;
            echo '   Proctor  : '.$tt.PHP_EOL;
        }
        return new Report(Report::TYPE_SUCCESS);
    }
}