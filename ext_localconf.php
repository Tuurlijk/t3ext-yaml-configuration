<?php
defined('TYPO3_MODE') or die();

/**
 * This file is part of the "yaml_configuration" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


// Register command controllers
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
    \MaxServ\YamlConfiguration\Command\TsConfigCommandController::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
    \MaxServ\YamlConfiguration\Command\ExportCommandController::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
    \MaxServ\YamlConfiguration\Command\ImportCommandController::class;
