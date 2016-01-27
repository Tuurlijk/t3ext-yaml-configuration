<?php
defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
        \MaxServ\YamlConfiguration\Command\YamlConfigurationCommandController::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
        \MaxServ\YamlConfiguration\Command\ExportCommandController::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
        \MaxServ\YamlConfiguration\Command\ImportCommandController::class;
}
