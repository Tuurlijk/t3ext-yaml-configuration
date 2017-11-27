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
 * @since 1.0.0
 *
 * @package MaxServ\Export
 * @subpackage Controller
 */
class ExportCommandController extends AbstractCommandController
{
    /**
     * Export be_users table to yml file
     *
     * @since 1.0.0
     *
     * @param string $file Path to the yml file. It is advised to store this outside of the web root.
     * @param string $skipColumns A comma separated list of column names to skip. Default: **uc,crdate,lastlogin,tstamp**
     * @param bool $includeDeleted Export deleted records. Default: **false**
     * @param bool $includeHidden Export hidden/disable records. Default: **false**
     * @param integer $indentLevel indent level to make yaml file human readable. Default: **2**
     */
    public function backendUsersCommand(
        $file = null,
        $skipColumns = 'crdate,lastlogin,tstamp,uc',
        $includeDeleted = false,
        $includeHidden = false,
        $indentLevel = 2
    ) {
        $this->exportTable('be_users', $file, $skipColumns, $includeDeleted, $includeHidden, $indentLevel);
    }
    /**
     * Export be_groups table to yml file
     *
     * @since 1.0.0
     *
     * @param string $file Path to the yml file. It is advised to store this outside of the web root.
     * @param string $skipColumns A comma separated list of column names to skip. Default: **uc,crdate,lastlogin,tstamp**
     * @param bool $includeDeleted Export deleted records. Default: **false**
     * @param bool $includeHidden Export hidden/disable records. Default: **false**
     * @param integer $indentLevel indent level to make yaml file human readable. Default: **2**
     */
    public function backendGroupsCommand(
        $file = null,
        $skipColumns = 'crdate,lastlogin,tstamp,uc',
        $includeDeleted = false,
        $includeHidden = false,
        $indentLevel = 2
    ) {
        $this->exportTable('be_groups', $file, $skipColumns, $includeDeleted, $includeHidden, $indentLevel);
    }

    /**
     * Export fe_users table to yml file
     *
     * @since 1.0.0
     *
     * @param string $file Path to the yml file. It is advised to store this outside of the web root.
     * @param string $skipColumns A comma separated list of column names to skip. Default: **uc,crdate,lastlogin,tstamp**
     * @param bool $includeDeleted Export deleted records. Default: **false**
     * @param bool $includeHidden Export hidden/disable records. Default: **false**
     * @param integer $indentLevel indent level to make yaml file human readable. Default: **2**
     */
    public function frontendUsersCommand(
        $file = null,
        $skipColumns = 'crdate,lastlogin,tstamp,uc',
        $includeDeleted = false,
        $includeHidden = false,
        $indentLevel = 2
    ) {
        $this->exportTable('fe_users', $file, $skipColumns, $includeDeleted, $includeHidden, $indentLevel);
    }

    /**
     * Export fe_groups table to yml file
     *
     * @since 1.0.0
     *
     * @param string $file Path to the yml file. It is advised to store this outside of the web root.
     * @param string $skipColumns A comma separated list of column names to skip. Default: **uc,crdate,lastlogin,tstamp**
     * @param bool $includeDeleted Export deleted records. Default: **false**
     * @param bool $includeHidden Export hidden/disable records. Default: **false**
     * @param integer $indentLevel indent level to make yaml file human readable. Default: **2**
     */
    public function frontendGroupsCommand(
        $file = null,
        $skipColumns = 'crdate,lastlogin,tstamp,uc',
        $includeDeleted = false,
        $includeHidden = false,
        $indentLevel = 2
    ) {
        $this->exportTable('fe_groups', $file, $skipColumns, $includeDeleted, $includeHidden, $indentLevel);
    }

    /**
     * Export a table to yml file
     *
     * @since 1.0.0
     *
     * @param string $table The name of the table to export
     * @param string $file Path to the yml file. It is advised to store this outside of the web root.
     * @param string $skipColumns A comma separated list of column names to skip. Default: **uc,crdate,lastlogin,tstamp**
     * @param bool $includeDeleted Dump deleted records. Default: **false**
     * @param bool $includeHidden Dump hidden/disable records. Default: **false**
     * @param integer $indentLevel indent level to make yaml file human readable. Default: **2**
     */
    public function tableCommand(
        $table,
        $file = null,
        $skipColumns = 'crdate,lastlogin,tstamp,uc',
        $includeDeleted = false,
        $includeHidden = false,
        $indentLevel = 2
    ) {
        $this->exportTable($table, $file, $skipColumns, $includeDeleted, $includeHidden, $indentLevel);
    }

    /**
     * Export table table to yml file
     *
     * @since 1.0.0
     *
     * @param string $table
     * @param string $file Path to the yml file. It is advised to store this outside of the web root.
     * @param string $skipColumns
     * @param bool $includeDeleted Export deleted records. Default: **false**
     * @param bool $includeHidden Export hidden/disable records. Default: **false**
     * @param integer $indentLevel indent level to make yaml file human readable. Default: **2**
     *
     * @return void
     */
    public function exportTable(
        $table,
        $file = null,
        $skipColumns = 'crdate,lastlogin,tstamp,uc',
        $includeDeleted = false,
        $includeHidden = false,
        $indentLevel = 2
    ) {
        $table = preg_replace('/[^a-z0-9_]/', '', $table);
        $skipColumns = explode(',', $skipColumns);
        $this->headerMessage('Exporting ' . $table . ' configuration');
        if (!$file) {
            $this->message('No ' . $this->successString('--file') .
                ' parameter specified. Data will be written to typo3temp/. ' .
                $this->warningString('This is pretty unsecure.'));
        } else {
            $filePath = GeneralUtility::getFileAbsFileName($file);
            if (strpos($filePath, PATH_site) !== false) {
                $this->errorMessage('Please specify an absolute path outside of the web root.');
                exit;
            }
        }
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

                    // Do not update usergroups by UID when exporting to other systems
                    // UID maybe different for the same usergroup name
                    if ($table == 'be_users' && $column == 'usergroup' && $value) {
                        $usergroups = $this->databaseConnection->exec_SELECTgetRows('title', 'be_groups', 'uid IN (' . $value . ')');
                        // @todo Currently the sorting of usergroups in the original records is ignored when exporting usergroups
                        $usergroupsTitles = [];
                        foreach ($usergroups as $singleUserGroup) {
                            $usergroupsTitles[] = $singleUserGroup['title'];
                        }
                        $explodedValue = $usergroupsTitles;
                        $value = $usergroupsTitles[0]; // Overwrite $value for case count() == 1, see below
                    } else {
                        $explodedValue = explode(',', $value);
                    }

                    if (count($explodedValue) > 1) {
                        $explodedRow[$column] = $explodedValue;
                    } elseif (strlen($value)) {
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
            $yaml = Yaml::dump($dump, $indentLevel);
        }

        if ($yaml !== '') {
            $secret = sha1($yaml);
            GeneralUtility::mkdir_deep(PATH_site . 'typo3temp/tx_yamlconfiguration/');
            $filePath = PATH_site . 'typo3temp/tx_yamlconfiguration/export_' . $table . '.' . $secret . '.yml';
            GeneralUtility::writeFile(
                ($file) ?: $filePath,
                (string)$yaml
            );
            $this->message('Wrote to: ' . $this->warningString(str_replace(PATH_site, '', ($file) ?: $filePath)));
            $this->message('You can tidy the yaml using a tool like: ' . $this->successString('http://www.yamllint.com/'));
        } else {
            $this->warningMessage('No records found in ' . $table . ' table.');
        }
    }
}
