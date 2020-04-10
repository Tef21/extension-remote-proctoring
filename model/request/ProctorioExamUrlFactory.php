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

use tao_helpers_Uri;

class ProctorioExamUrlFactory
{
    /** @var string */
    private $rootURl;

    public function __construct(string $rootURl = null)
    {
        $this->rootURl = $rootURl ?? tao_helpers_Uri::getRootUrl();
    }

    public function createExamStartUrl(): string
    {
        return $this->convertUrlToPattern($this->rootURl . '/remoteProctoring') . '.*';
    }

    public function createExamTakeUrl(): string
    {
        return $this->convertUrlToPattern($this->rootURl) . '/.*';
    }

    public function createExamEndUrl(): string
    {
        return $this->convertUrlToPattern(sprintf('%s/taoDelivery/DeliveryServer/index', $this->rootURl)) . '/.*';
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
}
