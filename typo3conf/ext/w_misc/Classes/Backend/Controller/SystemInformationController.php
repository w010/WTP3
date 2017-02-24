<?php
/*
 * wolo.pl '.' studio 2016
 * for Q3i
 */

namespace WTP\WMisc\Backend\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\Backend\ToolbarItems\SystemInformationToolbarItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
//use TYPO3\CMS\Backend\Toolbar\Enumeration\InformationStatus;
//use TYPO3\CMS\Extbase\Utility\LocalizationUtility;


/**
 * Display TYPO3 installation Q3i Context in the system information menu
 */
class SystemInformationController extends ActionController
{

	/**
	 * @var IconFactory
	 */
	protected $iconFactory;


    /**
     * Modifies the SystemInformation array
     *
     * @param SystemInformationToolbarItem $systemInformationToolbarItem
     */
    public function appendMessage(SystemInformationToolbarItem $systemInformationToolbarItem)
    {
	    $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);

	    // this is additional system information in system overview section
	    // Q3i instance context name
	    $systemInformationToolbarItem->addSystemInformation(
	        //LocalizationUtility::translate('systemmessage.instanceContext', 'wMisc'), // doesn't read lang label. why?
		    'Instance Context',
		    getenv("INSTANCE_CONTEXT"),
		    $this->iconFactory->getIcon('sysinfo-application-context', Icon::SIZE_SMALL)->render()
	        //InformationStatus::STATUS_INFO,   // api doesn't allow to set status from outside using addSystemInformation. maybe will be fixed in future versions
	    );

	    // Sometimes Context may be forced using putenv in AdditionalConfiguration, this will not be visible in backend toolbar
	    // due to bootstrap loading order. It will always show "Production" in such case.
	    // So we add it here again to see the actual context after config is loaded.
	    if (GeneralUtility::getApplicationContext() != 'Production')    {
		    $systemInformationToolbarItem->addSystemInformation(
			    'Real Application Context',
			    getenv("TYPO3_CONTEXT"),
			    $this->iconFactory->getIcon('sysinfo-application-context', Icon::SIZE_SMALL)->render()
		    );
	    }


	    // this is additional message on bottom of the container
		/*$systemInformationToolbarItem->addSystemMessage(
			//sprintf(LocalizationUtility::translate('systemmessage.errorsInPeriod', 'belog'), $count, BackendUtility::getModuleUrl('system_BelogLog')),
			'TEST',
			InformationStatus::STATUS_INFO,
			0
		);*/
    }
}
