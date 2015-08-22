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

// no need to include anymore if class lies in Classes/Pibase.php
// http://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/Autoloading/Index.html#autoload
// doesn't work as expected, so used autoload
//require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('w_tools').'class.tx_wtools_pibase.php');


use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Plugin 'Universal XML import' for w_tools
 *
 * @author	wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wtools
 */
class tx_wtools_pi3 extends tx_wtools_pibase    {
	var $prefixId      = 'tx_wtools_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_wtools_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'w_tools';	// The extension key.
	var $pi_checkCHash = true;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		/*$info = '';
		if ($_GET['testdebugster']){
			//$info .= '<br> time przed 1 debugsterem: '.(time() - $GLOBALS['EXEC_TIME']);
			$info .= '<br> time przed 1 debugsterem: '. ( round((microtime(true) - $GLOBALS['__start']) * 1000, 1) );
			debugster(1);
			$info .= '<br> time przed 2 debugsterem: '. ( round((microtime(true) - $GLOBALS['__start']) * 1000, 1) );
			debugster('test');
			$info .= '<br> time przed 3 debugsterem: '. ( round((microtime(true) - $GLOBALS['__start']) * 1000, 1) );
			debugster('test 2');
		}

		$info .= '<br> total parsetime po debugsterach: '.( round((microtime(true) - $GLOBALS['__start']) * 1000, 1) );

		return $info;*/



		// temporary - import sw additional cities - http://www.mantis.kbsystems.pl/view.php?id=4779
//		return $this->importSWcities();



		/** @var $import tx_wtools_import_crm */
		$import = GeneralUtility::makeInstance('tx_wtools_import_crm');
		$res = $import->execute();

		debugster($res);
		
		die('END.');	
		$content = 'IMPORT';
	
		return $content;
	}




	public function importSWcities()    {

		// temporary - import sw second city

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'fe_users',
			'1=1 AND pid = 45' . $this->cObj->enableFields('fe_users'),
			'', '', '',    // group, order, limit
			'uid'        // arrow by key
		);


		// read csv
		$csv = readCSV(PATH_site.'typo3conf/ext/w_tools/pi3/sw-cities.csv');
		$csv[] = [];    //add empty item on end to not loose last item in iteration...

		$newItems = [];
		$itemTemp = [];
		foreach ($csv as $row) {
			// same sw name, treat as one
			if ($itemTemp['name'] == $row[1]) {
				$itemTemp['cities'][] = $row[2];    // add cities
			}
			else  {
				if ($itemTemp)  $newItems[] = $itemTemp;
				// new sw
				$itemTemp = [
					'name' => $row[1],
					'city' => $row[0],
					'cities' => [ $row[2] ],
				];
			}
		}

		//debugster($newItems);

		$counter = 0;
		foreach ($res as $row) {
			//debugster($row);

			// take name and find it in newItems
			foreach ($newItems as $k => $newItem) {
				if ($newItem['name'] == $row['name'])   {

					// take cities from there
					$city = $newItem['city'];
					$cities = $newItem['cities'];

					// update in db
					debugster($row['name']);
					debugster($city);
					debugster($cities);
//					die();

					$updateArray = [
						'city' => $city,
						'citysecond' => implode(',', $cities)
					];

					$counter += (int) $GLOBALS['TYPO3_DB']->exec_UPDATEquery ('fe_users','uid = '.$row['uid'], $updateArray);

					unset($newItems[$k]);

					break;
				}
			}
		}
		debugster($counter);
		//debugster($newItems);   // te, ktore zostaly nieznalezione w biezacej bazie

		// end

		die('stop');

	}
}




function readCSV($csvFile){
	$file_handle = fopen($csvFile, 'r');
	while (!feof($file_handle) ) {
		$line_of_text[] = fgetcsv($file_handle, 1024);
	}
	fclose($file_handle);
	return $line_of_text;
}


/* import class extends this class. why can't just include it? what was the problem? */

/*class tx_scheduler_Task	{
	public function __construct() {	 }
	public function execute() {	 }
}*/



?>