<?php
namespace Ppi\TemplaVoilaPlus\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Ppi\TemplaVoilaPlus\Utility\TemplaVoilaUtility;

$GLOBALS['LANG']->includeLLFile(
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('templavoilaplus') . 'Resources/Private/Language/BackendControlCenter.xlf'
);

/**
 * Module 'TemplaVoila' for the 'templavoilaplus' extension.
 *
 * @author Kasper Skaarhoj <kasper@typo3.com>
 */
class BackendControlCenterController extends \TYPO3\CMS\Backend\Module\BaseScriptClass
{
    /**
     * @var array
     */
    protected $pidCache;

    /**
     * Import as first page in root!
     *
     * @var integer
     */
    public $importPageUid = 0;

    /**
     * @var array
     */
    public $pageinfo;

    /**
     * @var array
     */
    public $modTSconfig;

    /**
     * Extension key of this module
     *
     * @var string
     */
    public $extKey = 'templavoilaplus';

    /**
     * The name of the module
     *
     * @var string
     */
    protected $moduleName = 'web_txtemplavoilaplusCenter';

    /**
     * @var array
     */
    public $tFileList = array();

    /**
     * @var array
     */
    public $errorsWarnings = array();

    /**
     * holds the extconf configuration
     *
     * @var array
     */
    public $extConf;

    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    private $databaseConnection;

    /**
     * @return void
     */
    public function init()
    {
        $this->databaseConnection = TemplaVoilaUtility::getDatabaseConnection();
        parent::init();

        $this->moduleTemplate = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Template\ModuleTemplate::class);
        $this->iconFactory = $this->moduleTemplate->getIconFactory();
        $this->buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['templavoilaplus']);
    }

    /**
     * Preparing menu content
     *
     * @return void
     */
    public function menuConfig()
    {
        $this->MOD_MENU = [
        ];

        // page/be_user TSconfig settings and blinding of menu-items
        $this->modTSconfig = BackendUtility::getModTSconfig($this->id, 'mod.' . $this->moduleName);

        // CLEANSE SETTINGS
        $this->MOD_SETTINGS = BackendUtility::getModuleData($this->MOD_MENU, GeneralUtility::_GP('SET'), $this->moduleName);
    }

    /*******************************************
     *
     * Main functions
     *
     *******************************************/

    /**
     * Injects the request object for the current request or subrequest
     * As this controller goes only through the main() method, it is rather simple for now
     *
     * @param ServerRequestInterface $request the current request
     * @param ResponseInterface $response
     * @return ResponseInterface the response with the content
     */
    public function mainAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->init();
        $this->main();
        $response->getBody()->write($this->moduleTemplate->renderContent());
        return $response;
    }

    /**
     * Main function of the module.
     *
     * @return void
     */
    public function main()
    {
        // Access check!
        // The page will show only if there is a valid page and if this page may be viewed by the user
        $pageInfoArr = BackendUtility::readPageAccess($this->id, $this->perms_clause);
        $access = is_array($pageInfoArr);

        if ($access) {
            // Draw the header.

            // Add custom styles
            if (version_compare(TYPO3_version, '8.3.0', '>=')) {
                // Since TYPO3 8.3.0 EXT:extname/... is supported.
                $this->getPageRenderer()->addCssFile(
                    'EXT:' . $this->extKey . '/Resources/Public/StyleSheet/mod2_default.css'
                );
            } else {
                $this->getPageRenderer()->addCssFile(
                    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($this->extKey) . 'Resources/Public/StyleSheet/mod2_default.css'
                );
            }

            $this->getPageRenderer()->loadJquery();

            // Setup JS for ClickMenu/ContextMenu which isn't loaded by ModuleTemplate
            if (version_compare(TYPO3_version, '8.6.0', '>=')) {
                $this->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Backend/ContextMenu');
            } else {
                $this->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Backend/ClickMenu');
            }

            // Set up JS for dynamic tab menu and side bar
            $this->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Backend/Tabs');

            // Adding classic jumpToUrl function, needed for the function menu.
            // Also, the id in the parent frameset is configured.
            $this->moduleTemplate->addJavaScriptCode('templavoilaplus_function', '
                function jumpToUrl(URL)    { //
                    document.location = URL;
                    return false;
                }
                function setHighlight(id)    {    //
                    if (top.fsMod) {
                        top.fsMod.recentIds["web"]=id;
                        top.fsMod.navFrameHighlightedID["web"]="pages"+id+"_"+top.fsMod.currentBank;    // For highlighting

                        if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav)    {
                            top.content.nav_frame.refresh_nav();
                        }
                    }
                }
            ');

            $this->renderModuleContent();
        } else {
            $this->moduleTemplate->addFlashMessage(
                TemplaVoilaUtility::getLanguageService()->getLL('noaccess'),
                TemplaVoilaUtility::getLanguageService()->getLL('title'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::INFO
            );
        }

        $title = TemplaVoilaUtility::getLanguageService()->getLL('title');
        $header = $this->moduleTemplate->header($title);
        $this->moduleTemplate->setTitle($title);

        $this->moduleTemplate->getDocHeaderComponent()->setMetaInformation($pageInfoArr);
        $this->setDocHeaderButtons(!isset($pageInfoArr['uid']));

        if ($this->content) {
            $this->moduleTemplate->setContent($header . $this->content);
        }
    }

    /**
     * Gets the buttons that shall be rendered in the docHeader.
     *
     * @return array Available buttons for the docHeader
     */
    protected function setDocHeaderButtons()
    {
        $this->addCshButton('');
        $this->addShortcutButton();
    }

    /**
     * Adds csh icon to the right document header button bar
     */
    public function addCshButton($fieldName)
    {
        $contextSensitiveHelpButton = $this->buttonBar->makeHelpButton()
            ->setModuleName('_MOD_' . $this->moduleName)
            ->setFieldName($fieldName);
        $this->buttonBar->addButton($contextSensitiveHelpButton, ButtonBar::BUTTON_POSITION_RIGHT);
    }

    /**
     * Adds shortcut icon to the right document header button bar
     */
    public function addShortcutButton()
    {
        $shortcutButton = $this->buttonBar->makeShortcutButton()
            ->setModuleName($this->moduleName)
            ->setGetVariables(
                [
                    'id',
                ]
            )
            ->setSetVariables(array_keys($this->MOD_MENU));
        $this->buttonBar->addButton($shortcutButton, ButtonBar::BUTTON_POSITION_RIGHT);
    }

    /******************************
     *
     * Rendering module content:
     *
     *******************************/

    /**
     * Renders module content:
     *
     * @return void
     */
    public function renderModuleContent()
    {
        // If there are TO/DS, render the module as usual, otherwise do something else...:
        if ($this->isDataAvailable()) {
            $this->renderModuleContent_mainView();
        } else {
            $this->renderModuleContent_searchForTODS();
        }
    }

    /**
     * Returns true if data TO or DS Data is available on this->id
     *
     * @return bool
     */
    protected function isDataAvailable()
    {
        // We try TO first as DS may be outsourced into files which do not belong to PID
        return ($this->getCountTO($this->id) || $this->getCountDS($this->id));
    }

    /**
     * Returns real count of DS on given page id in contrast to dsRepository::getDatastructureCountForPid()
     *
     * @param integer $id Id of page to look into
     * @return integer Count of available DS
     */
    protected function getCountDS($id)
    {
        return $this->databaseConnection->exec_SELECTcountRows(
            'uid',
            'tx_templavoilaplus_datastructure',
            'pid=' . (int)$this->id . BackendUtility::deleteClause('tx_templavoilaplus_datastructure')
        );
    }

    /**
     * Returns count of TO in given page id should be same as tsRepository::getTemplateCountForPid()
     *
     * @param integer $id Id of page to look into
     * @return integer Count of available TO
     */
    protected function getCountTO($id)
    {
        return $this->databaseConnection->exec_SELECTcountRows(
            'uid',
            'tx_templavoilaplus_tmplobj',
            'pid=' . (int)$this->id . BackendUtility::deleteClause('tx_templavoilaplus_tmplobj')
        );
    }

    /**
     * Renders module content, overview of pages with DS/TO on.
     *
     * @return void
     */
    public function renderModuleContent_searchForTODS()
    {
        $dsRepo = GeneralUtility::makeInstance(\Ppi\TemplaVoilaPlus\Domain\Repository\DataStructureRepository::class);
        $toRepo = GeneralUtility::makeInstance(\Ppi\TemplaVoilaPlus\Domain\Repository\TemplateRepository::class);
        $list = $toRepo->getTemplateStoragePids();

        // Traverse the pages found and list in a table:
        $tRows = [];
        $tRows[] = '
            <thead>
                <th class="col-icon" nowrap="nowrap"></th>
                <th class="col-title" nowrap="nowrap">' . TemplaVoilaUtility::getLanguageService()->getLL('storagefolders', true) . '</th>
                <th>' . TemplaVoilaUtility::getLanguageService()->getLL('datastructures', true) . '</th>
                <th>' . TemplaVoilaUtility::getLanguageService()->getLL('templateobjects', true) . '</th>
            </thead>';

        if (is_array($list)) {
            foreach ($list as $pid) {
                $path = $this->findRecordsWhereUsed_pid($pid);
                if ($path) {
                    $editUrl = BackendUtility::getModuleUrl($this->moduleName, array('id' => $pid));
                    $tRows[] = '
                        <tr>
                            <td class="col-icon" nowrap="nowrap">'
                                . $this->iconFactory->getIconForRecord('pages', BackendUtility::getRecord('pages', $pid), Icon::SIZE_SMALL)->render()
                            . '</td>'
                            . '<td><a href="' . $editUrl . '" onclick="setHighlight(' . $pid . ')">'
                            . htmlspecialchars($path) . '</a></td>
                            <td>' . $dsRepo->getDatastructureCountForPid($pid) . '</td>
                            <td>' . $toRepo->getTemplateCountForPid($pid) . '</td>
                        </tr>';
                }
            }

            // Create overview
            $outputString = TemplaVoilaUtility::getLanguageService()->getLL('description_pagesWithCertainDsTo');
            $outputString .= '<br/>';
            $outputString .= '<table class="table table-striped table-hover">' . implode('', $tRows) . '</table>';

            // Add output:
            $this->content .= $outputString;
        }
    }

    /**
     * Renders module content main view:
     *
     * @return void
     */
    public function renderModuleContent_mainView()
    {
        // Traverse scopes of data structures display template records belonging to them:
        // Each scope is places in its own tab in the tab menu:
        $dsScopes = array(
            \Ppi\TemplaVoilaPlus\Domain\Model\AbstractDataStructure::SCOPE_PAGE,
            \Ppi\TemplaVoilaPlus\Domain\Model\AbstractDataStructure::SCOPE_FCE,
            \Ppi\TemplaVoilaPlus\Domain\Model\AbstractDataStructure::SCOPE_UNKNOWN
        );

        $toIdArray = $parts = array();
        foreach ($dsScopes as $scopePointer) {
            // Create listing for a DS:
            list($content, $dsCount, $toCount, $toIdArrayTmp) = $this->renderDSlisting($scopePointer);
            if ($dsCount > 0 || $toCount > 0) {
                $toIdArray = array_merge($toIdArrayTmp, $toIdArray);
                $scopeIcon = '';

                // Label for the tab:
                switch ((string) $scopePointer) {
                    case \Ppi\TemplaVoilaPlus\Domain\Model\AbstractDataStructure::SCOPE_PAGE:
                        $label = TemplaVoilaUtility::getLanguageService()->getLL('pagetemplates');
                        $scopeIcon = $this->iconFactory->getIconForRecord('pages', array(), Icon::SIZE_SMALL);
                        break;
                    case \Ppi\TemplaVoilaPlus\Domain\Model\AbstractDataStructure::SCOPE_FCE:
                        $label = TemplaVoilaUtility::getLanguageService()->getLL('fces');
                        $scopeIcon = $this->iconFactory->getIconForRecord('tt_content', array(), Icon::SIZE_SMALL);
                        break;
                    case \Ppi\TemplaVoilaPlus\Domain\Model\AbstractDataStructure::SCOPE_UNKNOWN:
                        $label = TemplaVoilaUtility::getLanguageService()->getLL('other');
                        $scopeIcon = $this->iconFactory->getIconForRecord('', array(), Icon::SIZE_SMALL);
                        break;
                    default:
                        $label = sprintf(TemplaVoilaUtility::getLanguageService()->getLL('unknown'), $scopePointer);
                        break;
                }

                // Error/Warning log:
                $errStat = $this->getErrorLog($scopePointer);

                // Add parts for Tab menu:
                $parts[] = [
                    'label' => $label,
                    'icon' => $scopeIcon,
                    'content' => $content,
                    'linkTitle' => 'DS/TO = ' . $dsCount . '/' . $toCount,
                    'stateIcon' => $errStat['iconCode'],
                ];
            }
        }

        // Find lost Template Objects and add them to a TAB if any are found:
        $lostTOs = '';
        $lostTOCount = 0;

        $toRepo = GeneralUtility::makeInstance(\Ppi\TemplaVoilaPlus\Domain\Repository\TemplateRepository::class);
        $toList = $toRepo->getAll($this->id);
        foreach ($toList as $toObj) {
            /** @var \Ppi\TemplaVoilaPlus\Domain\Model\Template $toObj */
            if (!in_array($toObj->getKey(), $toIdArray)) {
                $rTODres = $this->renderTODisplay($toObj, -1, 1);
                $lostTOs .= $rTODres['HTML'];
                $lostTOCount++;
            }
        }
        if ($lostTOs) {
            // Add parts for Tab menu:
            $parts[] = array(
                'label' => sprintf(TemplaVoilaUtility::getLanguageService()->getLL('losttos', true), $lostTOCount),
                'content' => $lostTOs
            );
        }

        // Complete Template File List
        $parts[] = array(
            'label' => TemplaVoilaUtility::getLanguageService()->getLL('templatefiles', true),
            'content' => $this->completeTemplateFileList()
        );

        // Errors:
        if (false !== ($errStat = $this->getErrorLog('_ALL'))) {
            $parts[] = array(
                'label' => 'Errors (' . $errStat['count'] . ')',
                'content' => $errStat['content'],
                'stateIcon' => $errStat['iconCode']
            );
        }

        // Add output:
        $this->content .= $this->moduleTemplate->getDynamicTabMenu($parts, 'TEMPLAVOILA:templateOverviewModule:' . $this->id, 1, 0, 300);
    }

    /**
     * Renders Data Structures from $dsScopeArray
     *
     * @param integer $scope
     *
     * @return array Returns array with three elements: 0: content, 1: number of DS shown, 2: number of root-level template objects shown.
     */
    public function renderDSlisting($scope)
    {
        $currentPid = (int)GeneralUtility::_GP('id');
        /** @var \Ppi\TemplaVoilaPlus\Domain\Repository\DataStructureRepository $dsRepo */
        $dsRepo = GeneralUtility::makeInstance(\Ppi\TemplaVoilaPlus\Domain\Repository\DataStructureRepository::class);
        /** @var \Ppi\TemplaVoilaPlus\Domain\Repository\TemplateRepository $toRepo */
        $toRepo = GeneralUtility::makeInstance(\Ppi\TemplaVoilaPlus\Domain\Repository\TemplateRepository::class);

        $dsList = $dsRepo->getDatastructuresByStoragePidAndScope($currentPid, $scope);

        $dsCount = 0;
        $toCount = 0;
        $content = '';
        $index = '';
        $toIdArray = array(-1);

        // Traverse data structures to list:
        if (count($dsList)) {
            foreach ($dsList as $dsObj) {
                /** @var \Ppi\TemplaVoilaPlus\Domain\Model\AbstractDataStructure $dsObj */

                // Traverse template objects which are not children of anything:
                $TOcontent = '';
                $indexTO = '';

                $toList = $toRepo->getTemplatesByDatastructure($dsObj, $currentPid);

                $newPid = (int)GeneralUtility::_GP('id');
                $newFileRef = '';
                $newTitle = $dsObj->getLabel() . ' [TEMPLATE]';
                if (count($toList)) {
                    foreach ($toList as $toObj) {
                        /** @var \Ppi\TemplaVoilaPlus\Domain\Model\Template $toObj */
                        $toIdArray[] = $toObj->getKey();
                        if ($toObj->hasParentTemplate()) {
                            continue;
                        }
                        $rTODres = $this->renderTODisplay($toObj, $scope);
                        $TOcontent .= '<a name="to-' . $toObj->getKey() . '"></a>' . $rTODres['HTML'];
                        $indexTO .= '
                            <tr>
                                <td></td>
                                <td>&nbsp;&nbsp;&nbsp;</td>
                                <td><a href="#to-' . $toObj->getKey() . '">' . htmlspecialchars($toObj->getLabel()) . $toObj->hasParentTemplate() . '</a></td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td align="center">' . $rTODres['mappingStatus'] . '</td>
                                <td align="center">' . $rTODres['usage'] . '</td>
                            </tr>';
                        $toCount++;

                        $newPid = -$toObj->getKey();
                        $newFileRef = $toObj->getFileref();
                        $newTitle = $toObj->getLabel() . ' [ALT]';
                    }
                }
                // New-TO link:
                $TOcontent .= '<a href="#" class="btn btn-default" onclick="'
                    . htmlspecialchars(
                        BackendUtility::editOnClick(
                            '&edit[tx_templavoilaplus_tmplobj][' . $newPid . ']=new'
                            . '&defVals[tx_templavoilaplus_tmplobj][datastructure]=' . rawurlencode($dsObj->getKey())
                            . '&defVals[tx_templavoilaplus_tmplobj][title]=' . rawurlencode($newTitle)
                            . '&defVals[tx_templavoilaplus_tmplobj][fileref]=' . rawurlencode($newFileRef)
                        )
                    )
                    . '">' . $this->iconFactory->getIcon('actions-document-new', Icon::SIZE_SMALL)->render() . ' '
                    . TemplaVoilaUtility::getLanguageService()->getLL('createnewto', true)
                    . '</a>';

                // Render data structure display
                $rDSDres = $this->renderDataStructureDisplay($dsObj, $scope, $toIdArray);
                $content .= '<a name="ds-' . md5($dsObj->getKey()) . '"></a>' . $rDSDres['HTML'];
                $index .= '
                    <tr class="active">
                        <td></td>
                        <td colspan="2"><a href="#ds-' . md5($dsObj->getKey()) . '">' . htmlspecialchars($dsObj->getLabel()) . '</a></td>
                        <td align="center">' . $rDSDres['languageMode'] . '</td>
                        <td>' . $rDSDres['container'] . '</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';
                if ($indexTO) {
                    $index .= $indexTO;
                }
                $dsCount++;

                // Wrap TO elements in a div-tag and add to content:
                if ($TOcontent) {
                    $content .= '<div style="margin: 0 0 5px 102px;">' . $TOcontent . '</div>';
                }
            }
        }

        $content = '
            <table class="table table-hover">
                <thead>
                    <th class="col-icon" nowrap="nowrap"></th>
                    <th class="col-title" nowrap="nowrap" colspan="2">' . TemplaVoilaUtility::getLanguageService()->getLL('dstotitle', true) . '</td>
                    <th>' . TemplaVoilaUtility::getLanguageService()->getLL('localization', true) . '</th>
                    <th>' . TemplaVoilaUtility::getLanguageService()->getLL('containerstatus', true) . '</th>
                    <th>' . TemplaVoilaUtility::getLanguageService()->getLL('mappingstatus', true) . '</th>
                    <th>' . TemplaVoilaUtility::getLanguageService()->getLL('usagecount', true) . '</th>
                </thead>
            ' . $index . '
            </table>'
            . $content;

        return array($content, $dsCount, $toCount, $toIdArray);
    }

    /**
     * Rendering a single data structures information
     *
     * @param \Ppi\TemplaVoilaPlus\Domain\Model\AbstractDataStructure $dsObj Structure information
     * @param integer $scope Scope.
     * @param array $toIdArray
     *
     * @return string HTML content
     */
    public function renderDataStructureDisplay(\Ppi\TemplaVoilaPlus\Domain\Model\AbstractDataStructure $dsObj, $scope, $toIdArray)
    {
        $XMLinfo = $this->DSdetails($dsObj->getDataprotXML());

        if ($dsObj->isFilebased()) {
            $overlay = 'overlay-edit';
            $fileName = GeneralUtility::getFileAbsFileName($dsObj->getKey());
            $editUrl = BackendUtility::getModuleUrl(
                'file_edit',
                [
                    'target' => $fileName,
                    // Edit file do not support returnUrl anymore
                    // 'returnUrl' => GeneralUtility::sanitizeLocalUrl(GeneralUtility::getIndpEnv('REQUEST_URI')),
                ]
            );
            if (!is_file($fileName)) {
                $overlay = 'overlay-missing';
            } elseif (!is_writable($fileName)) {
                $overlay = 'overlay-locked';
            }
            $dsIcon = '<a href="' . htmlspecialchars($editUrl) . '">' . $this->iconFactory->getIconForFileExtension('xml', Icon::SIZE_SMALL, $overlay)->render() . '</a>';
        } else {
            $dsIcon = $this->iconFactory->getIconForRecord('tx_templavoilaplus_datastructure', [], Icon::SIZE_SMALL)->render();
            $dsIcon = BackendUtility::wrapClickMenuOnIcon($dsIcon, 'tx_templavoilaplus_datastructure', $dsObj->getKey(), true);
        }

        // Preview icon:
        if ($dsObj->getIcon()) {
            $previewIcon = '<img src="' . $this->getThumbnail(realpath($dsObj->getIcon())) . '" alt="" />';
        } else {
            $previewIcon = TemplaVoilaUtility::getLanguageService()->getLL('noicon', true);
        }

        // Links:
        $lpXML = '';
        if ($dsObj->isFilebased()) {
            $editLink = '';
            $dsTitle = $dsObj->getLabel();
        } else {
            $editLink = $lpXML .= '<a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick('&edit[tx_templavoilaplus_datastructure][' . $dsObj->getKey() . ']=edit')) . '">'
            . $this->iconFactory->getIcon('actions-document-open', Icon::SIZE_SMALL)->render()
            . '</a>';

            // Mapping link:
            $uriParameters = [
                'table' => 'tx_templavoilaplus_datastructure',
                'uid' => $dsObj->getKey(),
                'id' => $this->id,
                'returnUrl' => $this->getBaseUrl(),
            ];

            $dsTitle = '<a href="' . BackendUtility::getModuleUrl('templavoilaplus_mapping', $uriParameters) . '">'
                . htmlspecialchars($dsObj->getLabel())
                . '</a>';
        }
        // Compile info table:
        $content = '
        <table class="table table-hover" style="margin-bottom: 0px">
            <thead>
                <th class="col-icon">'
                .  $dsIcon
                . '</th>
                <th class="col-title" colspan="3">'
                . $dsTitle . $editLink
            . '</th>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td>' . TemplaVoilaUtility::getLanguageService()->getLL('globalprocessing_xml') . '</td>
                    <td>
                        ' . $lpXML .  GeneralUtility::formatSize(strlen($dsObj->getDataprotXML())) . ' bytes
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>' . TemplaVoilaUtility::getLanguageService()->getLL('created', true) . '</td>
                    <td>' . BackendUtility::datetime($dsObj->getCrdate()) . ' ' . TemplaVoilaUtility::getLanguageService()->getLL('byuser', true) . ' [' . $dsObj->getCruser() . ']</td>
                </tr>
                <tr>
                    <td></td>
                    <td>' . TemplaVoilaUtility::getLanguageService()->getLL('updated', true) . '</td>
                    <td>' . BackendUtility::datetime($dsObj->getTstamp()) . '</td>
                </tr>
                ' . ($previewIcon ?
		        '<tr>
                    <td></td>
                    <td>' . TemplaVoilaUtility::getLanguageService()->getLL('preview', true) . '</td>
                    <td>' . $previewIcon . '</td>
                </tr>' : '') . '
            </tbody>
        </table>';

        // Format XML if requested (renders VERY VERY slow)
//         if ($this->MOD_SETTINGS['set_showDSxml']) {
//             if ($dsObj->getDataprotXML()) {
//                 $hlObj = GeneralUtility::makeInstance(\Ppi\TemplaVoilaPlus\Service\SyntaxHighlightingService::class);
//                 $content .= '<pre>' . str_replace(chr(9), '&nbsp;&nbsp;&nbsp;', $hlObj->highLight_DS($dsObj->getDataprotXML())) . '</pre>';
//             }
//         }

        $containerMode = '';
        if ($XMLinfo['referenceFields']) {
            $containerMode = TemplaVoilaUtility::getLanguageService()->getLL('yes', true);
            if ($XMLinfo['languageMode'] === 'Separate') {
                $containerMode .= ' ' . $this->moduleTemplate->icons(3)
                    . TemplaVoilaUtility::getLanguageService()->getLL('containerwithseparatelocalization', true);
            } elseif ($XMLinfo['languageMode'] === 'Inheritance') {
                $containerMode .= ' ' . $this->moduleTemplate->icons(2);
                if ($XMLinfo['inputFields']) {
                    $containerMode .= TemplaVoilaUtility::getLanguageService()->getLL('mixofcontentandref', true);
                } else {
                    $containerMode .= TemplaVoilaUtility::getLanguageService()->getLL('nocontentfields', true);
                }
            }
        } else {
            $containerMode = 'No';
        }

        $containerMode .= ' <br/>(ARI=' . $XMLinfo['rootelements'] . '/' . $XMLinfo['referenceFields'] . '/' . $XMLinfo['inputFields'] . ')';

        // Return content
        return array(
            'HTML' => $content,
            'languageMode' => $XMLinfo['languageMode'],
            'container' => $containerMode
        );
    }

    /**
     * Render display of a Template Object
     *
     * @param \Ppi\TemplaVoilaPlus\Domain\Model\Template $toObj Template Object record to render
     * @param integer $scope Scope of DS
     * @param integer $children If set, the function is asked to render children to template objects (and should not call it self recursively again).
     *
     * @return string HTML content
     */
    public function renderTODisplay($toObj, $scope, $children = 0)
    {
        // Put together the records icon including content sensitive menu link wrapped around it:
        $recordIcon = $this->iconFactory->getIconForRecord('tx_templavoilaplus_tmplobj', [], Icon::SIZE_SMALL)->render();
        $recordIcon = BackendUtility::wrapClickMenuOnIcon($recordIcon, 'tx_templavoilaplus_tmplobj', $toObj->getKey(), true);

        // Preview icon:
        if ($toObj->getIcon()) {
            $icon = '<img src="/' . $this->getThumbnail($toObj->getIcon()) . '" alt="" />';
        } else {
            $icon = TemplaVoilaUtility::getLanguageService()->getLL('noicon', true);
        }

        // Mapping status / link:
        $uriParameters = [
            'table' => 'tx_templavoilaplus_tmplobj',
            '_reload_from' => 1,
            'uid' => $toObj->getKey(),
            'id' => $this->id,
            'returnUrl' => $this->getBaseUrl(),
        ];
        $linkUrl = BackendUtility::getModuleUrl('templavoilaplus_mapping', $uriParameters);

        $fileReference = GeneralUtility::getFileAbsFileName($toObj->getFileref());
        if (@is_file($fileReference)) {
            $this->tFileList[$fileReference]++;
            $fileRef = '<a href="' . htmlspecialchars(substr($fileReference, strlen(PATH_site))) . '" target="_blank">' . htmlspecialchars($toObj->getFileref()) . '</a>';
            $fileMsg = '';
            $fileMtime = filemtime($fileReference);
        } else {
            $fileRef = htmlspecialchars($toObj->getFileref());
            $fileMsg = '<div class="typo3-red">ERROR: File not found</div>';
            $fileMtime = 0;
        }

        $mappingStatus_index = '';
        if ($fileMtime && $toObj->getFilerefMtime()) {
            if ($toObj->getFilerefMD5() != '') {
                $modified = (@md5_file($fileReference) != $toObj->getFilerefMD5());
            } else {
                $modified = ($toObj->getFilerefMtime() != $fileMtime);
            }
            if ($modified) {
                $mappingStatus = $mappingStatus_index = $this->iconFactory->getIcon('status-dialog-warning', Icon::SIZE_SMALL)->render();
                $mappingStatus .= sprintf(TemplaVoilaUtility::getLanguageService()->getLL('towasupdated', true), BackendUtility::datetime($toObj->getTstamp()));
                $this->setErrorLog($scope, 'warning', sprintf(TemplaVoilaUtility::getLanguageService()->getLL('warning_mappingstatus', true), $mappingStatus, $toObj->getLabel()));
            } else {
                $mappingStatus = $mappingStatus_index = $this->iconFactory->getIcon('status-dialog-ok', Icon::SIZE_SMALL)->render();
                $mappingStatus .= TemplaVoilaUtility::getLanguageService()->getLL('mapping_uptodate', true);
            }
            $mappingStatus .= '<br/>';
            $mappingStateLL = TemplaVoilaUtility::getLanguageService()->getLL('update_mapping', true);
        } elseif (!$fileMtime) {
            $mappingStatus = $mappingStatus_index = $this->iconFactory->getIcon('status-dialog-error', Icon::SIZE_SMALL)->render();
            $mappingStatus .= TemplaVoilaUtility::getLanguageService()->getLL('notmapped', true);
            $this->setErrorLog($scope, 'fatal', sprintf(TemplaVoilaUtility::getLanguageService()->getLL('warning_mappingstatus', true), $mappingStatus, $toObj->getLabel()));

            $mappingStatus .= TemplaVoilaUtility::getLanguageService()->getLL('updatemapping_info');
            $mappingStatus .= '<br/>';
            $mappingStateLL = TemplaVoilaUtility::getLanguageService()->getLL('map', true);
        } else {
            $mappingStatus = '';
            $mappingStatus .= '<input type="button" onclick="jumpToUrl(\'' . htmlspecialchars($linkUrl) . '\');" value="' . TemplaVoilaUtility::getLanguageService()->getLL('remap', true) . '" />';
            $mappingStatus .= '&nbsp;';
            $mappingStateLL = TemplaVoilaUtility::getLanguageService()->getLL('preview', true);
        }
        $mappingStatus .= '<span class="btn btn-info" onclick="jumpToUrl(\'' . htmlspecialchars($linkUrl) . '\');">'
            . '<i class="fa fa-pencil" aria-hidden="true"></i> ' . $mappingStateLL . '</span>';

//         $XMLinfo = $this->DSdetails($toObj->getLocalDataprotXML(true));

        // Format XML if requested
        $lpXML = '';
//         if ($toObj->getLocalDataprotXML(true)) {
//             $hlObj = GeneralUtility::makeInstance(\Ppi\TemplaVoilaPlus\Service\SyntaxHighlightingService::class);
//             $lpXML = '<pre>' . str_replace(chr(9), '&nbsp;&nbsp;&nbsp;', $hlObj->highLight_DS($toObj->getLocalDataprotXML(true))) . '</pre>';
//         }

        $lpXML .= '<a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick('&edit[tx_templavoilaplus_tmplobj][' . $toObj->getKey() . ']=edit&columnsOnly=localprocessing')) . '">'
        . $this->iconFactory->getIcon('actions-document-open', Icon::SIZE_SMALL)->render()
        . '</a>';

        // Links:
        $toTitle = '<a href="' . htmlspecialchars($linkUrl) . '">' . htmlspecialchars(TemplaVoilaUtility::getLanguageService()->sL($toObj->getLabel())) . '</a>';
        $editLink = '<a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick('&edit[tx_templavoilaplus_tmplobj][' . $toObj->getKey() . ']=edit')) . '">'
        . $this->iconFactory->getIcon('actions-document-open', Icon::SIZE_SMALL)->render()
        . '</a>';

        $fRWTOUres = array();

        if (!$children) {
            $count = $this->countRecordsWhereTOUsed($toObj, $scope);

            $content = '
            <table class="table table-hover" style="margin-bottom:5px;">
                <thead>
                    <th colspan="3">'
                    .  $recordIcon . ' ' . $toTitle . ' ' . $editLink
                    . '</th>
                </thead>
                <tr>
                    <td rowspan="5" style="width:100px">' . $icon . '</td>
                </tr>
                <tr>
                    <td style="width:200px;">' . TemplaVoilaUtility::getLanguageService()->getLL('filereference', true) . ':</td>
                    <td>' . $fileRef . $fileMsg . '</td>
                </tr>
                <tr>
                    <td>' . TemplaVoilaUtility::getLanguageService()->getLL('description', true) . ':</td>
                    <td>' . htmlspecialchars($toObj->getDescription()) . '</td>
                </tr>
                <tr>
                    <td>' . TemplaVoilaUtility::getLanguageService()->getLL('mappingstatus', true) . ':</td>
                    <td>' . $mappingStatus . '</td>
                </tr>
                <tr>
                    <td>' . TemplaVoilaUtility::getLanguageService()->getLL('localprocessing_xml') . ':</td>
                    <td>
                        ' . $lpXML . ($toObj->getLocalProcessing() ?
                    GeneralUtility::formatSize(strlen($toObj->getLocalProcessing())) . ' bytes'
                    : '') . '
                    </td>
                </tr>
            </table>';
        } else {
            $content = '
            <table class="table table-hover" style="margin-bottom:5px;">
                <thead>
                    <th colspan="3">'
                    .  $recordIcon . ' ' . $toTitle . ' ' . $editLink
                    . '</th>
                </thead>
                <tr>
                    <td style="width:200px;">' . TemplaVoilaUtility::getLanguageService()->getLL('filereference', true) . ':</td>
                    <td>' . $fileRef . $fileMsg . '</td>
                </tr>
                <tr>
                    <td>' . TemplaVoilaUtility::getLanguageService()->getLL('mappingstatus', true) . ':</td>
                    <td>' . $mappingStatus . '</td>
                </tr>
                <tr>
                    <td>' . TemplaVoilaUtility::getLanguageService()->getLL('rendertype', true) . ':</td>
                    <td>' . $this->getProcessedValue('tx_templavoilaplus_tmplobj', 'rendertype', $toObj->getRendertype()) . '</td>
                </tr>
                <tr>
                    <td>' . TemplaVoilaUtility::getLanguageService()->getLL('language', true) . ':</td>
                    <td>' . $this->getProcessedValue('tx_templavoilaplus_tmplobj', 'sys_language_uid', $toObj->getSyslang()) . '</td>
                </tr>
                <tr>
                    <td>' . TemplaVoilaUtility::getLanguageService()->getLL('localprocessing_xml') . ':</td>
                    <td>' . $lpXML . ($toObj->getLocalProcessing() ?
                    GeneralUtility::formatSize(strlen($toObj->getLocalProcessing())) . ' bytes'
                    : '') . '
                    </td>
                </tr>
            </table>';
        }

        // Traverse template objects which are not children of anything:
        $toRepo = GeneralUtility::makeInstance(\Ppi\TemplaVoilaPlus\Domain\Repository\TemplateRepository::class);
        $toChildren = $toRepo->getTemplatesByParentTemplate($toObj);

        if (!$children && count($toChildren)) {
            $TOchildrenContent = '';
            foreach ($toChildren as $toChild) {
                $rTODres = $this->renderTODisplay($toChild, $scope, 1);
                $TOchildrenContent .= $rTODres['HTML'];
            }
            $content .= '<div style="margin-left: 102px;">' . $TOchildrenContent . '</div>';
        }

        // Return content
        return array('HTML' => $content, 'mappingStatus' => $mappingStatus_index, 'usage' => $count);
    }

    /**
     * Creates listings of pages / content elements where template objects are used.
     *
     * @param \Ppi\TemplaVoilaPlus\Domain\Model\Template $toObj Template Object record
     * @param integer $scope Scope value. 1) page,  2) content elements
     *
     * @return string HTML table listing usages.
     */
    public function countRecordsWhereTOUsed($toObj, $scope)
    {
        $count = 0;

        switch ($scope) {
            case 1: // PAGES:
                $dsKey = $toObj->getDatastructure()->getKey();
                $count = $this->databaseConnection->exec_SELECTcountRows(
                    'uid',
                    'pages',
                    '(
                        (tx_templavoilaplus_to=' . (int)$toObj->getKey() . ' AND tx_templavoilaplus_ds=' . $this->databaseConnection->fullQuoteStr($dsKey, 'pages') . ') OR
                        (tx_templavoilaplus_next_to=' . (int)$toObj->getKey() . ' AND tx_templavoilaplus_next_ds=' . $this->databaseConnection->fullQuoteStr($dsKey, 'pages') . ')
                    )' .
                    BackendUtility::deleteClause('pages')
                );
                break;
            case 2:
                $count = $this->databaseConnection->exec_SELECTcountRows(
                    'uid',
                    'tt_content',
                    'CType=' . $this->databaseConnection->fullQuoteStr('templavoilaplus_pi1', 'tt_content') .
                    ' AND tx_templavoilaplus_to=' . (int)$toObj->getKey() .
                    ' AND tx_templavoilaplus_ds=' . $this->databaseConnection->fullQuoteStr($toObj->getDatastructure()->getKey(), 'tt_content') .
                    BackendUtility::deleteClause('tt_content'),
                    '',
                    'pid'
                );
                break;
        }

        return $count;
    }

    /**
     * Creates listings of pages / content elements where NO or WRONG template objects are used.
     * @TODO Maybe Move away to an error/analytics tool
     * At the moment unused code.
     *
     * @param \Ppi\TemplaVoilaPlus\Domain\Model\AbstractDataStructure $dsObj Data Structure ID
     * @param integer $scope Scope value. 1) page,  2) content elements
     * @param array $toIdArray Array with numerical toIDs. Must be integers and never be empty. You can always put in "-1" as dummy element.
     *
     * @return string HTML table listing usages.
     */
    public function findDSUsageWithImproperTOs($dsObj, $scope, $toIdArray)
    {
        $output = array();

        switch ($scope) {
            case 1: //
                // Header:
                $output[] = '
                            <tr class="bgColor5 tableheader">
                                <td>' . TemplaVoilaUtility::getLanguageService()->getLL('toused_title', true) . ':</td>
                                <td>' . TemplaVoilaUtility::getLanguageService()->getLL('toused_path', true) . ':</td>
                            </tr>';

                // Main templates:
                $res = $this->databaseConnection->exec_SELECTquery(
                    'uid,title,pid',
                    'pages',
                    '(
                        (tx_templavoilaplus_to NOT IN (' . implode(',', $toIdArray) . ') AND tx_templavoilaplus_ds=' . $this->databaseConnection->fullQuoteStr($dsObj->getKey(), 'pages') . ') OR
                        (tx_templavoilaplus_next_to NOT IN (' . implode(',', $toIdArray) . ') AND tx_templavoilaplus_next_ds=' . $this->databaseConnection->fullQuoteStr($dsObj->getKey(), 'pages') . ')
                    )' .
                    BackendUtility::deleteClause('pages')
                );

                while (false !== ($pRow = $this->databaseConnection->sql_fetch_assoc($res))) {
                    $path = $this->findRecordsWhereUsed_pid($pRow['uid']);
                    if ($path) {
                        $output[] = '
                            <tr class="bgColor4-20">
                                <td nowrap="nowrap">' .
                            '<a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick('&edit[pages][' . $pRow['uid'] . ']=edit')) . '">' .
                            htmlspecialchars($pRow['title']) .
                            '</a></td>
                        <td nowrap="nowrap">' .
                            '<a href="#" onclick="' . htmlspecialchars(BackendUtility::viewOnClick($pRow['uid']) . 'return false;') . '">' .
                            htmlspecialchars($path) .
                            '</a></td>
                    </tr>';
                    } else {
                        $output[] = '
                            <tr class="bgColor4-20">
                                <td><em>' . TemplaVoilaUtility::getLanguageService()->getLL('noaccess', true) . '</em></td>
                                <td>-</td>
                            </tr>';
                    }
                }
                $this->databaseConnection->sql_free_result($res);
                break;
            case 2:
                // Select Flexible Content Elements:
                $res = $this->databaseConnection->exec_SELECTquery(
                    'uid,header,pid',
                    'tt_content',
                    'CType=' . $this->databaseConnection->fullQuoteStr('templavoilaplus_pi1', 'tt_content') .
                    ' AND tx_templavoilaplus_to NOT IN (' . implode(',', $toIdArray) . ')' .
                    ' AND tx_templavoilaplus_ds=' . $this->databaseConnection->fullQuoteStr($dsObj->getKey(), 'tt_content') .
                    BackendUtility::deleteClause('tt_content'),
                    '',
                    'pid'
                );

                // Header:
                $output[] = '
                            <tr class="bgColor5 tableheader">
                                <td>' . TemplaVoilaUtility::getLanguageService()->getLL('toused_header', true) . ':</td>
                                <td>' . TemplaVoilaUtility::getLanguageService()->getLL('toused_path', true) . ':</td>
                            </tr>';

                // Elements:
                while (false !== ($pRow = $this->databaseConnection->sql_fetch_assoc($res))) {
                    $path = $this->findRecordsWhereUsed_pid($pRow['pid']);
                    if ($path) {
                        $output[] = '
                            <tr class="bgColor4-20">
                                <td nowrap="nowrap">' .
                            '<a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick('&edit[tt_content][' . $pRow['uid'] . ']=edit')) . '" title="Edit">' .
                            htmlspecialchars($pRow['header']) .
                            '</a></td>
                        <td nowrap="nowrap">' .
                            '<a href="#" onclick="' . htmlspecialchars(BackendUtility::viewOnClick($pRow['pid']) . 'return false;') . '" title="View page">' .
                            htmlspecialchars($path) .
                            '</a></td>
                    </tr>';
                    } else {
                        $output[] = '
                            <tr class="bgColor4-20">
                                <td><em>' . TemplaVoilaUtility::getLanguageService()->getLL('noaccess', true) . '</em></td>
                                <td>-</td>
                            </tr>';
                    }
                }
                $this->databaseConnection->sql_free_result($res);
                break;
        }

        // Create final output table:
        $outputString = '';
        if (count($output)) {
            if (count($output) > 1) {
                $outputString = $this->iconFactory->getIcon('status-dialog-error', Icon::SIZE_SMALL)->render() .
                    sprintf(TemplaVoilaUtility::getLanguageService()->getLL('invalidtemplatevalues', true), count($output) - 1);
                $this->setErrorLog($scope, 'fatal', $outputString);

                $outputString .= '<table border="0" cellspacing="1" cellpadding="1" class="lrPadding">' . implode('', $output) . '</table>';
            } else {
                $outputString = $this->iconFactory->getIcon('status-dialog-ok', Icon::SIZE_SMALL)->render() .
                    TemplaVoilaUtility::getLanguageService()->getLL('noerrorsfound', true);
            }
        }

        return $outputString;
    }

    /**
     * Checks if a PID value is accessible and if so returns the path for the page.
     * Processing is cached so many calls to the function are OK.
     *
     * @param integer $pid Page id for check
     *
     * @return string Page path of PID if accessible. otherwise zero.
     */
    public function findRecordsWhereUsed_pid($pid)
    {
        if (!isset($this->pidCache[$pid])) {
            $this->pidCache[$pid] = array();

            $pageinfo = BackendUtility::readPageAccess($pid, $this->perms_clause);
            $this->pidCache[$pid]['path'] = $pageinfo['_thePath'];
        }

        return $this->pidCache[$pid]['path'];
    }

    /**
     * Creates a list of all template files used in TOs
     *
     * @return string HTML table
     */
    public function completeTemplateFileList()
    {
        $output = '';
        if (is_array($this->tFileList)) {
            $output = '';

            // Mapping link:
            $uriParameters = [
                'id' => $this->id,
                'mapElPath' => '<ROOT>',
                'returnUrl' => $this->getBaseUrl(),
            ];

            // USED FILES:
            $tRows = array();
            $tRows[] = '
                <thead>
                    <th class="col-icon" nowrap="nowrap"></th>
                    <th class="col-title" nowrap="nowrap">' . TemplaVoilaUtility::getLanguageService()->getLL('file', true) . '</th>
                    <th align="center">' . TemplaVoilaUtility::getLanguageService()->getLL('usagecount', true) . '</th>
                    <th>' . TemplaVoilaUtility::getLanguageService()->getLL('newdsto', true) . '</th>
                </thead>';

            $i = 0;
            foreach ($this->tFileList as $tFile => $count) {
                $uriParameters['file'] = $tFile;
                BackendUtility::getModuleUrl('templavoilaplus_mapping', $uriParameters);
                $tRows[] = '
                    <tr>
                        <td>' . $this->iconFactory->getIcon('actions-document-view', Icon::SIZE_SMALL)->render() . '</td>
                        <td>'
                        . '<a href="' . htmlspecialchars(substr($tFile, strlen(PATH_site))) . '" target="_blank">'
                        . htmlspecialchars(substr($tFile, strlen(PATH_site)))
                        . '</a></td>
                        <td align="center">' . $count . '</td>
                        <td>'
                        . '<a href="' . BackendUtility::getModuleUrl('templavoilaplus_mapping', $uriParameters) . '">'
                        . $this->iconFactory->getIcon('actions-document-new', Icon::SIZE_SMALL)->render()
                        . ' ' . htmlspecialchars('Create...')
                        . '</a></td>
                    </tr>';
            }

            if (count($tRows) > 1) {
                $output .= '
                <h3>' . TemplaVoilaUtility::getLanguageService()->getLL('usedfiles', true) . ':</h3>
                <table class="table table-striped table-hover">
                    ' . implode('', $tRows) . '
                </table>
                ';
            }

            $files = $this->getTemplateFiles();

            // TEMPLATE ARCHIVE:
            if (count($files)) {
                $tRows = array();
                $tRows[] = '
                    <thead>
                        <th class="col-icon" nowrap="nowrap"></th>
                        <th class="col-title" nowrap="nowrap">' . TemplaVoilaUtility::getLanguageService()->getLL('file', true) . '</th>
                        <th align="center">' . TemplaVoilaUtility::getLanguageService()->getLL('usagecount', true) . '</th>
                        <th>' . TemplaVoilaUtility::getLanguageService()->getLL('newdsto', true) . '</th>
                    </thead>';

                $i = 0;
                foreach ($files as $file) {
                    // @TODO Only wortks in local storage!
                    $fullPath = $file->getForLocalProcessing(false);
                    $uriParameters['file'] = $fullPath;
                    $tRows[] = '
                        <tr>
                            <td>' . $this->iconFactory->getIcon('actions-document-view', Icon::SIZE_SMALL)->render() . '</td>
                            <td>'
                            . '<a href="/' . htmlspecialchars($file->getPublicUrl()) . '" target="_blank">'
                            . htmlspecialchars($file->getPublicUrl())
                            . '</a></td>
                            <td align="center">' . (isset($this->tFileList[$fullPath]) ? $this->tFileList[$fullPath] : '-') . '</td>
                            <td>'
                            . '<a href="' . BackendUtility::getModuleUrl('templavoilaplus_mapping', $uriParameters) . '">'
                            . $this->iconFactory->getIcon('actions-document-new', Icon::SIZE_SMALL)->render()
                            . ' ' . htmlspecialchars('Create...')
                            . '</a></td>
                        </tr>';
                }

                if (count($tRows) > 1) {
                    $output .= '
                        <h3>' . TemplaVoilaUtility::getLanguageService()->getLL('templatearchive', true) . ':</h3>
                        <table class="table table-striped table-hover">' . implode('', $tRows) . '</table>';
                }
            }
        }

        return $output;
    }

    /**
     * Get the processed value analog to BackendUtility::getProcessedValue
     * but take additional TSconfig values into account
     *
     * @param string $table
     * @param string $typeField
     * @param string $typeValue
     *
     * @return string
     */
    protected function getProcessedValue($table, $typeField, $typeValue)
    {
        $value = BackendUtility::getProcessedValue($table, $typeField, $typeValue);
        if (!$value) {
            $TSConfig = BackendUtility::getPagesTSconfig($this->id);
            if (isset($TSConfig['TCEFORM.'][$table . '.'][$typeField . '.']['addItems.'][$typeValue])) {
                $value = $TSConfig['TCEFORM.'][$table . '.'][$typeField . '.']['addItems.'][$typeValue];
            }
        }

        return $value;
    }

    /**
     * Stores errors/warnings inside the class.
     *
     * @param string $scope Scope string, 1=page, 2=ce, _ALL= all errors
     * @param string $type "fatal" or "warning"
     * @param string $HTML HTML content for the error.
     *
     * @return void
     * @see getErrorLog()
     */
    public function setErrorLog($scope, $type, $HTML)
    {
        $this->errorsWarnings['_ALL'][$type][] = $this->errorsWarnings[$scope][$type][] = $HTML;
    }

    /**
     * Returns status for a single scope
     *
     * @param string $scope Scope string
     *
     * @return array Array with content
     * @see setErrorLog()
     */
    public function getErrorLog($scope)
    {
        $errStat = false;
        if (is_array($this->errorsWarnings[$scope])) {
            $errStat = array();

            if (is_array($this->errorsWarnings[$scope]['warning'])) {
                $errStat['count'] = count($this->errorsWarnings[$scope]['warning']);
                $errStat['content'] = '<h3>' . TemplaVoilaUtility::getLanguageService()->getLL('warnings', true) . '</h3>' . implode('<hr/>', $this->errorsWarnings[$scope]['warning']);
                $errStat['iconCode'] = 2;
            }

            if (is_array($this->errorsWarnings[$scope]['fatal'])) {
                $errStat['count'] = count($this->errorsWarnings[$scope]['fatal']) . ($errStat['count'] ? '/' . $errStat['count'] : '');
                $errStat['content'] .= '<h3>' . TemplaVoilaUtility::getLanguageService()->getLL('fatalerrors', true) . '</h3>' . implode('<hr/>', $this->errorsWarnings[$scope]['fatal']);
                $errStat['iconCode'] = 3;
            }
        }

        return $errStat;
    }

    /**
     * Shows a graphical summary of a array-tree, which suppose was a XML
     * (but don't need to). This function works recursively.
     *
     * @param array $DStree an array holding the DSs defined structure
     *
     * @return string HTML showing an overview of the DS-structure
     */
    public function renderDSdetails($DStree)
    {
        $HTML = '';

        if (is_array($DStree) && (count($DStree) > 0)) {
            $HTML .= '<dl class="DS-details">';

            foreach ($DStree as $elm => $def) {
                if (!is_array($def)) {
                    $HTML .= '<p>' . $this->iconFactory->getIcon('status-dialog-error', Icon::SIZE_SMALL)->render()
                        . sprintf(TemplaVoilaUtility::getLanguageService()->getLL('invaliddatastructure_xmlbroken', true), $elm) . '</p>';
                    break;
                }

                $HTML .= '<dt>';
                $HTML .= ($elm == "meta" ? TemplaVoilaUtility::getLanguageService()->getLL('configuration', true) : $def['tx_templavoilaplus']['title'] . ' (' . $elm . ')');
                $HTML .= '</dt>';
                $HTML .= '<dd>';

                /* this is the configuration-entry ------------------------------ */
                if ($elm == "meta") {
                    /* The basic XML-structure of an meta-entry is:
                     *
                     * <meta>
                     *     <langDisable>        -> no localization
                     *     <langChildren>        -> no localization for children
                     *     <sheetSelector>        -> a php-function for selecting "sDef"
                     * </meta>
                     */

                    /* it would also be possible to use the 'list-style-image'-property
                     * for the flags, which would be more sensible to IE-bugs though
                     */
                    $conf = '';
                    if (isset($def['langDisable'])) {
                        $conf .= '<li>' .
                            (($def['langDisable'] == 1)
                                ? $this->iconFactory->getIcon('status-dialog-error', Icon::SIZE_SMALL)->render()
                                : $this->iconFactory->getIcon('status-dialog-ok', Icon::SIZE_SMALL)->render()
                            ) . ' ' . TemplaVoilaUtility::getLanguageService()->getLL('fceislocalized', true) . '</li>';
                    }
                    if (isset($def['langChildren'])) {
                        $conf .= '<li>' .
                            (($def['langChildren'] == 1)
                                ? $this->iconFactory->getIcon('status-dialog-ok', Icon::SIZE_SMALL)->render()
                                : $this->iconFactory->getIcon('status-dialog-error', Icon::SIZE_SMALL)->render()
                            ) . ' ' . TemplaVoilaUtility::getLanguageService()->getLL('fceinlineislocalized', true) . '</li>';
                    }
                    if (isset($def['sheetSelector'])) {
                        $conf .= '<li>' .
                            (($def['sheetSelector'] != '')
                                ? $this->iconFactory->getIcon('status-dialog-ok', Icon::SIZE_SMALL)->render()
                                : $this->iconFactory->getIcon('status-dialog-error', Icon::SIZE_SMALL)->render()
                            ) . ' custom sheet-selector' .
                            (($def['sheetSelector'] != '')
                                ? ' [<em>' . $def['sheetSelector'] . '</em>]'
                                : ''
                            ) . '</li>';
                    }

                    if ($conf != '') {
                        $HTML .= '<ul class="DS-config">' . $conf . '</ul>';
                    }
                } /* this a container for repetitive elements --------------------- */
                elseif (isset($def['section']) && ($def['section'] == 1)) {
                    $HTML .= '<p>[..., ..., ...]</p>';
                } /* this a container for cellections of elements ----------------- */
                else {
                    if (isset($def['type']) && ($def['type'] == "array")) {
                        $HTML .= '<p>[...]</p>';
                    } /* this a regular entry ----------------------------------------- */
                    else {
                        $tco = true;
                        /* The basic XML-structure of an entry is:
                         *
                         * <element>
                         *     <tx_templavoilaplus>    -> entries with informational character belonging to this entry
                         *     <TCEforms>        -> entries being used for TCE-construction
                         *     <type + el + section>    -> subsequent hierarchical construction
                         *    <langOverlayMode>    -> ??? (is it the language-key?)
                         * </element>
                         */
                        if (($tv = $def['tx_templavoilaplus'])) {
                            /* The basic XML-structure of an tx_templavoilaplus-entry is:
                             *
                             * <tx_templavoilaplus>
                             *     <title>            -> Human readable title of the element
                             *     <description>        -> A description explaining the elements function
                             *     <sample_data>        -> Some sample-data (can't contain HTML)
                             *     <eType>            -> The preset-type of the element, used to switch use/content of TCEforms/TypoScriptObjPath
                             *     <oldStyleColumnNumber>    -> for distributing the fields across the tt_content column-positions
                             *     <proc>            -> define post-processes for this element's value
                             *        <int>        -> this element's value will be cast to an integer (if exist)
                             *        <HSC>        -> this element's value will convert special chars to HTML-entities (if exist)
                             *        <stdWrap>    -> an implicit stdWrap for this element, "stdWrap { ...inside... }"
                             *     </proc>
                             *    <TypoScript_constants>    -> an array of constants that will be substituted in the <TypoScript>-element
                             *     <TypoScript>        ->
                             *     <TypoScriptObjPath>    ->
                             * </tx_templavoilaplus>
                             */

                            if (isset($tv['description']) && ($tv['description'] != '')) {
                                $HTML .= '<p>"' . $tv['description'] . '"</p>';
                            }

                            /* it would also be possible to use the 'list-style-image'-property
                             * for the flags, which would be more sensible to IE-bugs though
                             */
                            $proc = '';
                            if (isset($tv['proc']) && isset($tv['proc']['int'])) {
                                $proc .= '<li>' .
                                    (($tv['proc']['int'] == 1)
                                        ? $this->iconFactory->getIcon('status-dialog-ok', Icon::SIZE_SMALL)->render()
                                        : $this->iconFactory->getIcon('status-dialog-error', Icon::SIZE_SMALL)->render()
                                    ) . ' ' . TemplaVoilaUtility::getLanguageService()->getLL('casttointeger', true) . '</li>';
                            }
                            if (isset($tv['proc']) && isset($tv['proc']['HSC'])) {
                                $proc .= '<li>' .
                                    (($tv['proc']['HSC'] == 1)
                                        ? $this->iconFactory->getIcon('status-dialog-ok', Icon::SIZE_SMALL)->render()
                                        : $this->iconFactory->getIcon('status-dialog-error', Icon::SIZE_SMALL)->render()
                                    ) . ' ' . TemplaVoilaUtility::getLanguageService()->getLL('hsced', true) .
                                    (($tv['proc']['HSC'] == 1)
                                        ? ' ' . TemplaVoilaUtility::getLanguageService()->getLL('hsc_on', true)
                                        : ' ' . TemplaVoilaUtility::getLanguageService()->getLL('hsc_off', true)
                                    ) . '</li>';
                            }
                            if (isset($tv['proc']) && isset($tv['proc']['stdWrap'])) {
                                $proc .= '<li>' .
                                    (($tv['proc']['stdWrap'] != '')
                                        ? $this->iconFactory->getIcon('status-dialog-ok', Icon::SIZE_SMALL)->render()
                                        : $this->iconFactory->getIcon('status-dialog-error', Icon::SIZE_SMALL)->render()
                                    ) . ' ' . TemplaVoilaUtility::getLanguageService()->getLL('stdwrap', true) . '</li>';
                            }

                            if ($proc != '') {
                                $HTML .= '<ul class="DS-proc">' . $proc . '</ul>';
                            }
                            //TODO: get the registered eTypes and use the labels
                            switch ($tv['eType']) {
                                case "input":
                                    $preset = 'Plain input field';
                                    $tco = false;
                                    break;
                                case "input_h":
                                    $preset = 'Header field';
                                    $tco = false;
                                    break;
                                case "input_g":
                                    $preset = 'Header field, Graphical';
                                    $tco = false;
                                    break;
                                case "text":
                                    $preset = 'Text area for bodytext';
                                    $tco = false;
                                    break;
                                case "rte":
                                    $preset = 'Rich text editor for bodytext';
                                    $tco = false;
                                    break;
                                case "link":
                                    $preset = 'Link field';
                                    $tco = false;
                                    break;
                                case "int":
                                    $preset = 'Integer value';
                                    $tco = false;
                                    break;
                                case "image":
                                    $preset = 'Image field';
                                    $tco = false;
                                    break;
                                case "imagefixed":
                                    $preset = 'Image field, fixed W+H';
                                    $tco = false;
                                    break;
                                case "select":
                                    $preset = 'Selector box';
                                    $tco = false;
                                    break;
                                case "ce":
                                    $preset = 'Content Elements';
                                    $tco = true;
                                    break;
                                case "TypoScriptObject":
                                    $preset = 'TypoScript Object Path';
                                    $tco = true;
                                    break;

                                case "none":
                                    $preset = 'None';
                                    $tco = true;
                                    break;
                                default:
                                    $preset = 'Custom [' . $tv['eType'] . ']';
                                    $tco = true;
                                    break;
                            }

                            switch ($tv['oldStyleColumnNumber']) {
                                case 0:
                                    $column = 'Normal [0]';
                                    break;
                                case 1:
                                    $column = 'Left [1]';
                                    break;
                                case 2:
                                    $column = 'Right [2]';
                                    break;
                                case 3:
                                    $column = 'Border [3]';
                                    break;
                                default:
                                    $column = 'Custom [' . $tv['oldStyleColumnNumber'] . ']';
                                    break;
                            }

                            $notes = '';
                            if (($tv['eType'] != "TypoScriptObject") && isset($tv['TypoScriptObjPath'])) {
                                $notes .= '<li>' . TemplaVoilaUtility::getLanguageService()->getLL('redundant', true) . ' &lt;TypoScriptObjPath&gt;-entry</li>';
                            }
                            if (($tv['eType'] == "TypoScriptObject") && isset($tv['TypoScript'])) {
                                $notes .= '<li>' . TemplaVoilaUtility::getLanguageService()->getLL('redundant', true) . ' &lt;TypoScript&gt;-entry</li>';
                            }
                            if ((($tv['eType'] == "TypoScriptObject") || !isset($tv['TypoScript'])) && isset($tv['TypoScript_constants'])) {
                                $notes .= '<li>' . TemplaVoilaUtility::getLanguageService()->getLL('redundant', true) . ' &lt;TypoScript_constants&gt;-' . TemplaVoilaUtility::getLanguageService()->getLL('entry', true) . '</li>';
                            }
                            if (isset($tv['proc']) && isset($tv['proc']['int']) && ($tv['proc']['int'] == 1) && isset($tv['proc']['HSC'])) {
                                $notes .= '<li>' . TemplaVoilaUtility::getLanguageService()->getLL('redundant', true) . ' &lt;proc&gt;&lt;HSC&gt;-' . TemplaVoilaUtility::getLanguageService()->getLL('redundant', true) . '</li>';
                            }
                            if (isset($tv['TypoScriptObjPath']) && preg_match('/[^a-zA-Z0-9\.\:_]/', $tv['TypoScriptObjPath'])) {
                                $notes .= '<li><strong>&lt;TypoScriptObjPath&gt;-' . TemplaVoilaUtility::getLanguageService()->getLL('illegalcharacters', true) . '</strong></li>';
                            }

                            $tsstats = '';
                            if (isset($tv['TypoScript_constants'])) {
                                $tsstats .= '<li>' . sprintf(TemplaVoilaUtility::getLanguageService()->getLL('dsdetails_tsconstants', true), count($tv['TypoScript_constants'])) . '</li>';
                            }
                            if (isset($tv['TypoScript'])) {
                                $tsstats .= '<li>' . sprintf(TemplaVoilaUtility::getLanguageService()->getLL('dsdetails_tslines', true), (1 + strlen($tv['TypoScript']) - strlen(str_replace("\n", "", $tv['TypoScript'])))) . '</li>';
                            }
                            if (isset($tv['TypoScriptObjPath'])) {
                                $tsstats .= '<li>' . sprintf(TemplaVoilaUtility::getLanguageService()->getLL('dsdetails_tsutilize', true), '<em>' . $tv['TypoScriptObjPath'] . '</em>') . '</li>';
                            }

                            $HTML .= '<dl class="DS-infos">';
                            $HTML .= '<dt>' . TemplaVoilaUtility::getLanguageService()->getLL('dsdetails_preset', true) . ':</dt>';
                            $HTML .= '<dd>' . $preset . '</dd>';
                            $HTML .= '<dt>' . TemplaVoilaUtility::getLanguageService()->getLL('dsdetails_column', true) . ':</dt>';
                            $HTML .= '<dd>' . $column . '</dd>';
                            if ($tsstats != '') {
                                $HTML .= '<dt>' . TemplaVoilaUtility::getLanguageService()->getLL('dsdetails_ts', true) . ':</dt>';
                                $HTML .= '<dd><ul class="DS-stats">' . $tsstats . '</ul></dd>';
                            }
                            if ($notes != '') {
                                $HTML .= '<dt>' . TemplaVoilaUtility::getLanguageService()->getLL('dsdetails_notes', true) . ':</dt>';
                                $HTML .= '<dd><ul class="DS-notes">' . $notes . '</ul></dd>';
                            }
                            $HTML .= '</dl>';
                        } else {
                            $HTML .= '<p>' . TemplaVoilaUtility::getLanguageService()->getLL('dsdetails_nobasicdefinitions', true) . '</p>';
                        }

                        /* The basic XML-structure of an TCEforms-entry is:
                         *
                         * <TCEforms>
                         *     <label>            -> TCE-label for the BE
                         *     <config>        -> TCE-configuration array
                         * </TCEforms>
                         */
                        if (!($def['TCEforms'])) {
                            if (!$tco) {
                                $HTML .= '<p>' . TemplaVoilaUtility::getLanguageService()->getLL('dsdetails_notceformdefinitions', true) . '</p>';
                            }
                        }
                    }
                }

                /* there are some childs to process ----------------------------- */
                if (isset($def['type']) && ($def['type'] == "array")) {
                    if (isset($def['el'])) {
                        $HTML .= $this->renderDSdetails($def['el']);
                    }
                }

                $HTML .= '</dd>';
            }

            $HTML .= '</dl>';
        } else {
            $HTML .= '<p>' . $this->iconFactory->getIcon('status-dialog-warning', Icon::SIZE_SMALL)->render()
                . ' The element has no children!</p>';
        }
        return $HTML;
    }

    /**
     * Show meta data part of Data Structure
     *
     * @param string $DSstring
     *
     * @return array
     */
    public function DSdetails($DSstring)
    {
        if (trim($DSstring) === '') {
            // Empty DS
            return [];
        }
        $DScontent = GeneralUtility::xml2array($DSstring);

        if (!is_array($DScontent)) {
            if (trim($DScontent) === '') {
                // Empty DS XML
                return [];
            } else {
                // Errors in DS XML
                return [
                    'HTML' => '<p>' . $this->iconFactory->getIcon('status-dialog-error', Icon::SIZE_SMALL)->render() . $DScontent,
                ];
            }
        }

        $inputFields = 0;
        $referenceFields = 0;
        $rootelements = 0;
        if (is_array($DScontent) && is_array($DScontent['ROOT']['el'])) {
            foreach ($DScontent['ROOT']['el'] as $elCfg) {
                $rootelements++;
                if (isset($elCfg['TCEforms'])) {
                    // Assuming that a reference field for content elements is recognized like this, increment counter. Otherwise assume input field of some sort.
                    if ($elCfg['TCEforms']['config']['type'] === 'group' && $elCfg['TCEforms']['config']['allowed'] === 'tt_content') {
                        $referenceFields++;
                    } else {
                        $inputFields++;
                    }
                }
                if (isset($elCfg['el'])) {
                    $elCfg['el'] = '...';
                }
                unset($elCfg['tx_templavoilaplus']['sample_data']);
                unset($elCfg['tx_templavoilaplus']['tags']);
                unset($elCfg['tx_templavoilaplus']['eType']);
            }
        }

        $languageMode = '';
        if (is_array($DScontent['meta'])) {
            if (isset($DScontent['meta']['langDisable']) && $DScontent['meta']['langDisable']) {
                $languageMode = 'Disabled';
            } elseif (isset($DScontent['meta']['langChildren']) && $DScontent['meta']['langChildren']) {
                $languageMode = 'Inheritance';
            } else {
                $languageMode = 'Separate';
            }
        }

        return array(
            'languageMode' => $languageMode,
            'rootelements' => $rootelements,
            'inputFields' => $inputFields,
            'referenceFields' => $referenceFields
        );
    }

    /**
     * Initialize the import-engine
     *
     * @return \TYPO3\CMS\Impexp\ImportExport Returns object ready to import the import-file used to create the basic site!
     */
    public function getImportObj()
    {
        /** @var \TYPO3\CMS\Impexp\ImportExport $import */
        $import = GeneralUtility::makeInstance(\tx_impexp::class);
        $import->init(0, 'import');
        $import->enableLogging = true;

        return $import;
    }

    /**
     * Syntax Highlighting of TypoScript code
     *
     * @param string $v String of TypoScript code
     *
     * @return string HTML content with it highlighted.
     */
    public function syntaxHLTypoScript($v)
    {
        $tsparser = GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser::class);
        $tsparser->lineNumberOffset = 0;
        $TScontent = $tsparser->doSyntaxHighlight(trim($v) . chr(10), '', 1);

        return $TScontent;
    }

    /**
     * Produce WRAP value
     *
     * @param array $cfg menuItemSuggestion configuration
     *
     * @return string Wrap for TypoScript
     */
    public function makeWrap($cfg)
    {
        if (!$cfg['bulletwrap']) {
            $wrap = $cfg['wrap'];
        } else {
            $wrap = $cfg['wrap'] . '  |*|  ' . $cfg['bulletwrap'] . $cfg['wrap'];
        }

        return preg_replace('/[' . chr(10) . chr(13) . chr(9) . ']/', '', $wrap);
    }

    /**
     * Returns the code that the menu was mapped to in the HTML
     *
     * @param string $field "Field" from Data structure, either "field_menu" or "field_submenu"
     *
     * @return string
     */
    public function getMenuDefaultCode($field)
    {
        // Select template record and extract menu HTML content
        $toRec = BackendUtility::getRecordWSOL('tx_templavoilaplus_tmplobj', $this->wizardData['templateObjectId']);
        $tMapping = unserialize($toRec['templatemapping']);

        return $tMapping['MappingData_cached']['cArray'][$field];
    }

    /**
     * Saves the menu TypoScript code
     *
     * @return void
     */
    public function saveMenuCode()
    {
        // Save menu code to template record:
        $cfg = GeneralUtility::_POST('CFG');
        if (isset($cfg['menuCode'])) {
            // Get template record:
            $TSrecord = BackendUtility::getRecord('sys_template', $this->wizardData['typoScriptTemplateID']);
            if (is_array($TSrecord)) {
                $data = array();
                $data['sys_template'][$TSrecord['uid']]['config']
                    = '## Menu [Begin]'
                    . trim($cfg['menuCode'])
                    . '## Menu [End]'
                    . $TSrecord['config'];

                // Execute changes:
                $tce = GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
                $tce->stripslashes_values = 0;
                $tce->dontProcessTransformations = 1;
                $tce->start($data, []);
                $tce->process_datamap();
            }
        }
    }

    /**
     * Tries to fetch the background color of a GIF or PNG image.
     *
     * @param string $filePath Filepath (absolute) of the image (must exist)
     *
     * @return string HTML hex color code, if any.
     */
    public function getBackgroundColor($filePath)
    {
        if (substr($filePath, -4) == '.gif' && function_exists('imagecreatefromgif')) {
            $im = @imagecreatefromgif($filePath);
        } elseif (substr($filePath, -4) == '.png' && function_exists('imagecreatefrompng')) {
            $im = @imagecreatefrompng($filePath);
        } else {
            $im = null;
        }

        if (is_resource($im)) {
            $values = imagecolorsforindex($im, imagecolorat($im, 3, 3));
            $color = '#' . substr('00' . dechex($values['red']), -2) .
                substr('00' . dechex($values['green']), -2) .
                substr('00' . dechex($values['blue']), -2);

            return $color;
        }

        return false;
    }

    /**
     * Find and check all template paths
     *
     * @return array all relevant template paths
     */
    protected function getTemplateFolders()
    {
        $templateFolders = [];
        if (strlen($this->modTSconfig['properties']['templatePath'])) {
            $paths = GeneralUtility::trimExplode(',', $this->modTSconfig['properties']['templatePath'], true);
        } else {
            $paths = array('templates');
        }

        $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
        $defaultStorage = $resourceFactory->getDefaultStorage();

        // Check if a default storage was defined
        if ($defaultStorage) {
            foreach ($paths as $path) {
                try {
                    $folder = $defaultStorage->getFolder('/' . $path);
                } catch (\Exception $e) {
                    // Blank catch, as we exspect that not all pathes may exists.
                    continue;
                }
                $templateFolders[] = $folder;
            }
        }

        return $templateFolders;
    }

    /**
     * Find and check all templates within the template paths
     *
     * @return array all relevant templates
     */
    protected function getTemplateFiles()
    {
        $paths = $this->getTemplateFolders();

        $filter = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\Filter\FileExtensionFilter::class);
        $filter->setAllowedFileExtensions('html,htm,tmpl');

        $files = array();
        foreach ($paths as $folder) {
            $folder->setFileAndFolderNameFilters([[$filter, 'filterFileList']]);
            $files = array_merge(
                $folder->getFiles(0, 0, \TYPO3\CMS\Core\Resource\Folder::FILTER_MODE_USE_OWN_FILTERS, true),
                $files
            );
        }

        return $files;
    }

    public function getLinkParameters(array $extraParams = [])
    {
        return array_merge(
            [
                'id' => $this->id,
            ],
            $extraParams
        );
    }

    public function getBaseUrl(array $extraParams = [])
    {
        return BackendUtility::getModuleUrl(
            $this->moduleName,
            $this->getLinkParameters($extraParams)
        );
    }

    /**
     * Builds a bootstrap button for given url
     *
     * @param string $clickUrl
     * @param string $title
     * @param string $icon
     * @param string $text
     * @param string $buttonType Type of the html button, see bootstrap
     * @param string $extraClass Extra class names to add to the bootstrap button classes
     * @param string $rel Data for the rel attrib
     * @return string
     */
    public function buildButtonFromUrl(
        $clickUrl,
        $title,
        $icon,
        $text = '',
        $buttonType = 'default',
        $extraClass = '',
        $rel = null
    ) {
        return '<a href="#"' . ($rel ? ' rel="' . $rel . '"' : '')
            . ' class="btn btn-' . $buttonType . ' btn-sm' . ($extraClass ? ' ' . $extraClass : '') . '"'
            . ' onclick="' . $clickUrl . '" title="' . $title . '">'
            . $this->iconFactory->getIcon($icon, Icon::SIZE_SMALL)->render()
            . ($text ? ' ' . $text : '')
            . '</a>';
    }

    /**
     * Adds an icon button to the document header button bar (left or right)
     *
     * @param string $module Name of the module this icon should link to
     * @param string $title Title of the button
     * @param string $icon Name of the Icon (inside IconFactory)
     * @param array $params Array of parameters which should be added to module call
     * @param string $buttonPosition left|right to position button inside the bar
     * @param integer $buttonGroup Number of the group the icon should go in
     */
    public function addDocHeaderButton(
        $module,
        $title,
        $icon,
        array $params = [],
        $buttonPosition = ButtonBar::BUTTON_POSITION_LEFT,
        $buttonGroup = 1
    ) {
        $url = BackendUtility::getModuleUrl(
            $module,
            array_merge(
                $params,
                [
                    'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
                ]
            )
        );

        $button = $this->buttonBar->makeLinkButton()
            ->setHref($url)
            ->setTitle($title)
            ->setIcon($this->iconFactory->getIcon($icon, Icon::SIZE_SMALL));
        $this->buttonBar->addButton($button, $buttonPosition, $buttonGroup);
    }

    /**
     * Generates max 64x64 thumbnail of given file.
     *
     * @param string $filePathAndName Path and name of file to get thumbnail from
     * @return string|null Public url or null if file not found.
     */
    protected function getThumbnail($filePathAndName)
    {
        try {
            /** @var \TYPO3\CMS\Core\Resource\File $fileObject */
            $fileObject = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->retrieveFileOrFolderObject($filePathAndName);
            // Skip the resource if it's not of type AbstractFile. One case where this can happen if the
            // storage has been externally modified and the field value now points to a folder
            // instead of a file.
            if (!$fileObject instanceof \TYPO3\CMS\Core\Resource\AbstractFile || $fileObject->isMissing()) {
                return null;
            }
        } catch (\TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException $exception) {
            return null;
        }

        return $fileObject->process(
            \TYPO3\CMS\Core\Resource\ProcessedFile::CONTEXT_IMAGEPREVIEW,
            [
                'width' => 64,
                'height' => 64,
            ]
        )->getPublicUrl(true);
    }
}
