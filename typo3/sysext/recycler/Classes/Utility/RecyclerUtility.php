<?php
namespace TYPO3\CMS\Recycler\Utility;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper class for the 'recycler' extension.
 *
 * @author Julian Kleinhans <typo3@kj187.de>
 */
class RecyclerUtility {

	/************************************************************
	 * USER ACCESS
	 *
	 *
	 ************************************************************/
	/**
	 * Checks the page access rights (Code for access check mostly taken from alt_doc.php)
	 * as well as the table access rights of the user.
	 *
	 * @param string $table The table to check access for
	 * @param string $row Record array
	 * @return bool Returns TRUE is the user has access, or FALSE if not
	 */
	static public function checkAccess($table, $row) {
		/* @var $backendUser \TYPO3\CMS\Core\Authentication\BackendUserAuthentication */
		$backendUser = $GLOBALS['BE_USER'];

		// Checking if the user has permissions? (Only working as a precaution, because the final permission check is always down in TCE. But it's good to notify the user on beforehand...)
		// First, resetting flags.
		$hasAccess = FALSE;
		$calcPRec = $row;
		BackendUtility::fixVersioningPid($table, $calcPRec);
		if (is_array($calcPRec)) {
			if ($table == 'pages') {
				// If pages:
				$calculatedPermissions = $backendUser->calcPerms($calcPRec);
				$hasAccess = $calculatedPermissions & 2 ? TRUE : FALSE;
			} else {
				$calculatedPermissions = $backendUser->calcPerms(BackendUtility::getRecord('pages', $calcPRec['pid']));
				// Fetching pid-record first.
				$hasAccess = $calculatedPermissions & 16 ? TRUE : FALSE;
			}
			// Check internals regarding access:
			if ($hasAccess) {
				$hasAccess = $backendUser->recordEditAccessInternals($table, $calcPRec);
			}
		}
		if (!$backendUser->check('tables_modify', $table)) {
			$hasAccess = FALSE;
		}
		return $hasAccess;
	}

	/**
	 * Returns the path (visually) of a page $uid, fx. "/First page/Second page/Another subpage"
	 * Each part of the path will be limited to $titleLimit characters
	 * Deleted pages are filtered out.
	 *
	 * @param int $uid Page uid for which to create record path
	 * @param string $clause is additional where clauses, eg.
	 * @param int $titleLimit Title limit
	 * @param int $fullTitleLimit Title limit of Full title (typ. set to 1000 or so)
	 * @return mixed Path of record (string) OR array with short/long title if $fullTitleLimit is set.
	 */
	static public function getRecordPath($uid, $clause = '', $titleLimit = 1000, $fullTitleLimit = 0) {
		/* @var $databaseConnection \TYPO3\CMS\Core\Database\DatabaseConnection */
		$databaseConnection = $GLOBALS['TYPO3_DB'];

		$loopCheck = 100;
		$output = ($fullOutput = '/');
		while ($uid != 0 && $loopCheck > 0) {
			$loopCheck--;
			$res = $databaseConnection->exec_SELECTquery('uid,pid,title,deleted,t3ver_oid,t3ver_wsid', 'pages', 'uid=' . (int)$uid . (strlen(trim($clause)) ? ' AND ' . $clause : ''));
			if (is_resource($res)) {
				$row = $databaseConnection->sql_fetch_assoc($res);
				$databaseConnection->sql_free_result($res);
				BackendUtility::workspaceOL('pages', $row);
				if (is_array($row)) {
					BackendUtility::fixVersioningPid('pages', $row);
					$uid = $row['pid'];
					$output = '/' . htmlspecialchars(GeneralUtility::fixed_lgd_cs($row['title'], $titleLimit)) . $output;
					if ($row['deleted']) {
						$output = '<span class="deletedPath">' . $output . '</span>';
					}
					if ($fullTitleLimit) {
						$fullOutput = '/' . htmlspecialchars(GeneralUtility::fixed_lgd_cs($row['title'], $fullTitleLimit)) . $fullOutput;
					}
				} else {
					break;
				}
			} else {
				break;
			}
		}
		if ($fullTitleLimit) {
			return array($output, $fullOutput);
		} else {
			return $output;
		}
	}

	/**
	 * Gets the name of the field with the information whether a record is deleted.
	 *
	 * @param string $tableName Name of the table to get the deleted field for
	 * @return string Name of the field with the information whether a record is deleted
	 */
	static public function getDeletedField($tableName) {
		$TCA = self::getTableTCA($tableName);
		if ($TCA && isset($TCA['ctrl']['delete']) && $TCA['ctrl']['delete']) {
			return $TCA['ctrl']['delete'];
		}
		return '';
	}

	/**
	 * Gets the TCA of the table used in the current context.
	 *
	 * @param string $tableName Name of the table to get TCA for
	 * @return array|FALSE TCA of the table used in the current context
	 */
	static public function getTableTCA($tableName) {
		$TCA = FALSE;
		if (isset($GLOBALS['TCA'][$tableName])) {
			$TCA = $GLOBALS['TCA'][$tableName];
		}
		return $TCA;
	}

	/**
	 * Gets the current backend charset.
	 *
	 * @return string The current backend charset
	 */
	static public function getCurrentCharset() {
		return $GLOBALS['LANG']->csConvObj->parse_charset($GLOBALS['LANG']->charSet);
	}

	/**
	 * Determines whether the current charset is not UTF-8
	 *
	 * @return bool Whether the current charset is not UTF-8
	 */
	static public function isNotUtf8Charset() {
		return self::getCurrentCharset() !== 'utf-8';
	}

	/**
	 * Gets an UTF-8 encoded string (only if the current charset is not UTF-8!).
	 *
	 * @param string $string String to be converted to UTF-8 if required
	 * @return string UTF-8 encoded string
	 */
	static public function getUtf8String($string) {
		if (self::isNotUtf8Charset()) {
			$string = $GLOBALS['LANG']->csConvObj->utf8_encode($string, self::getCurrentCharset());
		}
		return $string;
	}

}
