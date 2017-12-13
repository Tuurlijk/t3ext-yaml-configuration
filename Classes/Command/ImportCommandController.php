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
 * @since 1.0.0
 *
 * @package MaxServ\Import
 * @subpackage Controller
 */
class ImportCommandController extends AbstractCommandController
{
    /**
     * Import backend users from yml file
     * Import backend users from yml file into be_users table. Existing records will be updated.
     *
     * @since 1.0.0
     *
     * @param string $matchFields Comma separated list of fields used to match configurations to database records. Default: **username**
     * @param bool $beUserMatchGroupByTitle Match be_group settings in be_users.usergroup by title or uid;
     *             if true be_groups.title is used. Default: **false**
     * @param string $file Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed
     */
    public function backendUsersCommand($matchFields = 'username', $beUserMatchGroupByTitle = false, $file = null)
    {
        $this->importData('be_users', $matchFields, $beUserMatchGroupByTitle, $file);
    }

    /**
     * Import backend groups from yml file
     * Import backend groups from yml file into be_groups table. Existing records will be updated.
     *
     * @since 1.0.0
     *
     * @param string $matchFields Comma separated list of fields used to match configurations to database records. Default: **title**
     * @param string $file Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed
     */
    public function backendGroupsCommand($matchFields = 'title', $file = null)
    {
        $this->importData('be_groups', $matchFields, $file);
    }

    /**
     * Import frontend users from yml file
     * Import frontend users from yml file into fe_users table. Existing records will be updated.
     *
     * @since 1.0.0
     *
     * @param string $matchFields Comma separated list of fields used to match configurations to database records. Default: **username**
     * @param string $file Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed
     */
    public function frontendUsersCommand($matchFields = 'username', $file = null)
    {
        $this->importData('fe_users', $matchFields, $file);
    }

    /**
     * Import frontend groups from yml file
     * Import frontend groups from yml file into fe_users table. Existing records will be updated.
     *
     * @since 1.0.0
     *
     * @param string $matchFields Comma separated list of fields used to match configurations to database records. Default: **title**
     * @param string $file Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed
     */
    public function frontendGroupsCommand($matchFields = 'title', $file = null)
    {
        $this->importData('fe_groups', $matchFields, $file);
    }

    /**
     * Import table data from yml file
     * Import table data from yml file. Existing records will be updated.
     *
     * @since 1.0.0
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
     * @since 1.0.0
     *
     * @param string $table
     * @param string $matchFields Comma separated list of fields used to match configurations to database records.
     * @param bool $beUserMatchGroupByTitle Match be_group settings in be_users.usergroup by title or uid;
     *             if true be_groups.title is used. Default: **false**
     * @param string $file Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed
     */
    protected function importData($table, $matchFields, $beUserMatchGroupByTitle = false, $file = null)
    {
        $table = preg_replace('/[^a-z0-9_]/', '', $table);
        $matchFields = explode(',', preg_replace('/[^a-z0-9_,]/', '', $matchFields));
        $columnNames = $this->getColumnNames($table);
        $this->doMatchFieldsExist($matchFields, $columnNames);
        if ($file === null) {
            $configurationFiles = $this->findYamlFiles();
        } else {
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
                    if (isset($record['usergroup']) && $beUserMatchGroupByTitle) {
                        $record['usergroup'] = $this->convertUsergroupNamesToUid($record);
                    }
                    $record = $this->updateTimeFields($record, $columnNames, array('tstamp'));
                    $this->databaseConnection->exec_UPDATEquery(
                        $table,
                        $matchClause,
                        $record
                    );
                } else {
                    $this->successMessage('Found NO existing ' . $table . ' record by matchfields: ' . $matchClause);
                    $this->message('Adding . . .');
                    $record = $this->updateTimeFields($record, $columnNames, array('crdate', 'tstamp'));
                    $this->databaseConnection->exec_INSERTquery(
                        $table,
                        $record
                    );
                }
            }
        }
    }

    /**
     * Usergroup names to uid conversion
     *
     * @since 1.1.0
     *
     * @param array $record Database record for the user that is going to import
     * @return string
     */
    protected function convertUsergroupNamesToUid($record)
    {
        if(!isset($record['usergroup'])) {
            return '';
        }

        $whereInCondition = '';
        foreach(explode(",",$record['usergroup']) as $usergroupTitle) {
            $whereInCondition .= $whereInCondition ? ",": "";
            $whereInCondition .= '"'.$usergroupTitle.'"';
        }

        $groupsUids = $this->databaseConnection->exec_SELECTgetRows('uid','be_groups','title IN('.$whereInCondition.')');
        $commaSepratedGroupUids = '';
        foreach ($groupsUids as $group) {
            $commaSepratedGroupUids .= $commaSepratedGroupUids ? ",": "";
            $commaSepratedGroupUids .= $group['uid'];
        }

        return $commaSepratedGroupUids;
    }

}
