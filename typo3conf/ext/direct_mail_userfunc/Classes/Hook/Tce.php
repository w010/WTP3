<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013-2015 Xavier Perseguers <xavier@causal.ch>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This class hooks into t3lib_TCEforms and t3lib_TCEmain to process the virtual fields.
 *
 * @category    Hook
 * @package     direct_mail_userfunc
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   2013-2015 Causal Sàrl
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Tx_DirectMailUserfunc_Hook_Tce {

	/**
	 * Pre-processes the TCA of table sys_dmail_group.
	 * Hook in t3lib_TCEforms
	 *
	 * @param string $table
	 * @param array $row
	 * @param t3lib_TCEforms|\TYPO3\CMS\Backend\Form\FormEngine $pObj
	 * @return void
	 */
	public function getMainFields_preProcess($table, array &$row, /* t3lib_TCEforms */ $pObj) {
		if ($table !== 'sys_dmail_group') {
			return;
		}

		$wizardFields = NULL;
		$itemsProcFunc = $row['tx_directmailuserfunc_itemsprocfunc'];
		if (Tx_DirectMailUserfunc_Utility_ItemsProcFunc::hasWizardFields($itemsProcFunc)) {
			$wizardFields = Tx_DirectMailUserfunc_Utility_ItemsProcFunc::callWizardFields($itemsProcFunc, $pObj);
			$this->reconfigureTCA($wizardFields, $row);
		}
	}

	/**
	 * Stores virtual field values into column tx_directmailuserfunc_params.
	 * Hook in t3lib_TCEmain
	 *
	 * @param array $incomingFieldArray
	 * @param string $table
	 * @param integer|string $id
	 * @param t3lib_TCEmain|\TYPO3\CMS\Core\DataHandling\DataHandler $pObj
	 * @return void
	 */
	public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, $table, $id, /* t3lib_TCEmain */ $pObj) {
		if ($table !== 'sys_dmail_group') {
			return;
		}

		if (strpos($id, 'NEW') !== FALSE) {
			$row = array();
		} else {
			$row = t3lib_BEfunc::getRecord($table, $id);
		}
		$currentValues = Tx_DirectMailUserfunc_Utility_ItemsProcFunc::decodeUserParameters($row);

		if (isset($incomingFieldArray['tx_directmailuserfunc_itemsprocfunc'])) {
			$itemsProcFunc = $incomingFieldArray['tx_directmailuserfunc_itemsprocfunc'];
		} else {
			$itemsProcFunc = $row['tx_directmailuserfunc_itemsprocfunc'];
		}

		if (Tx_DirectMailUserfunc_Utility_ItemsProcFunc::hasWizardFields($itemsProcFunc)) {
			$wizardFields = Tx_DirectMailUserfunc_Utility_ItemsProcFunc::callWizardFields($itemsProcFunc, $pObj);
			$this->reconfigureTCA($wizardFields, $row, FALSE);
		}

		$virtualValues = array();
		foreach ($incomingFieldArray as $field => $value) {
			if (t3lib_div::isFirstPartOfStr($field, 'tx_directmailuserfunc_virtual_')) {
				$virtualField = substr($field, strlen('tx_directmailuserfunc_virtual_'));

				// Evaluate field
				$curValue = isset($currentValues[$virtualField]) ? $currentValues[$virtualField] : '';
				$res = $this->checkValue($table, $field, $value, $curValue, $id, $status, $row['pid'], $pObj);
				if (isset($res['value'])) {
					$virtualValues[$virtualField] = $res['value'];
				}

				unset($incomingFieldArray[$field]);
			}
		}

		if (count($virtualValues) > 0) {
			$newValues = array_merge($currentValues, $virtualValues);
			Tx_DirectMailUserfunc_Utility_ItemsProcFunc::encodeUserParameters($incomingFieldArray, $newValues);
		}
	}

	/**
	 * @param string $table Table name
	 * @param string $field Field name
	 * @param string $value Value to be evaluated. Notice, this is the INPUT value from the form
	 * @param string $curValue The original value (from existing record)
	 * @param string $id The record-uid, mainly - but not exclusively - used for logging
	 * @param string $status 'update' or 'new' flag
	 * @param integer $realPid The real PID value of the record. For updates, this is just the pid of the record.
	 * @param t3lib_TCEmain|\TYPO3\CMS\Core\DataHandling\DataHandler $pObj
	 * @return array Returns the evaluated $value as key "value" in this array. Can be checked with isset($res['value']) ...
	 * @see t3lib_TCEmain::checkValue()
	 */
	protected function checkValue($table, $field, $value, $curValue, $id, $status, $realPid, /* t3lib_TCEmain */ $pObj) {
		// For our use $tscPID is always the real PID
		$tscPID = $realPid;
		// Getting config for the field
		$tcaFieldConf = $GLOBALS['TCA'][$table]['columns'][$field]['config'];
		// Perform processing:
		$res = $pObj->checkValue_SW($res, $value, $tcaFieldConf, $table, $id, $curValue, $status, $realPid, $recFID, $field, array(), $tscPID);
		return $res;
	}

	/**
	 * Reconfigures the TCA with custom fields.
	 *
	 * @param array|NULL $fields
	 * @param array $row
	 * @param boolean $removeStandardField
	 * @return void
	 */
	protected function reconfigureTCA(array $fields = NULL, array &$row, $removeStandardField = TRUE) {
		if ($fields === NULL) {
			// The user class is used for both TCA and non TCA-based additional parameters
			// and the standard text area should be shown
			// see http://forge.typo3.org/issues/53287
			return;
		}

		if ($removeStandardField) {
			// Remove standard textarea
			unset($GLOBALS['TCA']['sys_dmail_group']['columns']['tx_directmailuserfunc_params']);
		}

		if (count($fields) === 0) {
			// The user class does not need any additional parameters
			return;
		}

		$currentValues = Tx_DirectMailUserfunc_Utility_ItemsProcFunc::decodeUserParameters($row);

		// Prefix each additional field
		$prefixedFields = array(
			'columns' => array(),
			'types' => $fields['types'],
			'palettes' => isset($fields['palettes']) ? $fields['palettes'] : array(),
			'ctrl' => isset($fields['ctrl']) ? $fields['ctrl'] : array(),
		);
		foreach ($fields['columns'] as $field => $fieldInfo) {
			$virtualField = 'tx_directmailuserfunc_virtual_' . $field;
			$prefixedFields['columns'][$virtualField] = $fieldInfo;

			if (isset($currentValues[$field])) {
				// Populate virtual field's value
				$row[$virtualField] = $currentValues[$field];
			}

			foreach ($prefixedFields['types'] as &$typeInfo) {
				$typeInfo['showitem'] = preg_replace(
					'/(^|[, ])' . preg_quote($field) . '([,; ]|$)/',
					'$1' . $virtualField . '$2',
					$typeInfo['showitem']
				);
			}

			foreach ($prefixedFields['palettes'] as &$paletteInfo) {
				$paletteInfo['showitem'] = preg_replace(
					'/(^|[, ])' . preg_quote($field) . '([,; ]|$)/',
					'$1' . $virtualField . '$2',
					$paletteInfo['showitem']
				);
			}

			if (!empty($prefixedFields['ctrl']['requestUpdate'])) {
				$prefixedFields['ctrl']['requestUpdate'] = preg_replace(
					'/(^|[, ])' . preg_quote($field) . '([,; ]|$)/',
					'$1' . $virtualField . '$2',
					$prefixedFields['ctrl']['requestUpdate']
				);
			}
		}

		// Reconfigure TCA with virtual fields
		foreach ($prefixedFields['columns'] as $field => $fieldInfo) {
			$GLOBALS['TCA']['sys_dmail_group']['columns'][$field] = $fieldInfo;
		}

		foreach ($prefixedFields['types'] as $type => $typeInfo) {
			if (substr($GLOBALS['TCA']['sys_dmail_group']['types'][$type]['showitem'], -strlen($typeInfo['showitem'])) !== $typeInfo['showitem']) {
				$GLOBALS['TCA']['sys_dmail_group']['types'][$type]['showitem'] .= ',' . $typeInfo['showitem'];
			}
		}

		// sys_dmail_group has no initial palettes
		$GLOBALS['TCA']['sys_dmail_group']['palettes'] = $prefixedFields['palettes'];

		if (!empty($prefixedFields['ctrl']['requestUpdate'])) {
			$GLOBALS['TCA']['sys_dmail_group']['ctrl']['requestUpdate'] .= ',' . $prefixedFields['ctrl']['requestUpdate'];
		}
	}

}
