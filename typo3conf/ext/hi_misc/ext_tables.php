<?php
if (!defined ("TYPO3_MODE"))    die ("Access denied.");

$tempColumns = array (
	'tx_himisc_cssclass' => array (
		'exclude' => 1,
		'label' => 'Custom CSS class:',
		'config' => array (
			'type' => 'input',
			'size' => 8,
		)
	),
);

/*t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('pages','tx_himisc_cssclass', '', 'before:tx_templavoila_to');
t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addTCAcolumns('tt_content',$tempColumns,1);
$TCA['tt_content']['palettes']['4']['showitem'] .= ',tx_himisc_cssclass'; // doesn't work in 4.5
$TCA['tt_content']['palettes']['frames']['showitem'] .= ',tx_himisc_cssclass'; // works in 4.5*/

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages',$tempColumns,1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages','tx_himisc_cssclass', '', 'before:tx_templavoila_to');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content',$tempColumns,1);
$TCA['tt_content']['palettes']['4']['showitem'] .= ',tx_himisc_cssclass'; // doesn't work in 4.5
