<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addPlugin(array('LLL:EXT:w_ttcontent/locallang_db.xml:tt_content.menu_type_pi1', $_EXTKEY.'_pi1'),'menu_type');
t3lib_extMgm::addPlugin(array('LLL:EXT:w_ttcontent/locallang_db.xml:tt_content.menu_type_pi2', $_EXTKEY.'_pi2'),'menu_type');


$tempColumns = Array (
	"tx_wttcontent_visual" => Array (
		"exclude" => 0,
		"label" => "LLL:EXT:w_ttcontent/locallang_db.xml:pages.tx_wttcontent_visual",
		"config" => Array (
			"type" => "group",
			"internal_type" => "file",
			"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"].',swf',
			"max_size" => 10000,
			//"uploadfolder" => 'uploads/pics',
			"uploadfolder" => $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_ttcontent']['menuVisuals'][3]['menuVisualsUploadDir'],
			"show_thumbs" => 1,
			"size" => 2,
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
);
t3lib_div::loadTCA("pages");
t3lib_extMgm::addTCAcolumns("pages", $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes("pages","tx_wttcontent_visual;;;;1-1-1", '1', 'after:abstract');



$tempColumns = Array (
	"tx_wttcontent_visual_mode" => Array (
		"exclude" => 0,
		"label" => "LLL:EXT:w_ttcontent/locallang_db.xml:pages.tx_wttcontent_visual_mode",
		"config" => Array (
			"type" => "select",
			'items' => Array (
				Array('', '0'),
				Array('only text', '1'),
				Array('only visual', '2'),
				//Array('visual with text and more-link', '3'),
			)
		)
	),
	"tx_wttcontent_visual_size" => Array (
		"exclude" => 0,
		"label" => "LLL:EXT:w_ttcontent/locallang_db.xml:pages.tx_wttcontent_visual_size",
		"config" => Array (
			"type" => "select",
			'items' => Array (
				Array('standard', '0'),
				Array('bigger size', '1'),
			)
		)
	),
);
t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content", $tempColumns);
t3lib_extMgm::addToAllTCAtypes("tt_content","tx_wttcontent_visual_mode;;;3-3-3, tx_wttcontent_visual_size", 'menu');






// content sitemap/menu flexform
$tempColumns  =  Array (
    'menu_flexform'  => Array (
        'label'  => 'LLL:EXT:w_ttcontent/locallang_db.php:tt_content.menu_flexform',
        'config'  => Array (
            'type'  => 'flex',
            'ds_pointerField'  => 'menu_type',
            'ds' => Array(
					$_EXTKEY.'_pi1,menu' => 'FILE:EXT:w_ttcontent/pi1/flexform_ds.xml',
					$_EXTKEY.'_pi2,menu' => 'FILE:EXT:w_ttcontent/pi2/flexform_ds.xml',
			),
			'search' => Array(
				'andWhere' => "CType='menu'",
			),
        ),
    ),
);
t3lib_extMgm::addTCAcolumns("tt_content", $tempColumns);

$TCA['tt_content']['types']['menu']['subtype_value_field'] = 'menu_type';
$TCA['tt_content']['types']['menu']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'menu_flexform';
$TCA['tt_content']['types']['menu']['subtypes_addlist'][$_EXTKEY.'_pi2'] = 'menu_flexform';

?>