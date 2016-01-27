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
 * @package MaxServ\Export
 * @subpackage Controller
 */
class ExportCommandController extends AbstractCommandController
{
    /**
     * Export be_users table to yml file
     *
     * @param array $skipColumns A comma separated list of column names to skip. Default: uc,crdate,lastlogin,tstamp
     * @param bool $includeDeleted Export deleted records. Default: false
     * @param bool $includeHidden Export hidden/disable records. Default: false
     */
    public function backendUsersCommand(
        $skipColumns = array('crdate', 'lastlogin', 'tstamp', 'uc'),
        $includeDeleted = false,
        $includeHidden = false
    ) {
        $this->exportTable('be_users', $skipColumns, $includeDeleted, $includeHidden);
    }
    /**
     * Export be_groups table to yml file
     *
     * @param array $skipColumns A comma separated list of column names to skip. Default: uc,crdate,lastlogin,tstamp
     * @param bool $includeDeleted Export deleted records. Default: false
     * @param bool $includeHidden Export hidden/disable records. Default: false
     */
    public function backendGroupsCommand(
        $skipColumns = array('crdate', 'lastlogin', 'tstamp', 'uc'),
        $includeDeleted = false,
        $includeHidden = false
    ) {
        $this->exportTable('be_groups', $skipColumns, $includeDeleted, $includeHidden);
    }

    /**
     * Export fe_users table to yml file
     *
     * @param array $skipColumns A comma separated list of column names to skip. Default: uc,crdate,lastlogin,tstamp
     * @param bool $includeDeleted Export deleted records. Default: false
     * @param bool $includeHidden Export hidden/disable records. Default: false
     */
    public function frontendUsersCommand(
        $skipColumns = array('crdate', 'lastlogin', 'tstamp', 'uc'),
        $includeDeleted = false,
        $includeHidden = false
    ) {
        $this->exportTable('fe_users', $skipColumns, $includeDeleted, $includeHidden);
    }

    /**
     * Export fe_groups table to yml file
     *
     * @param array $skipColumns A comma separated list of column names to skip. Default: uc,crdate,lastlogin,tstamp
     * @param bool $includeDeleted Export deleted records. Default: false
     * @param bool $includeHidden Export hidden/disable records. Default: false
     */
    public function frontendGroupsCommand(
        $skipColumns = array('crdate', 'lastlogin', 'tstamp', 'uc'),
        $includeDeleted = false,
        $includeHidden = false
    ) {
        $this->exportTable('fe_groups', $skipColumns, $includeDeleted, $includeHidden);
    }

    /**
     * Export a table to yml file
     *
     * @param string $table The name of the table to export
     * @param array $skipColumns A comma separated list of column names to skip. Default: uc,crdate,lastlogin,tstamp
     * @param bool $includeDeleted Dump deleted records. Default: false
     * @param bool $includeHidden Dump hidden/disable records. Default: false
     */
    public function tableCommand(
        $table,
        $skipColumns = array('crdate', 'lastlogin', 'tstamp', 'uc'),
        $includeDeleted = false,
        $includeHidden = false
    ) {
        $this->exportTable($table, $skipColumns, $includeDeleted, $includeHidden);
    }

    /**
     * Export table table to yml file
     *
     * @param string $table
     * @param array $skipColumns
     * @param bool $includeDeleted Export deleted records. Default: false
     * @param bool $includeHidden Export hidden/disable records. Default: false
     *
     * @return void
     */
    public function exportTable(
        $table,
        $skipColumns = array('crdate', 'lastlogin', 'tstamp', 'uc'),
        $includeDeleted = false,
        $includeHidden = false
    ) {
        $table = preg_replace('/[^a-z0-9_]/', '', $table);
        $this->headerMessage('Exporting ' . $table . ' configuration');
        $yaml = '';
        $columnNames = $this->getColumnNames($table);
        $where = '1 = 1';
        if (!$includeHidden || !$includeDeleted) {
            $where = array();
            if (!$includeHidden) {
                if (in_array('disable', $columnNames)) {
                    $where[] = 'disable = 0';
                }
                if (in_array('hidden', $columnNames)) {
                    $where[] = 'hidden = 0';
                }
            }
            if (!$includeDeleted) {
                $where[] = 'deleted = 0';
            }
            $where = implode(' AND ', $where);
        }
        $result = $this->databaseConnection->exec_SELECTgetRows('*', $table, $where);
        if ($result) {
            $explodedResult = array();
            foreach ($result as $row) {
                $explodedRow = array();
                foreach ($row as $column => $value) {
                    if (in_array($column, $skipColumns)) {
                        continue;
                    }
                    $explodedValue = explode(',', $value);
                    if (count($explodedValue) > 1) {
                        $explodedRow[$column] = $explodedValue;
                    } elseif ($value) {
                        $explodedRow[$column] = $value;
                    }
                }
                $explodedResult[] = $explodedRow;
            }
            $dump = array(
                'TYPO3' => array(
                    'Data' => array(
                        $table => $explodedResult
                    )
                )
            );
            $yaml = Yaml::dump($dump);
        }

        if ($yaml !== '') {
            $secret = sha1($yaml);
            $filePath = PATH_site . 'typo3temp/tx_yamlconfiguration/export_' . $table . '.' . $secret . '.yml';
            GeneralUtility::writeFile(
                $filePath,
                (string)$yaml
            );
            $this->message('Wrote to: ' . $this->warningString(str_replace(PATH_site, '', $filePath)));
            $this->message('You can tidy the yaml using a tool like: ' . $this->successString('http://www.yamllint.com/'));
        } else {
            $this->warningMessage('No records found in ' . $table . ' table.');
        }
    }
}
