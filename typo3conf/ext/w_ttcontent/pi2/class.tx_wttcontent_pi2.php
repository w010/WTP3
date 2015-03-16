<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Marcin Ryżycki <marcin@ryzycki.pl>
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

require_once(PATH_site.'typo3conf/ext/w_ttcontent/pi1/class.tx_wttcontent_pi1.php');

/**
 * Plugin 'page visual: menu of subpages to these pages' for the 'w_ttcontent' extension.
 *
 * @author	Marcin Ryżycki <marcin@ryzycki.pl>
 * @package	TYPO3
 * @subpackage	tx_wttcontent
 */
class tx_wttcontent_pi2 extends tx_wttcontent_pi1 {
	var $prefixId      = 'tx_wttcontent_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_wttcontent_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'w_ttcontent';	// The extension key.
	var $pi_checkCHash = true;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website (Menu)
	 */
	function main($content,$conf)	{
		$this->visualMode = intval($this->cObj->data['tx_wttcontent_visual_mode']);
		$this->visualSize = intval($this->cObj->data['tx_wttcontent_visual_size']);

		$this->pi_initPIflexForm('menu_flexform');		//inicjalizacja ustawień flexforma
		$this->_initFlexConf('menu_flexform');
		//debugster($this->lConf);
		$this->conf = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_ttcontent']['menuVisuals'][$this->visualMode];
		$this->_columns = $this->lConf['columnsNum'] ? $this->lConf['columnsNum'] : $this->_columns;

			// Get the PID from which to make the menu.
			// If a page is set as reference in the 'Startingpoint' field, use that
			// Otherwise use the page's id-number from TSFE
		$menuPids = $this->cObj->data['pages']?$this->cObj->data['pages']:$GLOBALS['TSFE']->id;
		$menuPids = t3lib_div::intExplode(',',$menuPids);
		//debugster($menuPids);

		$menuItems_level1 = Array();
		foreach ($menuPids as $pid) {
			$menuItems_level1 = array_merge($menuItems_level1, $GLOBALS['TSFE']->sys_page->getMenu($pid));
		}

		return $this->content($menuItems_level1);
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/w_ttcontent/pi2/class.tx_wttcontent_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/w_ttcontent/pi2/class.tx_wttcontent_pi2.php']);
}

?>