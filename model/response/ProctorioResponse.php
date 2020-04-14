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

namespace oat\remoteProctoring\model\response;

use RuntimeException;

class ProctorioResponse
{
    /** @var string */
    private $testTakerUrl;

    /** @var string */
    private $testReviewerUrl;

    public function __construct(string $testTakerUrl, string $testReviewerUrl)
    {
        $this->testTakerUrl = $testTakerUrl;
        $this->testReviewerUrl = $testReviewerUrl;
    }

    public function getTestTakerUrl(): string
    {
        return $this->testTakerUrl;
    }

    public function getTestReviewerUrl(): string
    {
        return $this->testReviewerUrl;
    }

    public static function fromJson(string $json): ProctorioResponse
    {
        $data = json_decode($json, true);

        if (!empty($data) && count($data) === 2) {
            return new self($data[0], $data[1]);
        }

        throw new RuntimeException('Proctorio response Format is not proper');
    }

    public function toJson(): string
    {
        return json_encode([
            $this->getTestTakerUrl(),
            $this->getTestReviewerUrl(),
        ]);
    }

    public function __toString()
    {
        return $this->toJson();
    }
}