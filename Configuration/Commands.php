<?php

/**
 * This file is part of the "yaml_configuration" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Commands to be executed by typo3, where the key of the array
 * is the name of the command (to be called as the first argument after typo3).
 * Required parameter is the "class" of the command which needs to be a subclass
 * of Symfony/Console/Command.
 *
 * example: bin/typo3 yaml:import
 */
return [
    'yaml:import' => [
        'class' => \MichielRoos\YamlConfiguration\Command\ImportTableCommand::class,
        'schedulable' => false,
    ],
    'yaml:export' => [
        'class' => \MichielRoos\YamlConfiguration\Command\ExportTableCommand::class,
        'schedulable' => false,
    ],
];
