<?php

/**
 * This file is part of the "yaml_configuration" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Configure your TYPO3 site using YAML files',
    'description' => 'Export and import any table to and from a YAML file. Generate TSConfig from YAML files.',
    'category' => 'BE',
    'author' => 'Michiel Roos',
    'author_email' => 'michiel@maxserv.com',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => 'typo3temp/tx_yamlconfiguration/',
    'clearCacheOnLoad' => 1,
    'author_company' => 'MaxServ B.V.',
    'version' => '1.0.12',
    'constraints' => [
        'depends' => [
            'typo3' => '6.2.0-9.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
