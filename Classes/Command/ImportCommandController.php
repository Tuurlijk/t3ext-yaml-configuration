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
     * @param string $matchFields Comma separated list of fields used to match configurations to database records. Default: username
     * @param string $file Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed
     */
    public function backendUsersCommand($matchFields = 'username', $file = null)
    {
        $this->importData('be_users', $matchFields, $file);
    }

    /**
     * Import backend group configuration from yml files
     * Import backend group configuration from yml files into be_users table. Existing records will be updated.
     *
     * @param string $matchFields Comma separated list of fields used to match configurations to database records. Default: title
     * @param string $file Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed
     */
    public function backendGroupsCommand($matchFields = 'title', $file = null)
    {
        $this->importData('be_groups', $matchFields, $file);
    }

    /**
     * Import frontend user configuration from yml files
     * Import frontend user configuration from yml files into fe_users table. Existing records will be updated.
     *
     * @param string $matchFields Comma separated list of fields used to match configurations to database records. Default: username
     * @param string $file Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed
     */
    public function frontendUsersCommand($matchFields = 'username', $file = null)
    {
        $this->importData('fe_users', $matchFields, $file);
    }

    /**
     * Import frontend group configuration from yml files
     * Import frontend group configuration from yml files into fe_users table. Existing records will be updated.
     *
     * @param string $matchFields Comma separated list of fields used to match configurations to database records. Default: title
     * @param string $file Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed
     */
    public function frontendGroupsCommand($matchFields = 'title', $file = null)
    {
        $this->importData('fe_groups', $matchFields, $file);
    }

    /**
     * Import data into table from yml files
     * Import data from yml files into a table. Existing records will be updated.
     *
     * @param string $table The name of the table to export
     * @param string $matchFields Comma separated list of fields used to match configurations to database records.
     * @param string $file Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed
     */
    public function tableCommand($table, $matchFields, $file = null)
    {
        $this->importData($table, $matchFields, $file);
    }

    /**
     * Import Data
     *
     * @param $table
     * @param string $matchFields Comma separated list of fields used to match configurations to database records.
     * @param string $file Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed
     */
    protected function importData($table, $matchFields, $file = null)
    {
        $table = preg_replace('/[^a-z0-9_]/', '', $table);
        $matchFields = explode(',', preg_replace('/[^a-z0-9_,]/', '', $matchFields));
        $columnNames = $this->getColumnNames($table);
        $this->doMatchFieldsExist($matchFields, $columnNames);
        if ($file === null) {
            $configurationFiles = $this->findYamlFiles();
        } else {
            $file = GeneralUtility::getFileAbsFileName($file);
            $configurationFiles = array($file);
        }
        $this->headerMessage('Importing ' . $table . ' configuration');
        foreach ($configurationFiles as $configurationFile) {
            $configuration = $this->parseConfigurationFile($configurationFile);
            $this->infoMessage('Parsing: ' . str_replace(PATH_site, '', $configurationFile));
            $records = $this->getDataConfiguration($configuration, $table);
            foreach ($records as $record) {
                $record = $this->flattenYamlFields($record);
                $row = false;
                $matchClauseParts = array();
                foreach ($matchFields as $matchField) {
                    if (isset($record[$matchField])) {
                        $matchClauseParts[] =
                            $matchField . ' = "' . $this->databaseConnection->quoteStr($record[$matchField], $table) . '"';
                    }
                }
                $matchClause = (count($matchClauseParts)) ? implode(' AND ', $matchClauseParts) : '';
                if ($matchClause) {
                    $row = $this->databaseConnection->exec_SELECTgetSingleRow(
                        '*',
                        $table,
                        $matchClause
                    );
                }
                if ($row) {
                    $this->successMessage('Found existing ' . $table . ' record by matchfields: ' . $matchClause);
                    $this->message('Updating . . .');
                    $record = $this->updateTimeFields($row, $columnNames, array('tstamp'));
                    $this->databaseConnection->exec_UPDATEquery(
                        $table,
                        $matchClause,
                        $record
                    );
                } else {
                    $this->successMessage('Found NO existing ' . $table . ' record by matchfields: ' . $matchClause);
                    $this->message('Adding . . .');
                    $record = $this->updateTimeFields($row, $columnNames, array('crdate', 'tstamp'));
                    $this->databaseConnection->exec_INSERTquery(
                        $table,
                        $this->removeAutoIncrementFields($record, $table)
                    );
                }
            }
        }
    }
}
