<?php
namespace TYPO3\CMS\Recycler\Controller;

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
use TYPO3\CMS\Recycler\Utility\RecyclerUtility;

/**
 * Deleted Records View
 *
 * @author Erik Frister <erik_frister@otq-solutions.com>
 * @author Julian Kleinhans <typo3@kj187.de>
 */
class DeletedRecordsController {

	/**
	 * @var \TYPO3\CMS\Lang\LanguageService
	 */
	protected $languageService;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->languageService = $GLOBALS['LANG'];
	}

	/**
	 * Transforms the rows for the deleted Records into the Array View necessary for ExtJS Ext.data.ArrayReader
	 *
	 * @param array $deletedRowsArray Array with table as key and array with all deleted rows
	 * @param int $totalDeleted Number of deleted records in total, for PagingToolbar
	 * @return string JSON array
	 */
	public function transform($deletedRowsArray, $totalDeleted) {
		$total = 0;
		$jsonArray = array(
			'rows' => array()
		);
		// iterate
		if (is_array($deletedRowsArray) && count($deletedRowsArray) > 0) {
			foreach ($deletedRowsArray as $table => $rows) {
				$total += count($deletedRowsArray[$table]);
				foreach ($rows as $row) {
					$backendUser = BackendUtility::getRecord('be_users', $row[$GLOBALS['TCA'][$table]['ctrl']['cruser_id']], 'username', '', FALSE);
					$jsonArray['rows'][] = array(
						'uid' => $row['uid'],
						'pid' => $row['pid'],
						'table' => $table,
						'crdate' => BackendUtility::datetime($row[$GLOBALS['TCA'][$table]['ctrl']['crdate']]),
						'tstamp' => BackendUtility::datetime($row[$GLOBALS['TCA'][$table]['ctrl']['tstamp']]),
						'owner' => htmlspecialchars($backendUser['username']),
						'owner_uid' => $row[$GLOBALS['TCA'][$table]['ctrl']['cruser_id']],
						'tableTitle' => RecyclerUtility::getUtf8String($this->languageService->sL($GLOBALS['TCA'][$table]['ctrl']['title'])),
						'title' => htmlspecialchars(RecyclerUtility::getUtf8String(
								BackendUtility::getRecordTitle($table, $row))),
						'path' => RecyclerUtility::getRecordPath($row['pid'])
					);
				}
			}
		}
		$jsonArray['total'] = $totalDeleted;
		return json_encode($jsonArray);
	}
}