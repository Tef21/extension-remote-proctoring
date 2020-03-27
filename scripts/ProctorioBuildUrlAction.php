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
        $deliveryExecutionId = urldecode('kve_de_https%3A%2F%2Ftao33.bout%2Frdf%23i5e7dae79e6ded304522da34e3587659e3b');
        /** @var ProctorioApiService $proctorioApiService */
        $proctorioApiService = $this->getServiceLocator()->get(ProctorioApiService::class);

        $urls = $proctorioApiService->getProctorioUrl($deliveryExecutionId);
        echo 'URLS:'.PHP_EOL;
        foreach ($urls as $url) {
            echo $url.PHP_EOL;
        }

        $this->logInfo('Run get Proctorio Urls');
        if ($urls) {
            return new Report(Report::TYPE_SUCCESS, __('URls successfully retrieved'));
        }

        return new Report(Report::TYPE_WARNING, __('URls not retrieved'));
    }
}