<?php
namespace MaxServ\YamlConfiguration\Hook;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TceMain
 * When copying or moving a content element, check if the type of element
 * is allowed in the target column (colPos). If not, abort and show a
 * flash message.
 *
 * This hook works in conjunction with the
 * \MaxServ\YamlConfiguration\User\Condition::hasColumnPosition userFunction
 * in ext_localconf.php.
 *
 * @since 1.0.0
 */
class TceMain
{

    /**
     * Process Commandmap hook
     *
     * @since 1.0.0
     *
     * @param string $command The TCEmain operation status, fx. 'update'
     * @param string $table The table TCEmain is currently processing
     * @param string $id The records id (if any)
     * @param integer $value The uid of the element to move this element after.
     *             This value is negative when moving tt_content in page view.
     *             See \TYPO3\CMS\Core\DataHandling\DataHandler::resolvePid for more information.
     * @param boolean $commandIsProcessed
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parent : Reference to the parent object
     *
     * @return void
     */
    public function processCmdmap($command, $table, $id, $value, &$commandIsProcessed, $parent)
    {
        if (!in_array($table, array('tt_content'))) {
            return;
        }

        switch ($table) {
            case 'tt_content':
                /** @var \TYPO3\CMS\Dbal\Database\DatabaseConnection $databaseConnection */
                $databaseConnection = $GLOBALS['TYPO3_DB'];
                /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                /** @var $flashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
                $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
                switch ($command) {
                    case 'copy':
                    case 'move' :
                        $res = $databaseConnection->sql_query('
							SELECT
								tt_content.uid, pages.backend_layout, pages.doktype, tt_content.colPos
							FROM
								tt_content
							JOIN
								pages
							ON
								pages.uid = tt_content.pid
							WHERE
								tt_content.uid = ' . abs($value)
                        );

                        $pasteAfterFieldArray = $databaseConnection->sql_fetch_assoc($res);

                        if (is_array($pasteAfterFieldArray)) {
                            $GLOBALS['BE_USER']->setAndSaveSessionData('core.yaml_configuration.newColPos', $pasteAfterFieldArray);

                            $originalFieldArray = $parent->recordInfo($table, $id, '*');

                            // If content is moved or copied to another colPos, check
                            // if that is allowed by TSConfig
                            if (isset($pasteAfterFieldArray['colPos']) && isset($originalFieldArray['colPos']) && ($pasteAfterFieldArray['colPos'] !== $originalFieldArray['colPos'])) {
                                $tsConfig = BackendUtility::getTCEFORM_TSconfig('tt_content', $pasteAfterFieldArray);
                                if ($originalFieldArray['CType'] !== '') {
                                    $keepItems = explode(',', str_replace(' ', '', $tsConfig['CType']['keepItems']));
                                    $removeItems = explode(',', str_replace(' ', '', $tsConfig['CType']['removeItems']));
                                    foreach ($removeItems as $item) {
                                        unset($keepItems[$item]);
                                    }
                                    if (!in_array($originalFieldArray['CType'], $keepItems)) {
                                        if ($GLOBALS['BE_USER']->user['admin'] != 1) {
                                            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
                                            $flashMessage = GeneralUtility::makeInstance(
                                                FlashMessage::class,
                                                'You are not allowed to place elements of type "' . $originalFieldArray['CType'] .
                                                '" in that column. Allowed items: "' . implode(', ', $keepItems) . '".',
                                                'Content of type "' . $originalFieldArray['CType'] . '" not allowed in that column!',
                                                FlashMessage::WARNING,
                                                true
                                            );
                                            $flashMessageQueue->enqueue($flashMessage);

                                            $commandIsProcessed = true;
                                        } else {
                                            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
                                            $flashMessage = GeneralUtility::makeInstance(
                                                FlashMessage::class,
                                                'You are not allowed to place elements of type "' . $originalFieldArray['CType'] .
                                                '" in that column. Allowed items: "' . implode(', ',
                                                    $keepItems) . '". You are an ' .
                                                'admin so this time I will see it through the fingers . . . but boy oh boy . . . naughty naughty!',
                                                'Content of type "' . $originalFieldArray['CType'] . '" not allowed in that column!',
                                                FlashMessage::WARNING,
                                                true
                                            );
                                            $flashMessageQueue->enqueue($flashMessage);
                                        }
                                    }
                                }
                                if ($originalFieldArray['list_type'] !== '') {
                                    $keepItems = explode(',', str_replace(' ', '', $tsConfig['list_type']['keepItems']));
                                    $removeItems = explode(',', str_replace(' ', '', $tsConfig['list_type']['removeItems']));
                                    foreach ($removeItems as $item) {
                                        unset($keepItems[$item]);
                                    }
                                    if (!in_array($originalFieldArray['list_type'], $keepItems)) {
                                        if ($GLOBALS['BE_USER']->user['admin'] != 1) {
                                            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
                                            $flashMessage = GeneralUtility::makeInstance(
                                                FlashMessage::class,
                                                'You are not allowed to place elements of type "' . $originalFieldArray['list_type'] .
                                                '" in that column. Allowed items: "' . implode(', ', $keepItems) . '".',
                                                'Plugins of type "' . $originalFieldArray['list_type'] . '" not allowed in that column!',
                                                FlashMessage::WARNING,
                                                true
                                            );
                                            $flashMessageQueue->enqueue($flashMessage);

                                            $commandIsProcessed = true;
                                        } else {
                                            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
                                            $flashMessage = GeneralUtility::makeInstance(
                                                FlashMessage::class,
                                                'You are not allowed to place elements of type "' . $originalFieldArray['list_type'] .
                                                '" in that column. Allowed items: "' . implode(', ',
                                                    $keepItems) . '". You are an ' .
                                                'admin so this time I will see it through the fingers . . . but boy oh boy . . . naughty naughty!',
                                                'Plugins of type "' . $originalFieldArray['list_type'] . '" not allowed in that column!',
                                                FlashMessage::WARNING,
                                                true
                                            );
                                            $flashMessageQueue->enqueue($flashMessage);
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    default:
                }
                break;
        }
    }
}
