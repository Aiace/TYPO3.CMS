<?php
namespace TYPO3\CMS\WizardCrpages\Controller;

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
 * Creates the "Create pages" wizard
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 */
class CreatePagesWizardModuleFunctionController extends \TYPO3\CMS\Backend\Module\AbstractFunctionModule {

	/**
	 * Holds reference of lorem ipsum class
	 *
	 * @var tx_loremipsum_wiz
	 */
	protected $loremIpsumObject = NULL;

	/**
	 * Complete tsConfig
	 *
	 * @var array
	 */
	protected $tsConfig = array();

	/**
	 * Part of tsConfig with TCEFORM.pages. settings
	 *
	 * @var array
	 */
	protected $pagesTsConfig = array();

	/**
	 * Main function creating the content for the module.
	 *
	 * @return string HTML content for the module, actually a "section" made through the parent object in $this->pObj
	 */
	public function main() {
		$GLOBALS['LANG']->includeLLFile('EXT:wizard_crpages/locallang.xlf');
		$theCode = '';
		$this->tsConfig = BackendUtility::getPagesTSconfig($this->pObj->id);
		$this->pagesTsConfig = isset($this->tsConfig['TCEFORM.']['pages.']) ? $this->tsConfig['TCEFORM.']['pages.'] : array();
		// Create loremIpsum code:
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('lorem_ipsum')) {
			$this->loremIpsumObject = GeneralUtility::getUserObj('EXT:lorem_ipsum/class.tx_loremipsum_wiz.php:tx_loremipsum_wiz');
		}
		// Create new pages here?
		$m_perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(8);
		$pRec = BackendUtility::getRecord('pages', $this->pObj->id, 'uid', ' AND ' . $m_perms_clause);
		$sys_pages = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
		$menuItems = $sys_pages->getMenu($this->pObj->id, '*', 'sorting', '', FALSE);
		if (is_array($pRec)) {
			$data = GeneralUtility::_GP('data');
			if (is_array($data['pages'])) {
				if (GeneralUtility::_GP('createInListEnd')) {
					$endI = end($menuItems);
					$thePid = -(int)$endI['uid'];
					if (!$thePid) {
						$thePid = $this->pObj->id;
					}
				} else {
					$thePid = $this->pObj->id;
				}
				$firstRecord = TRUE;
				$previousIdentifier = '';
				foreach ($data['pages'] as $identifier => $dat) {
					if (!trim($dat['title'])) {
						unset($data['pages'][$identifier]);
					} else {
						$data['pages'][$identifier]['hidden'] = GeneralUtility::_GP('hidePages') ? 1 : 0;
						$data['pages'][$identifier]['nav_hide'] = GeneralUtility::_GP('hidePagesInMenus') ? 1 : 0;
						if ($firstRecord) {
							$firstRecord = FALSE;
							$data['pages'][$identifier]['pid'] = $thePid;
						} else {
							$data['pages'][$identifier]['pid'] = '-' . $previousIdentifier;
						}
						$previousIdentifier = $identifier;
					}
				}
				if (!empty($data['pages'])) {
					reset($data);
					$tce = GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
					$tce->stripslashes_values = 0;
					// set default TCA values specific for the user
					$TCAdefaultOverride = $GLOBALS['BE_USER']->getTSConfigProp('TCAdefaults');
					if (is_array($TCAdefaultOverride)) {
						$tce->setDefaultsFromUserTS($TCAdefaultOverride);
					}
					$tce->start($data, array());
					$tce->process_datamap();
					BackendUtility::setUpdateSignal('updatePageTree');
					$flashMessage = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class, '', $GLOBALS['LANG']->getLL('wiz_newPages_create'));
				} else {
					$flashMessage = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class, '', $GLOBALS['LANG']->getLL('wiz_newPages_noCreate'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				}
				$theCode .= $flashMessage->render();
				// Display result:
				$menuItems = $sys_pages->getMenu($this->pObj->id, '*', 'sorting', '', 0);
				$lines = array();
				foreach ($menuItems as $rec) {
					BackendUtility::workspaceOL('pages', $rec);
					if (is_array($rec)) {
						$lines[] = '<nobr>' . \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIconForRecord('pages', $rec, array('title' => BackendUtility::titleAttribForPages($rec, '', FALSE))) . htmlspecialchars(GeneralUtility::fixed_lgd_cs($rec['title'], $GLOBALS['BE_USER']->uc['titleLen'])) . '</nobr>';
					}
				}
				$theCode .= '<h4>' . $GLOBALS['LANG']->getLL('wiz_newPages_currentMenu') . '</h4>' . implode('<br />', $lines);
			} else {
				// Display create form
				$lines = array();
				$tableData = array();
				for ($a = 0; $a < 9; $a++) {
					$tableData[] = $this->getFormLine($a);
				}
				$lines[] = '<div class="table-fit"><table id="formFieldContainer" class="t3-table"><tbody id="formFieldContainerBody">' . implode(LF, $tableData) . '</tbody></table></div>';
				$theCode .= '<h4>' . $GLOBALS['LANG']->getLL('wiz_newPages') . ':</h4>' . implode('', $lines) . '<br class="clearLeft" />' . '<input type="button" id="createNewFormFields" value="' . $GLOBALS['LANG']->getLL('wiz_newPages_addMoreLines') . '" />' . '<br /><br />';
				$theCode .= '<div class="checkbox"><label for="createInListEnd"><input type="checkbox" name="createInListEnd" id="createInListEnd" value="1" />' . $GLOBALS['LANG']->getLL('wiz_newPages_listEnd') . '</label></div>';
				$theCode .= '<div class="checkbox"><label for="hidePages"><input type="checkbox" name="hidePages" id="hidePages" value="1" />' . $GLOBALS['LANG']->getLL('wiz_newPages_hidePages') . '</label></div>';
				$theCode .= '<div class="checkbox"><label for="hidePagesInMenus"><input type="checkbox" name="hidePagesInMenus" id="hidePagesInMenus" value="1" />' . $GLOBALS['LANG']->getLL('wiz_newPages_hidePagesInMenus') . '</label></div>';
				$theCode .= '<input type="submit" name="create" value="' . $GLOBALS['LANG']->getLL('wiz_newPages_lCreate') . '" />&nbsp;<input type="reset" value="' . $GLOBALS['LANG']->getLL('wiz_newPages_lReset') . '" /><br />';

				/** @var \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer */
				$pageRenderer = $GLOBALS['TBE_TEMPLATE']->getPageRenderer();
				$pageRenderer->loadJquery();
				$pageRenderer->loadRequireJsModule('TYPO3/CMS/WizardCrpages/WizardCreatePages');
				// Add inline code
				$inlineJavaScriptCode = 'var tpl = "' . addslashes(str_replace(array(LF, TAB), array('', ''), $this->getFormLine('#'))) . '", i, line, div, bg, label;';
				$pageRenderer->addJsInlineCode('wizard_crpages', $inlineJavaScriptCode);

				$pageRenderer->addCssInlineBlock(__CLASS__, '
				#formFieldContainer {float: left; margin: 0 0 10px 0;}
				.clearLeft {clear: left;}
				#formFieldContainer label {width: 70px; display: inline-block;}
				#formFieldContainer span {padding: 0 3px;}
				');
			}
		} else {
			$theCode .= GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class, '', $GLOBALS['LANG']->getLL('wiz_newPages_errorMsg1'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR)->render();
		}
		// CSH
		$theCode .= BackendUtility::cshItem('_MOD_web_func', 'tx_wizardcrpages', NULL, '<br />|');
		$out = $this->pObj->doc->header($GLOBALS['LANG']->getLL('wiz_crMany'));
		$out .= $this->pObj->doc->section('', $theCode, FALSE, TRUE);
		return $out;
	}

	/**
	 * Return one line in the form
	 *
	 * @param mixed $index An integer: the line counter for which to create the line. Use "#" to create an template for javascript (used by ExtJS)
	 * @return string HTML code for one input line for one new page
	 */
	protected function getFormLine($index) {
		$backPath = $GLOBALS['BACK_PATH'];
		if (is_numeric($index)) {
			$label = $index + 1;
		} else {
			// used as template for JavaScript
			$index = '{0}';
			$label = '{1}';
		}
		$content = '<label for="page_new_' . $index . '"> ' . $GLOBALS['LANG']->getLL('wiz_newPages_page') . ' ' . $label;
		$content .= ':&nbsp;</label>';
		// Title
		$content .= '<input type="text" id="page_new_' . $index . '" name="data[pages][NEW' . $index . '][title]"' . $this->pObj->doc->formWidth(35) . ' />&nbsp';
		// Lorem ipsum link, if available
		$content .= is_object($this->loremIpsumObject) ? '<a href="#" onclick="' . htmlspecialchars($this->loremIpsumObject->getHeaderTitleJS(('document.forms[0][\'data[pages][NEW' . $index . '][title]\'].value'), 'title')) . '">' . $this->loremIpsumObject->getIcon('', $this->pObj->doc->backPath) . '</a>' : '';
		// type selector
		$content .= '<span>' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_general.xlf:LGL.type') . '</span>';
		$content .= '<select id="type-select"';
		$content .= 'class="select icon-select" name="data[pages][NEW' . $index . '][doktype]" style="background: url(&quot;' . $backPath . 'sysext/t3skin/icons/gfx/i/pages.gif&quot;) no-repeat scroll 0% 50% rgb(255, 255, 255); padding: 1px 1px 1px 24px;">';
		// dokType
		$types = $GLOBALS['PAGES_TYPES'];
		unset($types['default']);
		$types = array_keys($types);
		$types[] = 1;
		if (!$GLOBALS['BE_USER']->isAdmin() && isset($GLOBALS['BE_USER']->groupData['pagetypes_select'])) {
			$types = GeneralUtility::trimExplode(',', $GLOBALS['BE_USER']->groupData['pagetypes_select'], TRUE);
		}
		$removeItems = isset($this->pagesTsConfig['doktype.']['removeItems']) ? GeneralUtility::trimExplode(',', $this->pagesTsConfig['doktype.']['removeItems'], TRUE) : array();
		$group = '';
		if (in_array(1, $types) && !in_array(1, $removeItems)) {
			$group .= '<option style="background: url(&quot;' . $backPath . 'sysext/t3skin/icons/gfx/i/pages.gif&quot;) no-repeat scroll 0% 50% rgb(255, 255, 255); height: 16px; padding-top: 2px; padding-left: 22px;" selected="selected" value="1">Standard</option>';
		}
		if (in_array(6, $types) && !in_array(6, $removeItems)) {
			$group .= '<option style="background: url(&quot;' . $backPath . 'gfx/i/be_users_section.gif&quot;) no-repeat scroll 0% 50% rgb(255, 255, 255); height: 16px; padding-top: 2px; padding-left: 22px;" value="6">Backend User Section</option>';
		}
		$content .= $group ? '<optgroup class="c-divider" label="Page">' . $group . '</optgroup>' : '';
		$group = '';
		if (in_array(4, $types) && !in_array(4, $removeItems)) {
			$group .= '<option style="background: url(&quot;' . $backPath . 'sysext/t3skin/icons/gfx/i/pages_shortcut.gif&quot;) no-repeat scroll 0% 50% rgb(255, 255, 255); height: 16px; padding-top: 2px; padding-left: 22px;" value="4">Shortcut</option>';
		}
		if (in_array(7, $types) && !in_array(7, $removeItems)) {
			$group .= '<option style="background: url(&quot;' . $backPath . 'gfx/i/pages_mountpoint.gif&quot;) no-repeat scroll 0% 50% rgb(255, 255, 255); height: 16px; padding-top: 2px; padding-left: 22px;" value="7">Mount Point</option>';
		}
		if (in_array(3, $types) && !in_array(3, $removeItems)) {
			$group .= '<option style="background: url(&quot;' . $backPath . 'sysext/t3skin/icons/gfx/i/pages_link.gif&quot;) no-repeat scroll 0% 50% rgb(255, 255, 255); height: 16px; padding-top: 2px; padding-left: 22px;" value="3">Link to external URL</option>';
		}
		$content .= $group ? '<optgroup class="c-divider" label="Link">' . $group . '</optgroup>' : '';
		$group = '';
		if (in_array(254, $types) && !in_array(254, $removeItems)) {
			$group .= '<option style="background: url(&quot;' . $backPath . 'sysext/t3skin/icons/gfx/i/sysf.gif&quot;) no-repeat scroll 0% 50% rgb(255, 255, 255); height: 16px; padding-top: 2px; padding-left: 22px;" value="254">Folder</option>';
		}
		if (in_array(255, $types) && !in_array(255, $removeItems)) {
			$group .= '<option style="background: url(&quot;' . $backPath . 'sysext/t3skin/icons/gfx/i/recycler.gif&quot;) no-repeat scroll 0% 50% rgb(255, 255, 255); height: 16px; padding-top: 2px; padding-left: 22px;" value="255">Recycler</option>';
		}
		if (in_array(199, $types) && !in_array(199, $removeItems)) {
			$group .= '<option style="background: url(&quot;' . $backPath . 'sysext/t3skin/icons/gfx/i/spacer_icon.gif&quot;) no-repeat scroll 0% 50% rgb(255, 255, 255); height: 16px; padding-top: 2px; padding-left: 22px;" value="199">Visual menu separator</option>';
		}
		$content .= $group ? '<optgroup class="c-divider" label="Special">' . $group . '</optgroup>' : '';
		$content .= '</select>';
		return '<tr id="form-line-' . $index . '"><td>' . $content . '</td></tr>';
	}

}
