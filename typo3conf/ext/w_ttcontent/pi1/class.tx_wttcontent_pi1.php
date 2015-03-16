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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_site.'typo3conf/ext/ryzy_flash/pi1/class.tx_ryzyflash_pi1.php');


/**
 * Plugin 'page visuals: menu of these pages' for the 'w_ttcontent' extension.
 *
 * @author	Marcin Ryżycki <marcin@ryzycki.pl>
 * @package	TYPO3
 * @subpackage	tx_wttcontent
 */
class tx_wttcontent_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_wttcontent_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wttcontent_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'w_ttcontent';	// The extension key.
	var $pi_checkCHash = true;

	/**
	 * @var tslib_cObj
	 */
	var $cObj;

	var $visualMode = 0;
	var $visualSize = 0;

	var $_columns = 999;

	var $lConf = array();


	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website (Menu)
	 */
	function main($content, $conf)	{
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
		$menuPid = $this->cObj->data['pages']?$this->cObj->data['pages']:$GLOBALS['TSFE']->id;
		$menuPids = t3lib_div::intExplode(',', $menuPid);

			// Now, get an array with all the subpages to this pid:
			// (Function getMenu() is found in class.t3lib_page.php)
		$menuItems_level1 = Array();
		foreach ($menuPids as $pid) {
			$menuItems_level1[] = $GLOBALS['TSFE']->sys_page->getPage($pid);
		}

		return $this->content($menuItems_level1);
	}

	function content($pagesArray) {
		$content = '<div class="csc-menu csc-menu-visual csc-menu-visual-'.$this->visualMode.' csc-menu-visual-col'.$this->_columns.'" id="ce_'.$this->cObj->data['uid'].'">  <ul> ';
		$i = 0;
		$j = 0;
		foreach ($pagesArray as $page) {
			$i++;
			$j++;
			if ($i==count($pagesArray))	$last = true;
			$content .= $this->renderPage($page, $j, $last);
			if ( (!($i%$this->_columns) && $i!=count($pagesArray)) )	{
				$content .=  '<br class="clear" />';
				$j = 0;
			}
		}
		//$content .= '<div class="clear">&nbsp;</div></div>';
		$content .= '</ul> </div>';

		$content .= str_replace('@@@menu_id@@@', '#ce_'.$this->cObj->data['uid'], $this->conf['additional_html_after']);

		return $content;
	}

	function renderPage($pageRow, $numInDisplayRow, $last) {
		$width = $this->conf['menuVisualsWidth'.$this->visualSize];
		$height = $this->conf['menuVisualsHeight'.$this->visualSize];

		$url = $this->cObj->getTypoLink_URL($pageRow['uid']);

		$moreURL = '<div class="link-more dotlink">'.$this->cObj->getTypoLink($this->pi_getLL('link_more', 'więcej'), $pageRow['uid']).'</div>';
		$actClass = ($pageRow['uid'] == $GLOBALS['TSFE']->id) ? ' act' : '';
		$lastClass = $last ? ' last' : '';

		$gfx = $this->_getGfx($width, $height, $this->_getResourceToDisplay($pageRow['tx_wttcontent_visual']), $url);
		$title = '<h4><a href="'.$url.'">'.($pageRow['nav_title']?$pageRow['nav_title']:$pageRow['title']).'</a></h4>';
		$titleNolink = ($pageRow['nav_title']?$pageRow['nav_title']:$pageRow['title']);

		// sprawdzamy, czy jest skonfigurowana userfunkcja i czy nie ma ręcznie wpisanego abstractu (w takim wypadku bierzemy tego)
		//if ($this->lConf['abstract'] == 'abstract'  &&  $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_ttcontent']['abstract_userfunc']  &&  !$pageRow['abstract'])	{
		if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_ttcontent']['abstract_userfunc']  &&  !$pageRow['abstract'])	{
			// jesli jest, wywolujemy i przypisujemy wynik. jesli nie - default (z pola abstract)

			$config = array('pageRow' => $pageRow);
			$abstract = t3lib_div::callUserFunction($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_ttcontent']['abstract_userfunc'], &$config, &$this);
			$abstract = $abstract ? $abstract : 'no abstract';
		}
		else	{
			$abstract = $pageRow['abstract'];
		}

		$abstract = '<div class="abstract">'.$abstract.'</div>';

		$content = '<li class="'.$actClass . $lastClass .'"> <div class="menu-item menu-item-col'.$numInDisplayRow . '">';
		
		switch ($this->visualMode) {
			// only text, no gfx
			case 1:
				$content .= $title.$abstract;
				break;
			// only gfx, no text, no additional links
			case 2:
				$content .= $gfx;
				break;
			// gfx with text and more-link
			case 3:
				$content .= '<div class="image"><a href="'.$url.'" onfocus="this.blur();return false">'.$gfx.'</a></div>';
				$content .= $title . $abstract . $moreURL;
				break;
			case 4:
			// nolink gfx and linked text
				$content .= $gfx . $title . $abstract;
				break;
			default:
				$content .= '<a href="'.$url.'" onfocus="this.blur();return false"> <div class="image">'.$gfx.'</div>';
				$content .= '<h4>'.$titleNolink.'</h4> </a>' . $abstract;
		}

		$content .= '</div> </li>';

		return $content;
	}

	function _getGfx($w, $h, $resource, $clickURL) {
		if ($resource['type'] == 'flash')	{
			$ryzyFlash = t3lib_div::makeInstance('tx_ryzyflash_pi1');
			$ryzyFlash->cObj = $this->cObj;

			$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_ryzyflash_pi1.'];
			$conf['swfFile'] = $resource['file'];
			$conf['width'] = $w;
			$conf['height'] = $h;
			//$conf['wmode'] = 'transparent';
			$conf['flashVars'] = "
				corners={$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_ttcontent']['imageDisplayerCorners']}
				frameColor={$GLOBALS['TSFE']->tmpl->setup['lib.']['imageDisplayerFrameColor']}
				clickURL=$clickURL
			";
			$conf['cssClassName'] = 'flash-image';

			return $ryzyFlash->main('', $conf);
		}
		else	{
			$imageConf = Array();
			$imageConf['file'] = $resource['file'];
			$img = $this->cObj->IMAGE($imageConf);

			return $img;
		}
	}

	function _getResourceToDisplay($visualFilename) {
		if (!$visualFilename) $visualFilename = 'no_image.png';

		$ext = pathinfo($visualFilename, PATHINFO_EXTENSION);
		if ($ext == 'swf')	$type = 'flash';
		else						$type = 'image';

		$filePath = $this->conf['menuVisualsUploadDir'].$visualFilename;
		return array('file' => $filePath, 'type' => $type);
	}

	function _initFlexConf($flexField = 'pi_flexform')	{
		$flexForm = $this->cObj->data[$flexField];
			// Traverse the entire array based on the language...
			// and assign each configuration option to $this->lConf array...
		foreach ($flexForm['data'] as $sheet => $data)	{
			foreach ($data as $lang => $value)		{
				foreach ($value as $key => $val)	{
					$this->lConf["$key"] = $this->pi_getFFvalue($flexForm, $key, $sheet);
				}
			}
		}
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/w_ttcontent/pi1/class.tx_wttcontent_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/w_ttcontent/pi1/class.tx_wttcontent_pi1.php']);
}

?>
