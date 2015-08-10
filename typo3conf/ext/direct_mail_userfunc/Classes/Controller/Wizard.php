<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2015 Xavier Perseguers (xavier@causal.ch)
 *  (c) 2009-2011 Xavier Perseguers (typo3@perseguers.ch)
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
 * This class encapsulates display of a user wizard.
 *
 * @category    Controller
 * @package     direct_mail_userfunc
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   2012-2015 Causal Sàrl
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Tx_DirectMailUserfunc_Controller_Wizard {

	/**
	 * Default constructor.
	 */
	public function __construct() {
		$GLOBALS['LANG']->includeLLFile('EXT:direct_mail_userfunc/Resources/Private/Language/locallang_tca.xml');
	}

	/**
	 * Returns code to show whether the itemsProcFunc definition is valid.
	 *
	 * @param array $PA TCA configuration passed by reference
	 * @param t3lib_TCEforms|\TYPO3\CMS\Backend\Form\FormEngine $pObj
	 * @return string HTML snippet to be put after the itemsProcFunc field
	 */
	public function itemsprocfunc_procWizard(array &$PA, /* t3lib_TCEforms */ $pObj) {
		$itemsProcFunc = $PA['row']['tx_directmailuserfunc_itemsprocfunc'];
		if (!$itemsProcFunc) {
			// Show the required icon
			$PA['item'] = static::getIcon('gfx/required_h.gif') . $PA['item'];
		}

		// Show the user function provider selector
		static::addUserFunctionProviders($PA);

		if (!$itemsProcFunc) {
			return;
		} elseif (Tx_DirectMailUserfunc_Utility_ItemsProcFunc::isMethodValid($itemsProcFunc)) {

			$this->appendItemContent($PA, static::getIcon('gfx/icon_ok.gif'));
		} elseif (!Tx_DirectMailUserfunc_Utility_ItemsProcFunc::isClassValid($itemsProcFunc)) {
			$this->appendItemContent($PA, static::getIcon('gfx/icon_warning.gif') . ' ' . $GLOBALS['LANG']->getLL('wizard.itemsProcFunc.invalidClass'));
		} else {
			$this->appendItemContent($PA, static::getIcon('gfx/icon_warning.gif') . ' ' . $GLOBALS['LANG']->getLL('wizard.itemsProcFunc.invalidMethod'));
		}
	}

	/**
	 * Appends some content to the PA's item (next to it).
	 *
	 * @param array $PA
	 * @param string $content
	 * @return void
	 */
	protected function appendItemContent(array &$PA, $content) {
		$PA['item'] = preg_replace('#(<div style="clear:both;"></div>)$#s', $content . '$1', $PA['item']);
	}

	/**
	 * Returns code to show a user-handled wizard associated to current
	 * itemsProcFunc value.
	 *
	 * @param array $PA TCA configuration passed by reference
	 * @param t3lib_TCEforms|\TYPO3\CMS\Backend\Form\FormEngine $pObj Parent object
	 * @return string HTML snippet to be put after the params field
	 */
	public function params_procWizard(array &$PA, /* t3lib_TCEforms */ $pObj) {
		$itemsProcFunc = $PA['row']['tx_directmailuserfunc_itemsprocfunc'];
		if (!Tx_DirectMailUserfunc_Utility_ItemsProcFunc::hasWizard($itemsProcFunc)) {
			return '';
		}

		$autoJS = TRUE;
		$wizardJS = Tx_DirectMailUserfunc_Utility_ItemsProcFunc::callWizard($itemsProcFunc, $PA, $autoJS, $pObj);

		if (!$wizardJS) {
			return '';
		}

		$altIcon = $GLOBALS['LANG']->getLL('wizard.parameters.title');
		if ($autoJS) {
			if (substr($wizardJS, -1) !== ';') {
				$wizardJS .= ';';
			}
			$wizardJS .= 'return false;';

			$output = '<a href="#" onclick="' . htmlspecialchars($wizardJS) . '" title="' . $altIcon . '">';
			$output .= static::getIcon('gfx/options.gif');
			$output .= '</a>';
		} else {
			$output = static::getIcon('gfx/options.gif', $altIcon, 'id="params-btn" style="cursor:pointer"');
			$output .= $wizardJS;
		}

		return $output;
	}

	/**
	 * Returns a HTML image tag with a given icon (taking t3skin into account).
	 *
	 * @param string $src Image source
	 * @param string $alt Alternate text
	 * @param string $params Additional parameters for the img tag
	 * @return string
	 */
	static protected function getIcon($src, $alt = '', $params = '') {
		return '<img ' . t3lib_iconWorks::skinImg($GLOBALS['BACKPATH'], $src) .
			' alt="' . $alt . '" title="' . $alt . '" vspace="4" align="absmiddle" ' . $params .'/>';
	}

	/**
	 * Prepends a user function provider selector to the itemsProcFunc field.
	 *
	 * @param array $PA TCA configuration passed by reference
	 * @return void
	 */
	static protected function addUserFunctionProviders(array &$PA) {
		if (!count($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['direct_mail_userfunc']['userFunc'])) {
			return;
		}

		// Sort list of providers
		$providers = array();
		foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['direct_mail_userfunc']['userFunc'] as $provider) {
			$itemsProcFunc = $provider['class'] .'->' . $provider['method'];
			$providers[$itemsProcFunc] = static::getLL($provider['label']);
		}
		asort($providers);

		$hasOptionSelected = FALSE;
		$options = array();
		foreach ($providers as $itemsProcFunc => $label) {
			$selected = '';
			if ($PA['row']['tx_directmailuserfunc_itemsprocfunc'] === $itemsProcFunc) {
				$hasOptionSelected = TRUE;
				$selected = ' selected="selected"';
			}
			$options[] = sprintf('<option value="%s"%s>%s</option>', $itemsProcFunc, $selected, $label);
		}

		$updateJS = 'var itemsProcFunc = document.' . $PA['formName'] . '[\'userfunc_provider\'].value;';
		$updateJS .= 'document.' . $PA['formName'] . '[\'' . $PA['itemName'] . '\'].value = itemsProcFunc;';
		$updateJS .= implode('', $PA['fieldChangeFunc']);
		// Automatically reload edit form
		$updateJS .= 'if (confirm(TBE_EDITOR.labels.onChangeAlert) &amp;&amp; TBE_EDITOR.checkSubmit(-1)){ TBE_EDITOR.submitForm() };';

		$selector = '
			<select name="userfunc_provider" onchange="' . $updateJS . '">
				<option value=""></option>' .
				join('', $options) . '
			</select>
		';

		$hideInput = $hasOptionSelected && Tx_DirectMailUserfunc_Utility_ItemsProcFunc::isMethodValid($PA['row']['tx_directmailuserfunc_itemsprocfunc']);

		// Prepend the provider selector
		$PA['item'] = $GLOBALS['LANG']->getLL('wizard.itemsProcFunc.providers') . $selector .
			($hideInput ? '<div style="display:none">' : '') . '<br />' . $PA['item'] . ($hideInput ? '</div>' : '');
	}

	/**
	 * Returns the label with key $index for current backend language.
	 *
	 * @param string $label Label/key reference
	 * @return string
	 */
	static public function getLL($label) {
		if (strcmp(substr($label, 0, 8), 'LLL:EXT:')) {
			// Non-localizable string provided
			return $label;
		}

		$label = substr($label, 8);	// Remove 'LLL:EXT:' at the beginning
		$extension = substr($label, 0, strpos($label, '/'));
		$references = explode(':', substr($label, strlen($extension) + 1));
		if (!($extension && count($references))) {
			return $label;
		}

		$file = t3lib_extMgm::extPath($extension) . $references[0];
		$index = $references[1];
		$LOCAL_LANG = t3lib_div::readLLfile($file, $GLOBALS['LANG']->lang);

		$ret = $label;
		if (version_compare(TYPO3_version, '4.6.0', '<')) {
			if (strcmp($LOCAL_LANG[$GLOBALS['LANG']->lang][$index], '')) {
				$ret = $LOCAL_LANG[$GLOBALS['LANG']->lang][$index];
			} else {
				$ret = $LOCAL_LANG['default'][$index];
			}
		} else {
			if (strcmp($LOCAL_LANG[$GLOBALS['LANG']->lang][$index][0]['target'], '')) {
				$ret = $LOCAL_LANG[$GLOBALS['LANG']->lang][$index][0]['target'];
			} else {
				$ret = $LOCAL_LANG['default'][$index][0]['target'];
			}
		}

		return $ret;
	}

}
