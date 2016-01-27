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

/**
 * Imports data into tables from YAML configuration
 *
 * @package MaxServ\Import
 * @subpackage Controller
 */
class ImportCommandController extends AbstractCommandController
{
    /**
     * Import backend user configuration from yml files
     * Import backend user configuration from yml files into be_users table. Existing records will be updated.
     *
     * @param string $matchField Field used to match configurations to database records. Default: uid
     */
    public function backendUsersCommand($matchField = 'uid')
    {
        $this->importData('be_users', $matchField);
    }

    /**
     * Import backend group configuration from yml files
     * Import backend group configuration from yml files into be_users table. Existing records will be updated.
     *
     * @param string $matchField Field used to match configurations to database records. Default: uid
     */
    public function backendGroupsCommand($matchField = 'uid')
    {
        $this->importData('be_groups', $matchField);
    }

    /**
     * Import frontend user configuration from yml files
     * Import frontend user configuration from yml files into fe_users table. Existing records will be updated.
     *
     * @param string $matchField Field used to match configurations to database records. Default: uid
     */
    public function frontendUsersCommand($matchField = 'uid')
    {
        $this->importData('fe_users', $matchField);
    }

    /**
     * Import frontend group configuration from yml files
     * Import frontend group configuration from yml files into fe_users table. Existing records will be updated.
     *
     * @param string $matchField Field used to match configurations to database records. Default: uid
     */
    public function frontendGroupsCommand($matchField = 'uid')
    {
        $this->importData('fe_groups', $matchField);
    }

    /**
     * Import data into table from yml files
     * Import data from yml files into a table. Existing records will be updated.
     *
     * @param string $table The name of the table to export
     * @param string $matchFields Field used to match configurations to database records. Default: uid
     */
    public function tableCommand($table, $matchFields = 'uid')
    {
        $this->importData($table, $matchFields);
    }

    /**
     * Import Data
     *
     * @param $table
     * @param $matchField
     */
    protected function importData($table, $matchField)
    {
        $table = preg_replace('/[^a-z0-9_]/', '', $table);
        $matchField = preg_replace('/[^a-z0-9_]/', '', $matchField);
        $this->headerMessage('Importing ' . $table . ' configuration');
        foreach ($this->findYamlFiles() as $configurationFile) {
            $configuration = $this->parseConfigurationFile($configurationFile);
            $this->infoMessage('Parsing: ' . str_replace(PATH_site, '', $configurationFile));
            $records = $this->getAccessConfiguration($configuration, $table);
            foreach ($records as $record) {
                $record = $this->flattenYamlFields($record);
                $row = false;
                if (isset($record[$matchField])) {
                    $record[$matchField] = $this->databaseConnection->quoteStr($record[$matchField], $table);
                    $row = $this->databaseConnection->exec_SELECTgetSingleRow(
                        '*',
                        $table,
                        $matchField . ' = ' . $record[$matchField]
                    );
                }
                if ($row) {
                    $this->successMessage('Found existing ' . $table . ' with ' . $matchField . ' ' . $record[$matchField]);
                    $this->message('Updating . . .');
                    $this->databaseConnection->exec_UPDATEquery(
                        $table,
                        $matchField . ' = ' . $record[$matchField],
                        $record
                    );
                } else {
                    $this->message('Adding . . .');
                    $this->databaseConnection->exec_INSERTquery(
                        $table,
                        $record
                    );
                }
            }
        }
    }
}
