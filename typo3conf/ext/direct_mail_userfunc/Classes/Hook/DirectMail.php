<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014-2015 Xavier Perseguers <xavier@causal.ch>
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
 * This class hooks into direct_mail to post-process the list of recipients.
 *
 * @category    Hook
 * @package     direct_mail_userfunc
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   2014-2015 Causal Sàrl
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Tx_DirectMailUserfunc_Hook_DirectMail {

	/**
	 * Post-processes the list of recipients.
	 *
	 * @param array $id_lists
	 * @param object $pObj parent object
	 * @param array $groups
	 * @return array
	 */
	public function cmd_compileMailGroup_postProcess(array $id_lists, $pObj, array $groups) {
		$mailGroups = array();

		if (!isset($groups['uid'])) {
			// Coming from mod2 (Dmail)
			foreach ($groups as $group) {
				// Testing to see if group ID is a valid integer, if not - skip to next group ID
				if (t3lib_div::compat_version('4.6')) {
					$group = t3lib_utility_Math::convertToPositiveInteger($group);
				} else {
					$group = t3lib_div::intval_positive($group);
				}
				if (!$group) {
					continue;
				}

				$mailGroup = t3lib_BEfunc::getRecord('sys_dmail_group', $group);
				if (is_array($mailGroup)) {
					$mailGroups[] = $mailGroup;
				}
			}
		} else {
			// Coming from mod5 (RecipientList)
			$mailGroups[] = $groups;
		}

		foreach ($mailGroups as $mailGroup) {
			if ((int)$mailGroup['type'] === 5) {
				$recipientList = $this->generateRecipientList($mailGroup, $pObj);
				$id_lists = array_merge_recursive($id_lists, $recipientList);
			}
		}

		return $id_lists;
	}

	/**
	 * Generates the list of recipients.
	 *
	 * @param array $mailGroup
	 * @param object $pObj parent object
	 * @return array
	 */
	protected function generateRecipientList(array $mailGroup, $pObj) {
		$recipientList = array(
			'tt_address' => array(),
			'fe_users' => array(),
			'PLAINLIST' => array(),
		);
		$itemsProcFunc = $mailGroup['tx_directmailuserfunc_itemsprocfunc'];
		if (Tx_DirectMailUserfunc_Utility_ItemsProcFunc::isMethodValid($itemsProcFunc)) {
			$userParams = $mailGroup['tx_directmailuserfunc_params'];
			if (Tx_DirectMailUserfunc_Utility_ItemsProcFunc::hasWizardFields($itemsProcFunc)) {
				$fields = Tx_DirectMailUserfunc_Utility_ItemsProcFunc::callWizardFields($itemsProcFunc);
				if ($fields !== NULL) {
					$userParams = count($fields) === 0
						? array()
						: Tx_DirectMailUserfunc_Utility_ItemsProcFunc::decodeUserParameters($mailGroup);
				}
			}

			$params = array(
				'groupUid' => $mailGroup['uid'],
				'lists' => &$recipientList,
				'userParams' => $userParams,
			);
			t3lib_div::callUserFunction($itemsProcFunc, $params, $pObj);

			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['direct_mail_userfunc']);
			if (!isset($extConf['makeEntriesUnique']) || $extConf['makeEntriesUnique'] == 1) {
				// Make unique entries
				$recipientList['tt_address'] = array_unique($recipientList['tt_address']);
				$recipientList['fe_users'] = array_unique($recipientList['fe_users']);
				if (version_compare(TYPO3_version, '6.2.0', '>=')) {
					$recipientList['PLAINLIST'] = \DirectMailTeam\DirectMail\DirectMailUtility::cleanPlainList($recipientList['PLAINLIST']);
				} else {
					$recipientList['PLAINLIST'] = tx_directmail_static::cleanPlainList($recipientList['PLAINLIST']);
				}
			}
		}

		return $recipientList;
	}

}