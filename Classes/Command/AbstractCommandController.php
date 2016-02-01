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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

// I can haz color / use unicode?
if (DIRECTORY_SEPARATOR !== '\\') {
    define('USE_COLOR', function_exists('posix_isatty') && posix_isatty(STDOUT));
    define('UNICODE', true);
} else {
    define('USE_COLOR', getenv('ANSICON') !== false);
    define('UNICODE', false);
}

// Get terminal width
if (@exec('tput cols')) {
    define('TERMINAL_WIDTH', exec('tput cols'));
} else {
    define('TERMINAL_WIDTH', 79);
}

/**
 * Abstract Command Controller
 *
 * @since 1.0.0
 *
 * @package B13\DamFalmigration
 * @subpackage Controller
 */
class AbstractCommandController extends CommandController
{

    /**
     * Relative path to the Yaml Configuration directory
     *
     * @since 1.0.0
     *
     * @var string
     */
    const CONFIGURATION_DIRECTORY = 'Configuration/';

    /**
     * Database connection
     *
     * @since 1.0.0
     *
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * Cache of auto increment fields
     *
     * @since 1.0.0
     *
     * @var array
     */
    protected $autoIncrementFieldCache = array();

    /**
     * Cache of table column names
     *
     * @since 1.0.0
     *
     * @var array
     */
    protected $tableColumnCache = array();

    /**
     * Cache of primary key fields
     *
     * @since 1.0.0
     *
     * @var array
     */
    protected $primaryKeyFieldCache = array();

    /**
     * ExportCommandController constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
    }

    /**
     * Check if matchFields exist in the table.
     *
     * @since 1.0.0
     *
     * @param array $matchFields
     * @param array $columnNames
     * @return bool
     */
    protected function doMatchFieldsExist($matchFields, $columnNames)
    {
        $nonExisting = array();
        foreach ($matchFields as $matchField) {
            if (!in_array($matchField, $columnNames)) {
                $nonExisting[] = $matchField;
            }
        }
        if (count($nonExisting)) {
            $this->errorMessage('Some matchFields do not exist: ' . implode(',', $matchFields));
            exit;
        }
        return true;
    }

    /**
     * Get AUTO_INCREMENT fields from $table
     *
     * @since 1.0.0
     *
     * @param string $table
     * @return array
     */
    protected function getAutoIncrementFields($table)
    {
        $table = preg_replace('/[^a-z0-9_]/', '', $table);
        if (isset($this->autoIncrementFieldCache[$table])) {
            return $this->autoIncrementFieldCache[$table];
        } else {
            $fields = array();
            if (!$table) {
                return $fields;
            }
            $result = $this->databaseConnection->sql_query(
                "SHOW COLUMNS FROM `" . $table . "`;"
            );
            while (($row = $this->databaseConnection->sql_fetch_assoc($result))) {
                if ($row['Extra'] == 'auto_increment') {
                    $fields[] = $row['Field'];
                }
            };
            $this->databaseConnection->sql_free_result($result);

            $this->autoIncrementFieldCache[$table] = $fields;

            return $fields;
        }
    }
    
    /**
     * Get Primary key fields from $table
     *
     * @since 1.0.0
     *
     * @param string $table
     * @return array
     */
    protected function getPrimaryKeyFields($table)
    {
        $table = preg_replace('/[^a-z0-9_]/', '', $table);
        if (isset($this->primaryKeyFieldCache[$table])) {
            return $this->primaryKeyFieldCache[$table];
        } else {
            $fields = array();
            if (!$table) {
                return $fields;
            }
            $result = $this->databaseConnection->sql_query(
                "SHOW KEYS FROM `" . $table . "` WHERE `Key_name` = 'PRIMARY';"
            );
            while (($row = $this->databaseConnection->sql_fetch_row($result))) {
                $fields[] = $row['Column_name'];
            };
            $this->databaseConnection->sql_free_result($result);

            $this->primaryKeyFieldCache[$table] = $fields;

            return $fields;
        }
    }

    /**
     * Get column names
     *
     * @since 1.0.0
     *
     * @param $table
     *
     * @return array
     */
    protected function getColumnNames($table)
    {
        $table = preg_replace('/[^a-z0-9_]/', '', $table);
        if (isset($this->tableColumnCache[$table])) {
            return $this->tableColumnCache[$table];
        } else {
            $result = $this->databaseConnection->exec_SELECTgetSingleRow(
                '*',
                $table,
                '1 = 1'
            );
            if ($result) {
                $columnNames = array_keys($result);
                $this->tableColumnCache[$table] = $columnNames;
            } else {
                $columnNames = array();
                $result = $this->databaseConnection->sql_query('SELECT DATABASE();');
                $row = $this->databaseConnection->sql_fetch_row($result);
                $databaseName = $row[0];
                $this->databaseConnection->sql_free_result($result);
                $result = $this->databaseConnection->sql_query(
                    "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" .
                    $databaseName .
                    "' AND TABLE_NAME = '" .
                    $table .
                    "';"
                );
                while (($row = $this->databaseConnection->sql_fetch_row($result))) {
                    $columnNames[] = $row[0];
                };
                $this->databaseConnection->sql_free_result($result);
                $this->tableColumnCache[$table] = $columnNames;
            }

            return $columnNames;
        }
    }

    /**
     * Find YAML configuration files in all active extensions
     *
     * @since 1.0.0
     *
     * @return array
     */
    protected function findYamlFiles()
    {
        /** @var \TYPO3\CMS\Core\Package\PackageManager $packageManager */
        $packageManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Package\\PackageManager');
        $activePackages = $packageManager->getActivePackages();

        $configurationFiles = array();
        foreach ($activePackages as $package) {
            if ($package->getPackageKey() === 'yaml-configuration') {
                continue;
            }
            if (!($package instanceof PackageInterface)) {
                continue;
            }
            $packagePath = $package->getPackagePath();
            if (!is_dir($packagePath . self::CONFIGURATION_DIRECTORY)) {
                continue;
            }
            $configurationFiles = array_merge(
                $configurationFiles,
                GeneralUtility::getFilesInDir(
                    $packagePath . self::CONFIGURATION_DIRECTORY,
                    'yaml,yml',
                    true
                )
            );
        }

        return $configurationFiles;
    }

    /**
     * Flatten yaml fields into string values.
     *
     * @since 1.0.0
     *
     * @param $row
     * @param string $glue
     *
     * @return array
     */
    protected function flattenYamlFields($row, $glue = ',')
    {
        $flat = array();
        foreach ($row as $key => $value) {
            if (is_array($value)) {
                $flat[$key] = implode($glue, $value);
            } else {
                $flat[$key] = $value;
            }
        }
        return $flat;
    }

    /**
     * Check if the configuration file exists and if the Yaml parser is
     * available
     *
     * @since 1.0.0
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

    /**
     * Remove auto_increment type fields from array
     *
     * @since 1.0.0
     *
     * @param array $fields
     * @param string $table
     *
     * @return array
     */
    protected function removeAutoIncrementFields($fields, $table)
    {
        $autoIncrementFields = $this->getAutoIncrementFields($table);
        foreach ($fields as $key => $value) {
            if (in_array($key, $autoIncrementFields)) {
                unset($fields[$key]);
            }
        }

        return $fields;
    }

    /**
     * Update timestamp fields
     *
     * @since 1.0.0
     *
     * @param array $row
     * @param array $columnNames
     * @param array $fields
     *
     * @return array
     */
    protected function updateTimeFields($row, $columnNames, $fields = array('crdate', 'tsamp'))
    {
        foreach ($fields as $field) {
            if (in_array($field, $columnNames) && !array_key_exists($field, $row)) {
                $row[$field] = time();
            }
        }

        return $row;
    }

    /**
     * Get Data configuration from configuration string
     *
     * @since 1.0.0
     *
     * @param $configuration
     * @param $table
     *
     * @return array
     */
    protected function getDataConfiguration($configuration, $table)
    {
        $records = array();
        if ($configuration !== null && count($configuration) === 1) {
            if (isset($configuration['TYPO3']['Data'][$table])
                && is_array($configuration['TYPO3']['Data'][$table])
            ) {
                $records = $configuration['TYPO3']['Data'][$table];
            }
        }

        return $records;
    }

    /**
     * Output FlashMessage
     *
     * @since 1.0.0
     *
     * @param FlashMessage $message
     *
     * @return void
     */
    public function outputMessage($message = null)
    {
        if ($message->getTitle()) {
            $this->outputLine($message->getTitle());
        }
        if ($message->getMessage()) {
            $this->outputLine($message->getMessage());
        }
        if ($message->getSeverity() !== FlashMessage::OK) {
            $this->sendAndExit(1);
        }
    }

    /**
     * Normal message
     *
     * @since 1.0.0
     *
     * @param $message
     * @param boolean $flushOutput
     *
     * @return void
     */
    public function message($message = null, $flushOutput = true)
    {
        $this->outputLine($message);
        if ($flushOutput) {
            $this->response->send();
            $this->response->setContent('');
        }
    }

    /**
     * Informational message
     *
     * @since 1.0.0
     *
     * @param string $message
     * @param boolean $showIcon
     * @param boolean $flushOutput
     *
     * @return void
     */
    public function infoMessage($message = null, $showIcon = false, $flushOutput = true)
    {
        $icon = '';
        if ($showIcon && UNICODE) {
            $icon = '★ ';
        }
        if (USE_COLOR) {
            $this->outputLine("\033[0;36m" . $icon . $message . "\033[0m");
        } else {
            $this->outputLine($icon . $message);
        }
        if ($flushOutput) {
            $this->response->send();
            $this->response->setContent('');
        }
    }

    /**
     * Error message
     *
     * @since 1.0.0
     *
     * @param string $message
     * @param boolean $showIcon
     * @param boolean $flushOutput
     *
     * @return void
     */
    public function errorMessage($message = null, $showIcon = false, $flushOutput = true)
    {
        $icon = '';
        if ($showIcon && UNICODE) {
            $icon = '✖ ';
        }
        if (USE_COLOR) {
            $this->outputLine("\033[31m" . $icon . $message . "\033[0m");
        } else {
            $this->outputLine($icon . $message);
        }
        if ($flushOutput) {
            $this->response->send();
            $this->response->setContent('');
        }
    }

    /**
     * Warning message
     *
     * @since 1.0.0
     *
     * @param string $message
     * @param boolean $showIcon
     * @param boolean $flushOutput
     *
     * @return void
     */
    public function warningMessage($message = null, $showIcon = false, $flushOutput = true)
    {
        $icon = '';
        if ($showIcon) {
            $icon = '! ';
        }
        if (USE_COLOR) {
            $this->outputLine("\033[0;33m" . $icon . $message . "\033[0m");
        } else {
            $this->outputLine($icon . $message);
        }
        if ($flushOutput) {
            $this->response->send();
            $this->response->setContent('');
        }
    }

    /**
     * Success message
     *
     * @since 1.0.0
     *
     * @param string $message
     * @param boolean $showIcon
     * @param boolean $flushOutput
     *
     * @return void
     */
    public function successMessage($message = null, $showIcon = false, $flushOutput = true)
    {
        $icon = '';
        if ($showIcon && UNICODE) {
            $icon = '✔ ';
        }
        if (USE_COLOR) {
            $this->outputLine("\033[0;32m" . $icon . $message . "\033[0m");
        } else {
            $this->outputLine($icon . $message);
        }
        if ($flushOutput) {
            $this->response->send();
            $this->response->setContent('');
        }
    }

    /**
     * Info string
     *
     * @since 1.0.0
     *
     * @param string $string
     *
     * @return string
     */
    public function infoString($string = null)
    {
        if (USE_COLOR) {
            $string = "\033[0;36m" . $string . "\033[0m";
        }

        return $string;
    }

    /**
     * Error string
     *
     * @since 1.0.0
     *
     * @param string $string
     *
     * @return string
     */
    public function errorString($string = null)
    {
        if (USE_COLOR) {
            $string = "\033[0;31m" . $string . "\033[0m";
        }

        return $string;
    }

    /**
     * Warning string
     *
     * @since 1.0.0
     *
     * @param string $string
     *
     * @return string
     */
    public function warningString($string = null)
    {
        if (USE_COLOR) {
            $string = "\033[0;33m" . $string . "\033[0m";
        }

        return $string;
    }

    /**
     * Success string
     *
     * @since 1.0.0
     *
     * @param string $string
     *
     * @return string
     */
    public function successString($string = null)
    {
        if (USE_COLOR) {
            $string = "\033[0;32m" . $string . "\033[0m";
        }

        return $string;
    }


    /**
     * Show a header message
     *
     * @since 1.0.0
     *
     * @param $message
     * @param string $style
     * @param boolean $flushOutput
     *
     * @return void
     */
    public function headerMessage($message, $style = 'info', $flushOutput = true)
    {
        // Crop the message
        $message = substr($message, 0, TERMINAL_WIDTH);
        if (UNICODE) {
            $linePaddingLength = mb_strlen('─') * (TERMINAL_WIDTH);
            $message =
                str_pad('', $linePaddingLength, '─') . LF .
                str_pad($message, TERMINAL_WIDTH) . LF .
                str_pad('', $linePaddingLength, '─');
        } else {
            $message =
                str_pad('', TERMINAL_WIDTH, '-') . LF .
                str_pad($message, TERMINAL_WIDTH) . LF .
                str_pad('', TERMINAL_WIDTH, '-');
        }
        switch ($style) {
            case 'error':
                $this->errorMessage($message, false, $flushOutput);
                break;
            case 'info':
                $this->infoMessage($message, false, $flushOutput);
                break;
            case 'success':
                $this->successMessage($message, false, $flushOutput);
                break;
            case 'warning':
                $this->warningMessage($message, false, $flushOutput);
                break;
            default:
                $this->message($message, $flushOutput);
        }
    }

    /**
     * Show a horizontal line
     *
     * @since 1.0.0
     *
     * @param string $style
     * @param boolean $flushOutput
     *
     * @return void
     */
    public function horizontalLine($style = '', $flushOutput = true)
    {
        if (UNICODE) {
            $linePaddingLength = mb_strlen('─') * (TERMINAL_WIDTH);
            $message =
                str_pad('', $linePaddingLength, '─');
        } else {
            $message =
                str_pad('', TERMINAL_WIDTH, '-');
        }
        switch ($style) {
            case 'error':
                $this->errorMessage($message, false, $flushOutput);
                break;
            case 'info':
                $this->infoMessage($message, false, $flushOutput);
                break;
            case 'success':
                $this->successMessage($message, false, $flushOutput);
                break;
            case 'warning':
                $this->warningMessage($message, false, $flushOutput);
                break;
            default:
                $this->message($message, $flushOutput);
        }
    }
}
