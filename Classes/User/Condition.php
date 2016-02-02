<?php
namespace MaxServ\YamlConfiguration\User;

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

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Condition
 *
 * @since 1.0.0
 */
class Condition
{
    /**
     * Backend User
     *
     * @since 1.0.0
     *
     * @var BackendUserAuthentication
     */
    protected static $backendUser = null;

    /**
     * Database connection
     *
     * @since 1.0.0
     *
     * @var DatabaseConnection
     */
    protected $databaseConnection = null;

    /**
     * setter for backendUser object
     *
     * @since 1.0.0
     *
     * @param BackendUserAuthentication $backendUser
     *
     * @return BackendUserAuthentication
     */
    public static function setBackendUser(BackendUserAuthentication $backendUser)
    {
        self::$backendUser = $backendUser;

        return self::$backendUser;
    }

    /**
     * getter for backendUser object
     *
     * @since 1.0.0
     *
     * @return BackendUserAuthentication $backendUser
     */
    public static function getBackendUser()
    {
        return (self::$backendUser) ?: self::setBackendUser($GLOBALS['BE_USER']);
    }

    /**
     * setter for databaseConnection object
     *
     * @since 1.0.0
     *
     * @param DatabaseConnection $databaseConnection
     *
     * @return DatabaseConnection
     */
    public function setDatabaseConnection(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;

        return $this->databaseConnection;
    }

    /**
     * getter for databaseConnection object
     *
     * @since 1.0.0
     *
     * @return DatabaseConnection $databaseConnection
     */
    public function getDatabaseConnection()
    {
        return ($this->databaseConnection) ?: $this->setDatabaseConnection($GLOBALS['TYPO3_DB']);
    }

    /**
     * User function used to check if a content element has a certain
     * backend_layout This is used in the YamlConfiguration.ts TSConfig file to make
     * sure only certain content elements are placed in certain columns.
     *
     * This method detects several 'entry points' to new element creation:
     *
     * 1). Clicking on a pencil icon
     * In this case the GET parameter edit tt_content will be set. It contains
     * the uid of the content element being edited. We can deduce the colPos
     * and backend_layout from there. ( edit[tt_content][6789,]:edit )
     *
     * 2). Clicking the content-with-green-plus-sign icon on a column header
     * In this case the GET parameterers colPos and id are set. This
     * directly provides us with the colPos. We can deduce the backend_layout
     * from the id which holds the page uid.
     *
     * 3). Right after creating a new element and displaying it.
     * When a new element is created, we need to apply the permissions too.
     * In this case the GET parameters defVals[tt_content][colPos] and
     * returnUrl may be used. Some of these parameters are also available in
     * case 1). Like the return Url.
     *
     * 4). Elements are pasted from a clipboard.
     * Any clipboard data? Then we're copying or moving elements. In that
     * case use the newColPos data stored in the processCmdmap hook.
     *
     * @since 1.0.0
     *
     * @param integer $backendLayout id of the colPos
     *
     * @return boolean
     */
    public function hasBackendLayout($backendLayout = 0)
    {
        $backendLayout = (int)$backendLayout;
        static $backendLayoutCache = array();
        if (isset($backendLayoutCache[$backendLayout])) {
            return $backendLayoutCache[$backendLayout];
        }

        $get = GeneralUtility::_GET();
        $result = false;

        // Case 1). edit one or more existing elements
        // Case 2). Adding a new element using the new-element icon
        // Case 3). Right after new element creation
        if (isset($get['id']) || isset($get['returnUrl'])) {
            if (isset($get['id'])) {
                $pid = (int)$get['id'];
            } else {
                $pid = (int)substr(strrchr($get['returnUrl'], '='), 1);
            }
            $queryResult = $this->getDatabaseConnection()->sql_query('SELECT backend_layout FROM pages WHERE uid =' . $pid);
            $row = $this->getDatabaseConnection()->sql_fetch_assoc($queryResult);
            $this->getDatabaseConnection()->sql_free_result($queryResult);
            $result = $backendLayout === (int)$row['backend_layout'];

            // Case 4). Elements pasted from a clipboard
        } elseif (is_array($get['CB'])) {
            $newColPosData = self::getBackendUser()->getSessionData('core.yaml_configuration.newColPos');
            if (is_array($newColPosData) && ($backendLayout == $newColPosData['backend_layout'])) {
                $result = true;
            };
        }
        $backendLayoutCache[$backendLayout] = $result;

        return $result;
    }

    /**
     * User function used to check if a content element is in a certain column
     * This is used in the YamlConfiguration.ts TSConfig file to make sure only
     * certain content elements are placed in certain columns.
     *
     * @see hasBackendLayout for documentation
     *
     * @since 1.0.0
     *
     * @param int $colPos id of the colPos
     *
     * @return boolean
     */
    public function hasColumnPosition($colPos = 0)
    {
        $colPos = (int)$colPos;
        static $colPosCache = array();
        if (isset($colPosCache[$colPos])) {
            return $colPosCache[$colPos];
        }
        /*
            returnUrl:/typo3/sysext/cms/layout/db_layout.php?id=7309
            edit[tt_content][168267,]:edit
        */
        $get = GeneralUtility::_GET();
        $result = false;

        // Case 2). Adding a new element using the new-element icon
        if (isset($get['colPos'])) {
            $result = $colPos === (int)$get['colPos'];

            // Case 3). Right after new element creation
        } elseif (isset($get['defVals']['tt_content']['colPos'])) {
            $result = $colPos === (int)$get['defVals']['tt_content']['colPos'];

            // Case 1). edit one or more existing elements
        } elseif (isset($get['edit']['tt_content'])) {
            $getUid = $get['edit']['tt_content'];
            if (is_array($getUid)) {
                $uid = key($getUid);
                // strip off trailing comma
                $uid = rtrim($uid, ',');
                $queryResult = $this->getDatabaseConnection()->sql_query('SELECT colPos FROM tt_content WHERE uid =' . (int)abs($uid));
                $row = $this->getDatabaseConnection()->sql_fetch_assoc($queryResult);
                $this->getDatabaseConnection()->sql_free_result($queryResult);
                $result = $colPos === (int)$row['colPos'];
            }

            // Case 4). Elements pasted from a clipboard
        } elseif (is_array($get['CB'])) {
            $newColPosData = self::getBackendUser()->getSessionData('core.yaml_configuration.newColPos');
            if (is_array($newColPosData) && ($colPos == $newColPosData['colPos'])) {
                $result = true;
            };
        }
        $colPosCache[$colPos] = $result;

        return $result;
    }

    /**
     * User function used to check if a content element is a certain content element
     * This is used in the YamlConfiguration.ts TSConfig file change configuration for
     * certain content elements
     *
     * @since 1.0.0
     *
     * @param string $cType Identifier of the CType
     *
     * @return boolean
     */
    public function isContentType($cType)
    {
        $get = GeneralUtility::_GET();
        $result = false;

        // Case 1). Right after new element creation
        if (isset($get['defVals']['tt_content']['CType'])
            && $get['defVals']['tt_content']['CType'] === $cType
        ) {
            $result = true;

            // Case 2). edit one or more existing elements
        } elseif (isset($get['edit']['tt_content'])) {
            $getUid = $get['edit']['tt_content'];

            if (is_array($getUid)) {
                $uid = key($getUid);
                // strip off trailing comma
                $uid = rtrim($uid, ',');
                $queryResult = $this->getDatabaseConnection()->sql_query('SELECT CType FROM tt_content WHERE uid =' . (int)abs($uid));
                $row = $this->getDatabaseConnection()->sql_fetch_assoc($queryResult);
                $this->getDatabaseConnection()->sql_free_result($queryResult);
                $result = $cType === $row['CType'];
            }
        }

        return $result;
    }

    /**
     * User function used to check if a content element is a certain plugin
     * This is used in the YamlConfiguration.ts TSConfig file change configuration for
     * certain content elements
     *
     * @since 1.0.0
     *
     * @param string $listType Identifier of the list_type
     *
     * @return boolean
     */
    public function isListType($listType)
    {
        $get = GeneralUtility::_GET();
        $result = false;

        // Case 1). Right after new element creation
        if (isset($get['defVals']['tt_content']['list_type'])
            && $get['defVals']['tt_content']['list_type'] === $listType
        ) {
            $result = true;

            // Case 2). edit one or more existing elements
        } elseif (isset($get['edit']['tt_content'])) {
            $getUid = $get['edit']['tt_content'];

            if (is_array($getUid)) {
                $uid = key($getUid);
                // strip off trailing comma
                $uid = rtrim($uid, ',');
                $queryResult = $this->getDatabaseConnection()->sql_query('SELECT list_type FROM tt_content WHERE uid =' . (int)abs($uid));
                $row = $this->getDatabaseConnection()->sql_fetch_assoc($queryResult);
                $this->getDatabaseConnection()->sql_free_result($queryResult);
                $result = $listType === $row['list_type'];
            }
        }

        return $result;
    }

    /**
     * Check if current Backend user is in the given UserGroup
     *
     * @since 1.0.0
     *
     * @param integer|string $role Identifier of the userGroup
     *
     * @return boolean
     */
    public function isInUserGroup($role)
    {
        if (!is_array(self::getBackendUser()->userGroups)) {
            return false;
        }
        if (is_numeric($role)) {
            foreach (self::getBackendUser()->userGroups as $userGroup) {
                if ((int)$userGroup['uid'] === (int)$role) {
                    return true;
                }
            }
        } else {
            foreach (self::getBackendUser()->userGroups as $userGroup) {
                if ($userGroup['title'] === $role) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * User function used to check if a content element has a certain
     * doktype This is used in the YamlConfiguration.ts TSConfig file to make
     * sure only certain content elements are placed in certain columns.
     *
     * @see hasBackendLayout for documentation
     *
     * @since 1.0.0
     *
     * @param integer $doktype id of the doktype
     *
     * @return boolean
     */
    public function hasDoktype($doktype = 0)
    {
        $doktype = (int)$doktype;
        static $doktypeCache = array();
        if (isset($doktypeCache[$doktype])) {
            return $doktypeCache[$doktype];
        }

        $get = GeneralUtility::_GET();
        $result = false;

        // Case 1). edit one or more existing elements
        // Case 2). Adding a new element using the new-element icon
        // Case 3). Right after new element creation
        if (isset($get['id']) || isset($get['returnUrl'])) {
            if (isset($get['id'])) {
                $pid = (int)$get['id'];
            } else {
                $pid = (int)substr(strrchr($get['returnUrl'], '='), 1);
            }
            $queryResult = $this->getDatabaseConnection()->sql_query('SELECT doktype FROM pages WHERE uid =' . $pid);
            $row = $this->getDatabaseConnection()->sql_fetch_assoc($queryResult);
            $this->getDatabaseConnection()->sql_free_result($queryResult);
            $result = $doktype === (int)$row['doktype'];

            // Case 4). Elements pasted from a clipboard
        } elseif (is_array($get['CB'])) {
            $newColPosData = $this->getBackendUser()->getSessionData('core.yaml_configuration.newColPos');
            if (is_array($newColPosData) && ($doktype == $newColPosData['doktype'])) {
                $result = true;
            }
        }
        $doktypeCache[$doktype] = $result;

        return $result;
    }
}
