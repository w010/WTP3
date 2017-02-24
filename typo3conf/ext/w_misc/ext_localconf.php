<?php
defined('TYPO3_MODE') or die();


/**
 * Add Context info to backend's System Information toolbar item
 */
if (TYPO3_MODE === 'BE' && !(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
	/** @var $signalSlotDispatcher TYPO3\CMS\Extbase\SignalSlot\Dispatcher */
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
	$signalSlotDispatcher->connect(
		// to signal 'loadMessages' in SystemInformationToolbarItem...
		\TYPO3\CMS\Backend\Backend\ToolbarItems\SystemInformationToolbarItem::class,
		'loadMessages',
		// we connect our controller with custom message. it will run if the signal triggers
		\WTP\WMisc\Backend\Controller\SystemInformationController::class,
		'appendMessage'
	);
}


// example of xclass in 6.x / 7.x way
// https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/Xclasses/Index.html
/*$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Backend\Backend\ToolbarItems\SystemInformationToolbarItem'] = array(
    'className' => 'WTP\\WMisc\\Backend\\ToolbarItems\\SystemInformationToolbarItem'
);*/
