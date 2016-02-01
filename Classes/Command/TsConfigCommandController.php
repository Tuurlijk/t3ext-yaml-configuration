<?php
namespace MaxServ\YamlConfiguration\Command;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Generate TSConfig configuration files from a YAML configuration
 *
 * @package MaxServ\YamlConfiguration
 * @subpackage Controller
 */
class TsConfigCommandController extends AbstractCommandController
{
    /**
     * Condition Class prefix
     *
     * @var string
     */
    const CONDITION_PREFIX = 'MaxServ\YamlConfiguration\User\Condition::';

    /**
     * Generate TSConfig configuration files from a YAML configuration
     * \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig
     *
     * @return void
     */
    public function generateCommand()
    {
        $this->headerMessage('Generating permssions');
        foreach ($this->findYamlFiles() as $configurationFile) {
            $configuration = $this->parseConfigurationFile($configurationFile);

            $this->infoMessage('Parsing: ' . str_replace(PATH_site, '', $configurationFile));
            $forms = $this->getFormConfiguration($configuration);
            foreach ($forms as $table => $ruleSets) {
                foreach ($ruleSets as $key => $ruleSet) {
                    $lines = array();
                    $hasCondition = false;
                    if (isset($ruleSet['title'])) {
                        $lines[] = '';
                        $lines[] = "// " . $ruleSet['title'];
                    }
                    if (isset($ruleSet['description'])) {
                        $lines[] = "// ";
                        $lines[] = "// " . $ruleSet['description'];
                        $lines[] = '';
                    }
                    if (!in_array($key, $this->getColumnNames($table))
                        && isset($ruleSet['userFunctions'])
                    ) {
                        $conditionLineParts = array();
                        $operator = ($ruleSet['operator']) ?: '&&';
                        foreach ($ruleSet['userFunctions'] as $userFunction) {
                            $conditionLineParts[] = '[userFunc = ' . self::CONDITION_PREFIX . $userFunction . ']';
                        }
                        if (count($conditionLineParts)) {
                            $hasCondition = true;
                            $lines[] = implode(' ' . $operator . ' ', $conditionLineParts);
                        }
                    }
                    $lines[] = "TCEFORM {";
                    $lines[] = "\t" . $table . ' {';
                    if (isset($ruleSet['contentElements'])) {
                        $lines[] = "\t\tCType.keepItems := addToList(" . implode(', ',
                                $ruleSet['contentElements']) . ')';
                    }
                    if (isset($ruleSet['plugins'])) {
                        $lines[] = "\t\tlist_type.keepItems := addToList(" . implode(', ',
                                $ruleSet['plugins']) . ')';
                    }
                    $lines[] = "\t}";
                    $lines[] = "}";
                    $lines[] = "mod.wizards.newContentElement.wizardItems {";
                    if (isset($ruleSet['contentElements'])) {
                        $lines[] = "\tcommon.show := addToList(" . implode(', ',
                                $ruleSet['contentElements']) . ')';
                    }
                    if (isset($ruleSet['plugins'])) {
                        $lines[] = "\tplugins.show := addToList(" . implode(', ',
                                $ruleSet['plugins']) . ')';
                    }
                    $lines[] = "}";
                    if ($hasCondition) {
                        $lines[] = '[global]';
                    }
                    $fileContent = implode(PHP_EOL, $lines);
                    $filePath = PATH_site . 'typo3temp/tx_yamlconfiguration/' . $key . '.ts';
                    GeneralUtility::mkdir_deep(PATH_site . 'typo3temp/tx_yamlconfiguration/');
                    GeneralUtility::writeFile(
                        $filePath,
                        (string)$fileContent
                    );
                    $this->message('Wrote configuration to: ' . str_replace(PATH_site,
                            '', $filePath));
                }
            }
        }
    }

    /**
     * Get TCEFORM configuration from configuration string
     *
     * @param $configuration
     *
     * @return array
     */
    protected function getFormConfiguration($configuration)
    {
        $ruleSets = array();
        if ($configuration !== null && count($configuration) === 1) {
            if (isset($configuration['TYPO3']['TSConfig']['TCEFORM'])
                && is_array($configuration['TYPO3']['TSConfig']['TCEFORM'])
            ) {
                $ruleSets = $configuration['TYPO3']['TSConfig']['TCEFORM'];
            }
        }

        return $ruleSets;
    }
}
