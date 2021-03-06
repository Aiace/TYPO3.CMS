<?php
namespace TYPO3\CMS\Frontend\ContentObject;

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

/**
 * Contains TEXT class object.
 *
 * @author Xavier Perseguers <typo3@perseguers.ch>
 * @author Steffen Kamper <steffen@typo3.org>
 */
class HorizontalRulerContentObject extends AbstractContentObject {

	/**
	 * Rendering the cObject, HRULER
	 *
	 * @param array $conf Array of TypoScript properties
	 * @return string Output
	 */
	public function render($conf = array()) {
		$lineThickness = isset($conf['lineThickness.']) ? $this->cObj->stdWrap($conf['lineThickness'], $conf['lineThickness.']) : $conf['lineThickness'];
		$lineThickness = \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($lineThickness, 1, 50);
		$lineColor = isset($conf['lineColor.']) ? $this->cObj->stdWrap($conf['lineColor'], $conf['lineColor.']) : $conf['lineColor'];
		if (!$lineColor) {
			$lineColor = 'black';
		}
		$spaceBefore = isset($conf['spaceLeft.']) ? (int)$this->cObj->stdWrap($conf['spaceLeft'], $conf['spaceLeft.']) : (int)$conf['spaceLeft'];
		$spaceAfter = isset($conf['spaceRight.']) ? (int)$this->cObj->stdWrap($conf['spaceRight'], $conf['spaceRight.']) : (int)$conf['spaceRight'];
		$tableWidth = isset($conf['tableWidth.']) ? (int)$this->cObj->stdWrap($conf['tableWidth'], $conf['tableWidth.']) : (int)$conf['tableWidth'];
		if (!$tableWidth) {
			$tableWidth = '99%';
		}
		$theValue = '';
		$theValue .= '<table border="0" cellspacing="0" cellpadding="0"
			width="' . htmlspecialchars($tableWidth) . '"
			summary=""><tr>';
		if ($spaceBefore) {
			$theValue .= '<td width="1">
				<img src="' . $GLOBALS['TSFE']->absRefPrefix . 'clear.gif"
				width="' . $spaceBefore . '"
				height="1" alt="" title="" />
			</td>';
		}
		$theValue .= '<td bgcolor="' . $lineColor . '">
			<img src="' . $GLOBALS['TSFE']->absRefPrefix . 'clear.gif"
			width="1"
			height="' . $lineThickness . '"
			alt="" title="" />
		</td>';
		if ($spaceAfter) {
			$theValue .= '<td width="1">
				<img src="' . $GLOBALS['TSFE']->absRefPrefix . 'clear.gif"
				width="' . $spaceAfter . '"
				height="1" alt="" title="" />
			</td>';
		}
		$theValue .= '</tr></table>';
		if (isset($conf['stdWrap.'])) {
			$theValue = $this->cObj->stdWrap($theValue, $conf['stdWrap.']);
		}
		return $theValue;
	}

}
