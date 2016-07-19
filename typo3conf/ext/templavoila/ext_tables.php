<?php
defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {

	// Adding click menu item:
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = array(
		'name' => 'Extension\\Templavoila\\Service\\ClickMenu\\MainClickMenu',
	);

	// Adding backend modules:
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
		'web',
		'txtemplavoilaM1',
		'top',
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'mod1/'
	);

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
		'web',
		'txtemplavoilaM2',
		'',
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'mod2/'
	);

	$_EXTCONF = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['templavoila']);
	// Remove default Page module (layout) manually if wanted:
	if (!$_EXTCONF['enable.']['oldPageModule']) {
		$tmp = $GLOBALS['TBE_MODULES']['web'];
		$GLOBALS['TBE_MODULES']['web'] = str_replace(',,', ',', str_replace('layout', '', $tmp));
		unset ($GLOBALS['TBE_MODULES']['_PATHS']['web_layout']);
	}

	// Registering CSH:
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
		'be_groups',
		'EXT:templavoila/Resources/Private/Language/locallang_csh_begr.xlf'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
		'pages',
		'EXT:templavoila/Resources/Private/Language/locallang_csh_pages.xlf'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
		'tt_content',
		'EXT:templavoila/Resources/Private/Language/locallang_csh_ttc.xlf'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
		'tx_templavoila_datastructure',
		'EXT:templavoila/Resources/Private/Language/locallang_csh_ds.xlf'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
		'tx_templavoila_tmplobj',
		'EXT:templavoila/Resources/Private/Language/locallang_csh_to.xlf'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
		'xMOD_tx_templavoila',
		'EXT:templavoila/Resources/Private/Language/locallang_csh_module.xlf'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
		'xEXT_templavoila',
		'EXT:templavoila/Resources/Private/Language/locallang_csh_intro.xlf'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
		'_MOD_web_txtemplavoilaM1',
		'EXT:templavoila/Resources/Private/Language/locallang_csh_pm.xlf'
	);

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
		'web_func',
		'Extension\\Templavoila\\Controller\\ReferenceElementWizardController',
		NULL,
		'LLL:EXT:templavoila/Resources/Private/Language/locallang.xlf:wiz_refElements'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
		'web_func',
		'Extension\\Templavoila\\Controller\\RenameFieldInPageFlexWizardController',
		NULL,
		'LLL:EXT:templavoila/Resources/Private/Language/locallang.xlf:wiz_renameFieldsInPage'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('_MOD_web_func', 'EXT:wizard_crpages/locallang_csh.xlf');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_templavoila_datastructure');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_templavoila_tmplobj');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
	array(
		'LLL:EXT:templavoila/Resources/Private/Language/locallang_db.xlf:tt_content.CType_pi1',
		$_EXTKEY . '_pi1',
		'EXT:' . $_EXTKEY . '/Resources/Public/Icon/icon_fce_ce.png'
	),
	'CType'
);

// complex condition to make sure the icons are available during frontend editing...
if (
	TYPO3_MODE === 'BE' ||
	(
		TYPO3_MODE === 'FE'
		&& isset($GLOBALS['BE_USER'])
		&& method_exists($GLOBALS['BE_USER'], 'isFrontendEditingActive')
		&& $GLOBALS['BE_USER']->isFrontendEditingActive()
	)
) {
	$icons = array(
		'paste' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('templavoila') . 'mod1/clip_pasteafter.gif',
		'pasteSubRef' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('templavoila') . 'mod1/clip_pastesubref.gif',
		'makelocalcopy' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('templavoila') . 'mod1/makelocalcopy.gif',
		'clip_ref' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('templavoila') . 'mod1/clip_ref.gif',
		'clip_ref-release' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('templavoila') . 'mod1/clip_ref_h.gif',
		'unlink' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('templavoila') . 'mod1/unlink.png',
		'htmlvalidate' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('templavoila') . 'Resources/Public/Icon/html_go.png',
		'type-fce' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('templavoila') . 'Resources/Public/Icon/icon_fce_ce.png',
		'templavoila-logo' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('templavoila') . 'Resources/Public/Image/templavoila-logo.png',
		'templavoila-logo-small' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('templavoila') . 'Resources/Public/Image/templavoila-logo-small.png',
	);
	\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons($icons, $_EXTKEY);
}
