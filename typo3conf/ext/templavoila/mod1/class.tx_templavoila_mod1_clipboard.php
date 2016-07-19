<?php
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

/**
 * Submodule 'clipboard' for the templavoila page module
 *
 * @author Robert Lemke <robert@typo3.org>
 */
class tx_templavoila_mod1_clipboard {

	/**
	 * @var \TYPO3\CMS\Backend\Clipboard\Clipboard
	 */
	protected $t3libClipboardObj;

	/**
	 * @var array
	 */
	protected $deleteUids;

	/**
	 * @var string
	 */
	protected $extKey;

	/**
	 * @var array
	 */
	protected $MOD_SETTINGS;

	/**
	 * A pointer to the parent object, that is the templavoila page module script. Set by calling the method init() of this class.
	 *
	 * @var \tx_templavoila_module1
	 */
	public $pObj;

	/**
	 * A reference to the doc object of the parent object.
	 *
	 * @var \TYPO3\CMS\Backend\Template\DocumentTemplate
	 */
	public $doc;

	/**
	 * Initializes the clipboard object. The calling class must make sure that the right locallang files are already loaded.
	 * This method is usually called by the templavoila page module.
	 *
	 * Also takes the GET variable "CB" and submits it to the t3lib clipboard class which handles all
	 * the incoming information and stores it in the user session.
	 *
	 * @param \tx_templavoila_module1 $pObj Reference to the parent object ($this)
	 *
	 * @return void
	 */
	public function init(&$pObj) {
		global $BACK_PATH;

		// Make local reference to some important variables:
		$this->pObj =& $pObj;
		$this->doc =& $this->pObj->doc;
		$this->extKey =& $this->pObj->extKey;
		$this->MOD_SETTINGS =& $this->pObj->MOD_SETTINGS;

		// Initialize the t3lib clipboard:
		$this->t3libClipboardObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Clipboard\Clipboard::class);
		$this->t3libClipboardObj->backPath = $BACK_PATH;
		$this->t3libClipboardObj->initializeClipboard();
		$this->t3libClipboardObj->lockToNormal();

		// Clipboard actions are handled:
		$CB = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('CB'); // CB is the clipboard command array
		$this->t3libClipboardObj->setCmd($CB); // Execute commands.

		if (isset ($CB['setFlexMode'])) {
			switch ($CB['setFlexMode']) {
				case 'copy' :
					$this->t3libClipboardObj->clipData['normal']['flexMode'] = 'copy';
					break;
				case 'cut':
					$this->t3libClipboardObj->clipData['normal']['flexMode'] = 'cut';
					break;
				case 'ref':
					$this->t3libClipboardObj->clipData['normal']['flexMode'] = 'ref';
					break;
				default:
					unset ($this->t3libClipboardObj->clipData['normal']['flexMode']);
					break;
			}
		}

		$this->t3libClipboardObj->cleanCurrent(); // Clean up pad
		$this->t3libClipboardObj->endClipboard(); // Save the clipboard content

		// Add a list of non-used elements to the sidebar:
		$this->pObj->sideBarObj->addItem('nonUsedElements', $this, 'sidebar_renderNonUsedElements', \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('nonusedelements'), 30);
	}

	/**
	 * Renders the copy, cut and reference buttons for the element specified by the
	 * flexform pointer.
	 *
	 * @param array $elementPointer Flex form pointer specifying the element we want to render the buttons for
	 * @param string $listOfButtons A comma separated list of buttons which should be rendered. Possible values: 'copy', 'cut' and 'ref'
	 *
	 * @return string HTML output: linked images which act as copy, cut and reference buttons
	 */
	public function element_getSelectButtons($elementPointer, $listOfButtons = 'copy,cut,ref') {
		$clipActive_copy = $clipActive_cut = $clipActive_ref = FALSE;
		if (!$elementPointer = $this->pObj->apiObj->flexform_getValidPointer($elementPointer)) {
			return '';
		}
		$elementRecord = $this->pObj->apiObj->flexform_getRecordByPointer($elementPointer);

		// Fetch the element from the "normal" clipboard (if any) and set the button states accordingly:
		if (is_array($this->t3libClipboardObj->clipData['normal']['el'])) {
			reset($this->t3libClipboardObj->clipData['normal']['el']);
			list ($clipboardElementTableAndUid, $clipboardElementPointerString) = each($this->t3libClipboardObj->clipData['normal']['el']);
			$clipboardElementPointer = $this->pObj->apiObj->flexform_getValidPointer($clipboardElementPointerString);

			// If we have no flexform reference pointing to the element, we create a short flexform pointer pointing to the record directly:
			if (!is_array($clipboardElementPointer)) {
				list ($clipboardElementTable, $clipboardElementUid) = explode('|', $clipboardElementTableAndUid);
				$pointToTheSameRecord = ($elementRecord['uid'] == $clipboardElementUid);
			} else {
				unset ($clipboardElementPointer['targetCheckUid']);
				unset ($elementPointer['targetCheckUid']);
				$pointToTheSameRecord = ($clipboardElementPointer == $elementPointer);
			}

			// Set whether the current element is selected for copy/cut/reference or not:
			if ($pointToTheSameRecord) {
				$selectMode = isset ($this->t3libClipboardObj->clipData['normal']['flexMode']) ? $this->t3libClipboardObj->clipData['normal']['flexMode'] : ($this->t3libClipboardObj->clipData['normal']['mode'] == 'copy' ? 'copy' : 'cut');
				$clipActive_copy = ($selectMode == 'copy');
				$clipActive_cut = ($selectMode == 'cut');
				$clipActive_ref = ($selectMode == 'ref');
			}
		}

		$copyIcon = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-edit-copy' . ($clipActive_copy ? '-release' : ''), array('title' => \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('copyrecord')));
		$cutIcon = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-edit-cut' . ($clipActive_cut ? '-release' : ''), array('title' => \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('cutrecord')));
		$refIcon = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('extensions-templavoila-clip_ref' . ($clipActive_ref ? '-release' : ''), array('title' => \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('createreference')));

		$removeElement = '&amp;CB[removeAll]=normal';
		$setElement = '&amp;CB[el][' . rawurlencode('tt_content|' . $elementRecord['uid']) . ']=' . rawurlencode($this->pObj->apiObj->flexform_getStringFromPointer($elementPointer));
		$setElementRef = '&amp;CB[el][' . rawurlencode('tt_content|' . $elementRecord['uid']) . ']=1';

		$linkCopy = '<a class="tpm-copy" href="index.php?' . $this->pObj->link_getParameters() . '&amp;CB[setCopyMode]=1&amp;CB[setFlexMode]=copy' . ($clipActive_copy ? $removeElement : $setElement) . '">' . $copyIcon . '</a>';
		$linkCut = '<a class="tpm-cut" href="index.php?' . $this->pObj->link_getParameters() . '&amp;CB[setCopyMode]=0&amp;CB[setFlexMode]=cut' . ($clipActive_cut ? $removeElement : $setElement) . '">' . $cutIcon . '</a>';
		$linkRef = '<a class="tpm-ref" href="index.php?' . $this->pObj->link_getParameters() . '&amp;CB[setCopyMode]=1&amp;CB[setFlexMode]=ref' . ($clipActive_ref ? $removeElement : $setElementRef) . '">' . $refIcon . '</a>';

		$output =
			(\TYPO3\CMS\Core\Utility\GeneralUtility::inList($listOfButtons, 'copy') && !in_array('copy', $this->pObj->blindIcons) ? $linkCopy : '') .
			(\TYPO3\CMS\Core\Utility\GeneralUtility::inList($listOfButtons, 'ref') && !in_array('ref', $this->pObj->blindIcons) ? $linkRef : '') .
			(\TYPO3\CMS\Core\Utility\GeneralUtility::inList($listOfButtons, 'cut') && !in_array('cut', $this->pObj->blindIcons) ? $linkCut : '');

		return $output;
	}

	/**
	 * Renders and returns paste buttons for the destination specified by the flexform pointer.
	 * The buttons are (or is) only rendered if a suitable element is found in the "normal" clipboard
	 * and if it is valid to paste it at the given position.
	 *
	 * @param array $destinationPointer Flexform pointer defining the destination location where a possible element would be pasted.
	 *
	 * @return string HTML output: linked image(s) which act as paste button(s)
	 */
	public function element_getPasteButtons($destinationPointer) {
		if (in_array('paste', $this->pObj->blindIcons)) {
			return '';
		}

		$origDestinationPointer = $destinationPointer;
		if (!$destinationPointer = $this->pObj->apiObj->flexform_getValidPointer($destinationPointer)) {
			return '';
		}
		if (!is_array($this->t3libClipboardObj->clipData['normal']['el'])) {
			return '';
		}

		reset($this->t3libClipboardObj->clipData['normal']['el']);
		list ($clipboardElementTableAndUid, $clipboardElementPointerString) = each($this->t3libClipboardObj->clipData['normal']['el']);
		$clipboardElementPointer = $this->pObj->apiObj->flexform_getValidPointer($clipboardElementPointerString);

		// If we have no flexform reference pointing to the element, we create a short flexform pointer pointing to the record directly:
		list ($clipboardElementTable, $clipboardElementUid) = explode('|', $clipboardElementTableAndUid);
		if (!is_array($clipboardElementPointer)) {
			if ($clipboardElementTable != 'tt_content') {
				return '';
			}

			$clipboardElementPointer = array(
				'table' => 'tt_content',
				'uid' => $clipboardElementUid
			);
		}

		// If the destination element is already a sub element of the clipboard element, we mustn't show any paste icon:
		$destinationRecord = $this->pObj->apiObj->flexform_getRecordByPointer($destinationPointer);
		$clipboardElementRecord = $this->pObj->apiObj->flexform_getRecordByPointer($clipboardElementPointer);
		$dummyArr = array();
		$clipboardSubElementUidsArr = $this->pObj->apiObj->flexform_getListOfSubElementUidsRecursively('tt_content', $clipboardElementRecord['uid'], $dummyArr);
		$clipboardElementHasSubElements = count($clipboardSubElementUidsArr) > 0;

		if ($clipboardElementHasSubElements) {
			if (array_search($destinationRecord['uid'], $clipboardSubElementUidsArr) !== FALSE) {
				return '';
			}
			if ($origDestinationPointer['uid'] == $clipboardElementUid) {
				return '';
			}
		}

		// Prepare the ingredients for the different buttons:
		$pasteMode = isset ($this->t3libClipboardObj->clipData['normal']['flexMode']) ? $this->t3libClipboardObj->clipData['normal']['flexMode'] : ($this->t3libClipboardObj->clipData['normal']['mode'] == 'copy' ? 'copy' : 'cut');
		$pasteAfterIcon = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('extensions-templavoila-paste', array('title' => \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('pasterecord')));
		$pasteSubRefIcon = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('extensions-templavoila-pasteSubRef', array('title' => \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('pastefce_andreferencesubs')));

		$sourcePointerString = $this->pObj->apiObj->flexform_getStringFromPointer($clipboardElementPointer);
		$destinationPointerString = $this->pObj->apiObj->flexform_getStringFromPointer($destinationPointer);

		$output = '';
		$clearCB = $this->pObj->modTSconfig['properties']['keepElementsInClipboard'] ? '' : '&amp;CB[removeAll]=normal';
		if (!in_array('pasteAfter', $this->pObj->blindIcons)) {
			$output .= '<a class="tpm-pasteAfter" href="index.php?' . $this->pObj->link_getParameters() . $clearCB . '&amp;pasteRecord=' . $pasteMode . '&amp;source=' . rawurlencode($sourcePointerString) . '&amp;destination=' . rawurlencode($destinationPointerString) . '">' . $pasteAfterIcon . '</a>';
		}
		// FCEs with sub elements have two different paste icons, normal elements only one:
		if ($pasteMode == 'copy' && $clipboardElementHasSubElements && !in_array('pasteSubRef', $this->pObj->blindIcons)) {
			$output .= '<a class="tpm-pasteSubRef" href="index.php?' . $this->pObj->link_getParameters() . $clearCB . '&amp;pasteRecord=copyref&amp;source=' . rawurlencode($sourcePointerString) . '&amp;destination=' . rawurlencode($destinationPointerString) . '">' . $pasteSubRefIcon . '</a>';
		}

		return $output;
	}

	/**
	 * Displays a list of local content elements on the page which were NOT used in the hierarchical structure of the page.
	 *
	 * @return string HTML output
	 * @access protected
	 */
	public function sidebar_renderNonUsedElements() {
		$output = '';
		$elementRows = array();
		$usedUids = array_keys($this->pObj->global_tt_content_elementRegister);
		$usedUids[] = 0;
		$pid = $this->pObj->id; // If workspaces should evaluated non-used elements it must consider the id: For "element" and "branch" versions it should accept the incoming id, for "page" type versions it must be remapped (because content elements are then related to the id of the offline version)

		$res = \Extension\Templavoila\Utility\GeneralUtility::getDatabaseConnection()->exec_SELECTquery(
			\TYPO3\CMS\Backend\Utility\BackendUtility::getCommonSelectFields('tt_content', '', array('uid', 'header', 'bodytext', 'sys_language_uid')),
			'tt_content',
			'pid=' . (int)$pid . ' ' .
			'AND uid NOT IN (' . implode(',', $usedUids) . ') ' .
			'AND ( t3ver_state NOT IN (1,3) OR (t3ver_wsid > 0 AND t3ver_wsid = ' . (int)\Extension\Templavoila\Utility\GeneralUtility::getBackendUser()->workspace . ') )' .
			\TYPO3\CMS\Backend\Utility\BackendUtility::deleteClause('tt_content') .
			\TYPO3\CMS\Backend\Utility\BackendUtility::versioningPlaceholderClause('tt_content'),
			'',
			'uid'
		);

		$this->deleteUids = array(); // Used to collect all those tt_content uids with no references which can be deleted
		while (FALSE !== ($row = \Extension\Templavoila\Utility\GeneralUtility::getDatabaseConnection()->sql_fetch_assoc($res))) {
			$elementPointerString = 'tt_content:' . $row['uid'];

			// Prepare the language icon:
			$languageLabel = htmlspecialchars($this->pObj->allAvailableLanguages[$row['sys_language_uid']]['title']);
			if ($this->pObj->allAvailableLanguages[$row['sys_language_uid']]['flagIcon']) {
				$languageIcon = \Extension\Templavoila\Utility\IconUtility::getFlagIconForLanguage($this->pObj->allAvailableLanguages[$row['sys_language_uid']]['flagIcon'], array('title' => $languageLabel, 'alt' => $languageLabel));
			} else {
				$languageIcon = ($languageLabel && $row['sys_language_uid'] ? '[' . $languageLabel . ']' : '');
			}

			// Prepare buttons:
			$cutButton = $this->element_getSelectButtons($elementPointerString, 'ref');
			$recordIcon = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIconForRecord('tt_content', $row);
			$recordButton = $this->pObj->doc->wrapClickMenuOnIcon($recordIcon, 'tt_content', $row['uid'], 1, '&callingScriptId=' . rawurlencode($this->pObj->doc->scriptID), 'new,copy,cut,pasteinto,pasteafter,delete');

			if (\Extension\Templavoila\Utility\GeneralUtility::getBackendUser()->workspace) {
				$wsRow = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordWSOL('tt_content', $row['uid']);
				$isDeletedInWorkspace = $wsRow['t3ver_state'] == 2;
			} else {
				$isDeletedInWorkspace = FALSE;
			}
			if (!$isDeletedInWorkspace) {
				$elementRows[] = '
					<tr id="' . $elementPointerString . '" class="tpm-nonused-element">
						<td class="tpm-nonused-controls">' .
					$cutButton . $languageIcon .
					'</td>
					<td class="tpm-nonused-ref">' .
					$this->renderReferenceCount($row['uid']) .
					'</td>
					<td class="tpm-nonused-preview">' .
					$recordButton . htmlspecialchars(\TYPO3\CMS\Backend\Utility\BackendUtility::getRecordTitle('tt_content', $row)) .
					'</td>
				</tr>
			';
			}
		}

		if (count($elementRows)) {

			// Control for deleting all deleteable records:
			$deleteAll = '';
			if (count($this->deleteUids)) {
				$params = '';
				foreach ($this->deleteUids as $deleteUid) {
					$params .= '&cmd[tt_content][' . $deleteUid . '][delete]=1';
				}
				$label = \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('rendernonusedelements_deleteall');
				$deleteAll = '<a href="#" onclick="' . htmlspecialchars('jumpToUrl(\'' . $this->doc->issueCommand($params, -1) . '\');') . '">' .
					\TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-edit-delete', array('title' => htmlspecialchars($label))) .
					htmlspecialchars($label) .
					'</a>';
			}

			// Create table and header cell:
			$output = '
				<table class="tpm-nonused-elements lrPadding" border="0" cellpadding="0" cellspacing="1" width="100%">
					<tr class="bgColor4-20">
						<td colspan="3">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('inititemno_elementsNotBeingUsed', TRUE) . ':</td>
					</tr>
					' . implode('', $elementRows) . '
					<tr class="bgColor4">
						<td colspan="3" class="tpm-nonused-deleteall">' . $deleteAll . '</td>
					</tr>
				</table>
			';
		}

		return $output;
	}

	/**
	 * Render a reference count in form of an HTML table for the content
	 * element specified by $uid.
	 *
	 * @param integer $uid Element record Uid
	 *
	 * @return string HTML-table
	 * @access protected
	 */
	public function renderReferenceCount($uid) {
		$rows = \Extension\Templavoila\Utility\GeneralUtility::getDatabaseConnection()->exec_SELECTgetRows(
			'*',
			'sys_refindex',
			'ref_table=' . \Extension\Templavoila\Utility\GeneralUtility::getDatabaseConnection()->fullQuoteStr('tt_content', 'sys_refindex') .
			' AND ref_uid=' . (int)$uid .
			' AND deleted=0'
		);

		// Compile information for title tag:
		$infoData = array();
		if (is_array($rows)) {
			foreach ($rows as $row) {

				if (\Extension\Templavoila\Utility\GeneralUtility::getBackendUser()->workspace && $row['tablename'] == 'pages' && $this->pObj->id == $row['recuid']) {
					// We would have found you but we didn't - you're most likely deleted
				} elseif (\Extension\Templavoila\Utility\GeneralUtility::getBackendUser()->workspace && $row['tablename'] == 'tt_content' && $this->pObj->global_tt_content_elementRegister[$row['recuid']] > 0) {
					// We would have found you but we didn't - you're most likely deleted
				} else {
					$infoData[] = $row['tablename'] . ':' . $row['recuid'] . ':' . $row['field'];
				}
			}
		}
		if (count($infoData)) {
			return '<a class="tpm-countRef" href="#" onclick="' . htmlspecialchars('top.launchView(\'tt_content\', \'' . $uid . '\'); return false;') . '" title="' . htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::fixed_lgd_cs(implode(' / ', $infoData), 100)) . '">Ref: ' . count($infoData) . '</a>';
		} else {
			$this->deleteUids[] = $uid;
			$params = '&cmd[tt_content][' . $uid . '][delete]=1';

			return '<a class="tpm-countRef" href="#" onclick="' . htmlspecialchars('jumpToUrl(\'' . $this->doc->issueCommand($params, -1) . '\');') . '">' .
			\TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-edit-delete', array('title' => \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('renderreferencecount_delete', TRUE))) .
			'</a>';
		}
	}
}
