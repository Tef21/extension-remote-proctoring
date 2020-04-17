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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 *
 */

use oat\remoteProctoring\scripts\install\RegisterAuthorizationProvider;
use oat\tao\model\user\TaoRoles;

/**
 * Generated using taoDevTools 6.5.0
 */

return [
    'name' => 'remoteProctoring',
    'label' => 'Remote Proctoring extension that allows you to use a remote proctoring solution',
    'description' => 'This extension provides functionality to integrate with Proctorio tool',
    'license' => 'GPL-2.0',
    'version' => '0.2.0',
    'author' => 'Open Assessment Technologies SA',
    'requires' => [
        'generis' => '>=12.17.0',
        'taoDelivery' => '14.10.1',
        'tao' => '>=41.12.0',
    ],
    'managementRole' => 'http://www.tao.lu/Ontologies/generis.rdf#remoteProctoringManager',
    'acl' => [
        ['grant', 'http://www.tao.lu/Ontologies/generis.rdf#remoteProctoringManager', ['ext' => 'remoteProctoring']],
        ['grant', TaoRoles::ANONYMOUS, ['ext' => 'remoteProctoring', 'mod' => 'DeliveryLaunch', 'act' => 'launch']],
    ],
    'install' => [
        'php' => [
            RegisterAuthorizationProvider::class
        ],
        'rdf' => [
            __DIR__.'/scripts/install/remoteProctoring.rdf'
        ]
    ],
    'uninstall' => [
    ],
    'update' => 'oat\\remoteProctoring\\scripts\\update\\Updater',
    'routes' => [
        '/remoteProctoring' => 'oat\\remoteProctoring\\controller',
    ],
    'constants' => [
        # views directory
        'DIR_ACTIONS' => __DIR__ . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR,

        #BASE URL (usually the domain root)
        'BASE_URL' => ROOT_URL . 'remoteProctoring/',
    ],
    'extra' => [
    ],
];
