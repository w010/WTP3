<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 wolo <wolo.wolski@gmail.com>
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Raw HTML' for the 'w_rawhtml' extension.
 *
 * @author	wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wrawhtml
 */
class tx_wrawhtml_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_wrawhtml_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wrawhtml_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'w_rawhtml';	// The extension key.
	var $pi_checkCHash = true;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf)	{
		$this->pi_initPIflexForm('CType_flexform');
		
		return $this->getConfVar('html', 'sDEF', 'CType_flexform');
	}


	function getConfVar($var, $sheetName = 'sDEF', $field = 'pi_flexform') {
		return $this->pi_getFFvalue($this->cObj->data[$field], $var, $sheetName) ? $this->pi_getFFvalue($this->cObj->data[$field], $var, $sheetName) : $this->getTSConfVar($var);
	}

	function getTSConfVar($var) {
		if (isset($this->conf[$var]) && isset($this->conf[$var.'.'])) {
			return $this->cObj->cObjGetSingle($this->conf[$var], $this->conf[$var.'.']);
		} else {
			return $this->conf[$var];
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/w_rawhtml/pi1/class.tx_wrawhtml_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/w_rawhtml/pi1/class.tx_wrawhtml_pi1.php']);
}

?>