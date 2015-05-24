<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types'][$_EXTKEY . '_pi1']['showitem'] = 'CType;;4;button;1-1-1, header;;3;;2-2-2';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:w_rawhtml/locallang_db.xml:tt_content.CType_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'CType');




// content cType flexform
$tempColumns  =  Array (
    'CType_flexform'  => Array (
        'label'  => 'LLL:EXT:w_rawhtml/locallang_db.php:tt_content.CType_pi1_flexform',
        'config'  => Array (
            'type'  => 'flex',
            'ds_pointerField'  => 'CType',
            'ds' => Array(
					$_EXTKEY.'_pi1' => 'FILE:EXT:w_rawhtml/pi1/flexform_ds.xml',
				),
        )
    ),
);
t3lib_extMgm::addTCAcolumns("tt_content", $tempColumns);

$TCA['tt_content']['types'][$_EXTKEY.'_pi1']['subtype_value_field'] = 'CType';
$TCA['tt_content']['types'][$_EXTKEY.'_pi1']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'CType_flexform';




?>