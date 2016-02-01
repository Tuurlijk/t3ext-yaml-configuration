<?php
defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {
    // Register command controllers
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
        \MaxServ\YamlConfiguration\Command\TsConfigCommandController::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
        \MaxServ\YamlConfiguration\Command\ExportCommandController::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
        \MaxServ\YamlConfiguration\Command\ImportCommandController::class;

    // Register command map hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] =
        \MaxServ\YamlConfiguration\Hook\TceMain::class;
}
