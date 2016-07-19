<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE=='BE') {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('tools', 'txdevlogM1', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'mod1/');
}

// Includes
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('devlog', 'class.tx_devlog_tceforms.php'));

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_devlog');

$TCA['tx_devlog'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:devlog/locallang_db.xml:tx_devlog',		
		'label' => 'msg',	
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate DESC,uid',
		'rootLevel' => -1,
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'tca.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'icon_tx_devlog.gif',
	),
	'feInterface' => array(
		'fe_admin_fieldList' => 'severity, extkey, msg, location, line, data_var',
	)
);

// Add context sensitive help (csh) to the backend module and to the tx_devlog table
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('_MOD_tools_txdevlogM1', 'EXT:devlog/locallang_csh_txdevlog.xml');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_devlog', 'EXT:devlog/locallang_csh_txdevlog.xml');
?>
