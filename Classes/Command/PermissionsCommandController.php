<?php
namespace MaxServ\Permissions\Command;

/**
 *  Copyright notice
 *
 *  ⓒ 2016 Michiel Roos <michiel@maxserv.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is free
 *  software; you can redistribute it and/or modify it under the terms of the
 *  GNU General Public License as published by the Free Software Foundation;
 *  either version 2 of the License, or (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *  or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 *  more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Generate TSConfig configuration files from a YAML configuration
 *
 * @package MaxServ\Permissions
 * @subpackage Controller
 */
class PermissionsCommandController extends AbstractCommandController
{
    /**
     * Relative path to the Permission Configuration directory
     *
     * @var string
     */
    const PERMISSIONS_CONFIGURATION_DIRECTORY = 'Configuration/Permissions/';

    /**
     * Condition Class prefix
     *
     * @var string
     */
    const CONDITION_PREFIX = 'MaxServ\Permissions\User\Condition::';

    /**
     * Generate TSConfig configuration files from a YAML configuration
     * \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig
     *
     * @return void
     */
    public function generateCommand()
    {
        $this->headerMessage('Generating permssions');
        $lines = array();
        foreach ($this->findPermissionFiles() as $configurationFile) {
            $configuration = $this->parseConfigurationFile($configurationFile);

            $this->infoMessage('Parsing: ' . str_replace(PATH_site, '', $configurationFile));
            if ($configuration !== null && count($configuration) === 1) {
                if (isset($configuration['TYPO3']['BE']['Permissions'])
                    && isset($configuration['TYPO3']['BE']['Permissions']['ruleSets'])
                    && is_array($configuration['TYPO3']['BE']['Permissions']['ruleSets'])
                ) {
                    $ruleSets = $configuration['TYPO3']['BE']['Permissions']['ruleSets'];
                    foreach ($ruleSets as $key => $ruleSet) {
                        $conditionLineParts = array();
                        $operator = ($ruleSet['operator']) ?: '&&';
                        $userFunctions = $ruleSet['userFunctions'];
                        foreach (explode('_', $key) as $index => $value) {
                            $conditionLineParts[] = '[userFunc = ' . self::CONDITION_PREFIX . $userFunctions[$index] . '('. $value .')]';
                        }
                        $lines[] = implode(' ' . $operator . ' ', $conditionLineParts);
                    }
                    var_dump($lines);
                }
            }
        }
    }

    /**
     * Find permission files in all active extensions
     *
     * @return array
     */
    protected function findPermissionFiles()
    {
        /** @var \TYPO3\CMS\Core\Package\PackageManager $packageManager */
        $packageManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Package\\PackageManager');
        $activePackages = $packageManager->getActivePackages();

        $configurationFiles = array();
        foreach ($activePackages as $package) {
//            if ($package->getPackageKey() === 'permissions') {
//                continue;
//            }
            if (!($package instanceof PackageInterface)) {
                continue;
            }
            $packagePath = $package->getPackagePath();
            if (!is_dir($packagePath . self::PERMISSIONS_CONFIGURATION_DIRECTORY)) {
                continue;
            }
            $configurationFiles = array_merge(
                $configurationFiles,
                GeneralUtility::getFilesInDir(
                    $packagePath . self::PERMISSIONS_CONFIGURATION_DIRECTORY,
                    'yaml,yml',
                    true
                )
            );
        }

        return $configurationFiles;
    }

    /**
     * Check if the configuration file exists and if the Yaml parser is
     * available
     *
     * @param $configurationFile
     *
     * @return array|null
     */
    protected function parseConfigurationFile($configurationFile)
    {
        $configuration = null;
        if (!empty($configurationFile)
            && is_file($configurationFile)
            && is_callable(array(
                'Symfony\\Component\\Yaml\\Yaml',
                'parse'
            ))
        ) {
            $configuration = Yaml::parse(file_get_contents($configurationFile));
        }

        return $configuration;
    }
}
