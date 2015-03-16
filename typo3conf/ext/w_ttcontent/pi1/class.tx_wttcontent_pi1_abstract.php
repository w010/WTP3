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

require_once (t3lib_extMgm::extPath('w_rating').'pi1/class.tx_wrating_pi1.php');


/**
 * Addition plugin 'Auto abstract for the 'page visuals: menu of these pages' plugin from the 'w_ttcontent' extension.
 *
 * @author	Wolo Wolski <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wttcontent
 */
class tx_wttcontent_pi1_abstract	{


	function getRating(&$config, &$pObj)	{
		$r_conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_wrating_pi1.'];
		$r_conf['mode'] = 'stars';
		$r_conf['record_uid'] = $config['pageRow']['uid'];
		unset($r_conf['record_uid.']);	//['special'] = '';

		$Rating = t3lib_div::makeInstance('tx_wrating_pi1');
		$Rating->cObj = $pObj->cObj;
		return $Rating->main('', $r_conf);
	}
}


/**
* returns first occurance of needle
*
* @param mixed $needle
* @param array $haystack
*/
function array_search_path($needle, $haystack)	{
	$_keys = array();

	foreach(is_array($haystack) ? $haystack : array()  as  $key => $val)	{

		if ($val == $needle)	{
			$_keys[] = $key;
			break;
		}
		else if (is_array($val))	{
			$res = array_search_path($needle, $val);

			if (count($res))	{
				$_keys[$key] = $res;
				break;
	}}}

	return $_keys;
}


?>