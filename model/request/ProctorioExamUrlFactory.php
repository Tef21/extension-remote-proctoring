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

namespace oat\remoteProctoring\model\request;

use oat\oatbox\service\ConfigurableService;
use tao_helpers_Uri;

class ProctorioExamUrlFactory extends ConfigurableService
{
    public const OPTION_BASE_URL = 'base_url';

    public function createExamStartUrl(): string
    {
        return $this->convertUrlToPattern(rtrim($this->getRootURl(), '/') . '/remoteProctoring') . '.*';
    }

    public function createExamTakeUrl(): string
    {
        return $this->convertUrlToPattern(rtrim($this->getRootURl(), '/')) . '/.*';
    }

    public function createExamEndUrl(): string
    {
        return $this->convertUrlToPattern(sprintf('%s/taoDelivery/DeliveryServer/index', rtrim($this->getRootURl(), '/'))) . '/.*';
    }

    private function convertUrlToPattern(string $url): string
    {
        return str_replace(
            ['.', '/', '+', '*', '?', '[', '^', ']', '$', '(', ')', '{', '}', '=', '!', '<', '>', '|', ':', '-', '#'],
            [
                '\.', '\/', '\+', '\*', '\?', '\[', '\^', '\]', '\$', '\(', '\)', '\{', '\}', '\=', '\!', '\<', '\>',
                '\|', '\:', '\-', '\#'
            ],
            $url
        );
    }

    /**
     * @return string
     */
    public function getRootURl(): string
    {
        return $this->getOption(self::OPTION_BASE_URL) ?? tao_helpers_Uri::getRootUrl();
    }
}
