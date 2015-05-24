<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2015 wolo <wolo.wolski@gmail.com>
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
 * wolo.pl '.' studio 2015
 * tools plugins
 */

//require_once(\TYPO3\CMS\Extbase\Utility\ExtensionUtility::extPath('w_tools').'class.tx_wtools_pibase.php');
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('tt_news').'pi/class.tx_ttnews.php');

/**
 * Plugin 'ce single tt_news' for the 'w_tools' extension.
 *
 * @author	wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wtools
 */
class tx_wtools_pi1 extends tx_wtools_pibase {
	var $prefixId	  = 'tx_wtools_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtools_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey		= 'w_tools';	// The extension key.
	var $pi_checkCHash = true;



	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		parent::main($content, $conf);

		$newsItemUid = $this->getConfVar('newsItem');

		if (!$newsItemUid)
			return 'plugin not configured! set record';

		$TtNews = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_ttnews');
		$TtNews->cObj = $this->cObj;
		$ttnewsConf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tt_news.'];

		$ttnewsConf['templateFile'] = $conf['templateFile'];
		$TtNews->piVars['tt_news'] = intval($newsItemUid);
		$ttnewsConf['code'] = 'SINGLE';

		return $TtNews->main_news('', $ttnewsConf);
	}
}



?>