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
 * Submodule 'dsEdit' for the mapping module
 *
 * @author Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @co-author Robert Lemke <robert@typo3.org>
 * @co-author Steffen kamper <info@sk-typo3.de>
 */
class tx_templavoila_cm1_dsEdit {

	/**
	 * @var \tx_templavoila_cm1
	 */
	protected $pObj;

	/**
	 * @var integer
	 */
	protected $oldStyleColumnNumber = 0;

	/**
	 * @param \tx_templavoila_cm1 $pObj
	 */
	public function init($pObj) {
		$this->pObj = $pObj;
	}

	/**
	 * Creates the editing row for a Data Structure element - when DS's are build...
	 *
	 * @param string $formPrefix Form element prefix
	 * @param string $key Key for form element
	 * @param array $value Values for element
	 * @param integer $level Indentation level
	 * @param array $rowCells Array containing mapping links and commands
	 *
	 * @return array Two values, first is addEditRows (string HTML content), second is boolean whether to place row before or after.
	 */
	public function drawDataStructureMap_editItem($formPrefix, $key, $value, $level, $rowCells) {

		// Init:
		$addEditRows = '';
		$placeBefore = 0;

		// If editing command is set:
		if ($this->pObj->editDataStruct) {
			if ($this->pObj->DS_element == $formPrefix . '[' . $key . ']') { // If the editing-command points to this element:

				// Initialize, detecting either "add" or "edit" (default) mode:
				$autokey = '';
				if ($this->pObj->DS_cmd == 'add') {
					if (trim($this->pObj->fieldName) != '[' . htmlspecialchars(\Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEnterNewFieldname')) . ']' && trim($this->pObj->fieldName) != 'field_') {
						$autokey = strtolower(preg_replace('/[^a-z0-9_]/i', '', trim($this->pObj->fieldName)));
						if (isset($value['el'][$autokey])) {
							$autokey .= '_' . substr(md5(microtime()), 0, 2);
						}
					} else {
						$autokey = 'field_' . substr(md5(microtime()), 0, 6);
					}

					// new entries are more offset
					$level = $level + 1;

					$formFieldName = 'autoDS' . $formPrefix . '[' . $key . '][el][' . $autokey . ']';
					$insertDataArray = array();
				} else {
					$placeBefore = 1;

					$formFieldName = 'autoDS' . $formPrefix . '[' . $key . ']';
					$insertDataArray = $value;
				}

				/* put these into array-form for preset-completition */
				$insertDataArray['tx_templavoila']['TypoScript_constants'] =
					$this->pObj->unflattenarray($insertDataArray['tx_templavoila']['TypoScript_constants']);
				$insertDataArray['TCEforms']['config'] =
					$this->pObj->unflattenarray($insertDataArray['TCEforms']['config']);

				/* do the preset-completition */
				$real = array($key => &$insertDataArray);
				$this->pObj->eTypes->substEtypeWithRealStuff($real);

				/* ... */
				if ($insertDataArray['type'] == 'array' && $insertDataArray['section']) {
					$insertDataArray['type'] = 'section';
				}

				$eTypes = $this->pObj->eTypes->defaultEtypes();
				$eTypes_formFields = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $eTypes['defaultTypes_formFields']);
				$eTypes_typoscriptElements = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $eTypes['defaultTypes_typoscriptElements']);
				$eTypes_misc = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $eTypes['defaultTypes_misc']);

				// Create form:
				/* The basic XML-structure of an tx_templavoila-entry is:
				 *
				 * <tx_templavoila>
				 * 	<title>			-> Human readable title of the element
				 * 	<description>		-> A description explaining the elements function
				 * 	<sample_data>		-> Some sample-data (can't contain HTML)
				 * 	<eType>			-> The preset-type of the element, used to switch use/content of TCEforms/TypoScriptObjPath
				 * 	<oldStyleColumnNumber>	-> for distributing the fields across the tt_content column-positions
				 * </tx_templavoila>
				 */
				$form = '
				<dl id="dsel-general" class="DS-config">
					<!-- always present options +++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->
					<dt><label>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('renderDSO_title') . ':</label></dt>
					<dd><input type="text" size="40" name="' . $formFieldName . '[tx_templavoila][title]" value="' . htmlspecialchars($insertDataArray['tx_templavoila']['title']) . '" /></dd>

					<dt><label>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('renderDSO_mappingInstructions') . ':</label></dt>
					<dd><input type="text" size="40" name="' . $formFieldName . '[tx_templavoila][description]" value="' . htmlspecialchars($insertDataArray['tx_templavoila']['description']) . '" /></dd>';

				if ($insertDataArray['type'] != 'array' && $insertDataArray['type'] != 'section') {
					$form .= '
					<!-- non-array options ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->
					<dt><label>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapSampleData') . ':</label></dt>
					<dd><textarea cols="40" rows="5" name="' . $formFieldName . '[tx_templavoila][sample_data][]">' . htmlspecialchars($insertDataArray['tx_templavoila']['sample_data'][0]) . '</textarea>
					' . $this->pObj->lipsumLink($formFieldName . '[tx_templavoila][sample_data]') . '</dd>

					<dt><label>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapElementPreset') . ':</label></dt>
					<dd><select name="' . $formFieldName . '[tx_templavoila][eType]">
						<optgroup class="c-divider" label="' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapPresetGroups_tceFields') . '">';
					foreach ($eTypes_formFields as $eType) {
						$label = htmlspecialchars($eType == 'ce' ?
							sprintf($eTypes['eType'][$eType]['label'], $insertDataArray['tx_templavoila']['oldStyleColumnNumber'] ? (int)$insertDataArray['tx_templavoila']['oldStyleColumnNumber'] : $this->oldStyleColumnNumber) :
							$eTypes['eType'][$eType]['label']);
						$form .= chr(10) . '<option value="' . $eType . '"' . ($insertDataArray['tx_templavoila']['eType'] == $eType ? ' selected="selected"' : '') . '>' . $label . '</option>';
					}
					$form .= '
						</optgroup>
						<optgroup class="c-divider" label="' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapPresetGroups_ts') . '">';
					foreach ($eTypes_typoscriptElements as $eType) {
						$form .= chr(10) . '<option value="' . $eType . '"' . ($insertDataArray['tx_templavoila']['eType'] == $eType ? ' selected="selected"' : '') . '>' . htmlspecialchars($eTypes['eType'][$eType]['label']) . '</option>';
					}
					$form .= '
						</optgroup>
						<optgroup class="c-divider" label="' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapPresetGroups_other') . '">';
					foreach ($eTypes_misc as $eType) {
						$form .= chr(10) . '<option value="' . $eType . '"' . ($insertDataArray['tx_templavoila']['eType'] == $eType ? ' selected="selected"' : '') . '>' . htmlspecialchars($eTypes['eType'][$eType]['label']) . '</option>';
					}
					$form .= '
						</optgroup>
					</select><p>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapWarningElementChange') . '</p><input type="hidden"
						name="' . $formFieldName . '[tx_templavoila][eType_before]"
						value="' . $insertDataArray['tx_templavoila']['eType'] . '" /></dd>';
				}

				$form .= '
					<dt><label>Mapping rules:</label></dt>
					<dd><input type="text" size="40" name="' . $formFieldName . '[tx_templavoila][tags]" value="' . htmlspecialchars($insertDataArray['tx_templavoila']['tags']) . '" /></dd>
				</dl>';

				if (($insertDataArray['type'] != 'array') &&
					($insertDataArray['type'] != 'section')
				) {
					/* The Typoscript-related XML-structure of an tx_templavoila-entry is:
					 *
					 * <tx_templavoila>
					 *	<TypoScript_constants>	-> an array of constants that will be substituted in the <TypoScript>-element
					 * 	<TypoScript>		->
					 * </tx_templavoila>
					 */
					if ($insertDataArray['tx_templavoila']['eType'] != 'TypoScriptObject') {
						$form .= '
					<dl id="dsel-ts" class="DS-config">
						<dt><label>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapTSconstants') . ':</label></dt>
						<dd><textarea class="xml enable-tab" cols="40" rows="10" wrap="off" name="' . $formFieldName . '[tx_templavoila][TypoScript_constants]">' . htmlspecialchars($this->pObj->flattenarray($insertDataArray['tx_templavoila']['TypoScript_constants'])) . '</textarea></dd>
						<dt><label>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapTScode') . ':</label></dt>
						<dd><textarea class="code enable-tab" cols="40" rows="10" wrap="off" name="' . $formFieldName . '[tx_templavoila][TypoScript]">' . htmlspecialchars($insertDataArray['tx_templavoila']['TypoScript']) . '</textarea></dd>
					</dl>';
					}

					/* The Typoscript-related XML-structure of an tx_templavoila-entry is:
					 *
					 * <tx_templavoila>
					 * 	<TypoScriptObjPath>	->
					 * </tx_templavoila>
					 */

					if (isset($insertDataArray['tx_templavoila']['TypoScriptObjPath'])) {
						$curValue = array('objPath' => $insertDataArray['tx_templavoila']['TypoScriptObjPath']);
					} elseif (isset($insertDataArray['tx_templavoila']['eType_EXTRA'])) {
						$curValue = $insertDataArray['tx_templavoila']['eType_EXTRA'];
					} else {
						$curValue = '';
					}
					$extra = $this->drawDataStructureMap_editItem_editTypeExtra(
						$insertDataArray['tx_templavoila']['eType'],
						$formFieldName,
						$curValue,
						$key
					);

					if ($extra) {
						$form .= '
						<dl id="dsel-extra" class="DS-config">
							<dt>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapExtraOptions') . '</dt>
							<dd>' . $extra . '</dd>
						</dl>';
					}

					/* The process-related XML-structure of an tx_templavoila-entry is:
					 *
					 * <tx_templavoila>
					 * 	<proc>			-> define post-processes for this element's value
					 *		<int>		-> this element's value will be cast to an integer (if exist)
					 *		<HSC>		-> this element's value will convert special chars to HTML-entities (if exist)
					 *		<stdWrap>	-> an implicit stdWrap for this element, "stdWrap { ...inside... }"
					 * 	</proc>
					 * </tx_templavoila>
					 */
					$form .= '
					<dl id="dsel-proc" class="DS-config">
						<dt>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapPostProcesses') . ':</dt>
						<dd>
							<input type="checkbox" class="checkbox" id="tv_proc_int_" value="1" ' . ($insertDataArray['tx_templavoila']['proc']['int'] ? 'checked="checked"' : '') . ' onclick="$(\'tv_proc_int\').value=(this.checked ? 1 : 0);" />
							<label for="tv_proc_int_">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapPPcastInteger') . '</label><br />
							<input type="checkbox" class="checkbox" id="tv_proc_hsc_" value="1" ' . ($insertDataArray['tx_templavoila']['proc']['HSC'] ? 'checked="checked"' : '') . ' onclick="$(\'tv_proc_hsc\').value=(this.checked ? 1 : 0);" />
							<label for="tv_proc_hsc_">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapPPhsc') . '</label>
							<input type="hidden" id="tv_proc_int" name="' . $formFieldName . '[tx_templavoila][proc][int]" value="' . (int)$insertDataArray['tx_templavoila']['proc']['int'] . '" />
							<input type="hidden" id="tv_proc_hsc" name="' . $formFieldName . '[tx_templavoila][proc][HSC]" value="' . (int)$insertDataArray['tx_templavoila']['proc']['HSC'] . '" />
						</dd>

						<dt><label>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapCustomStdWrap') . ':</label></dt>
						<dd><textarea class="code" cols="40" rows="10" name="' . $formFieldName . '[tx_templavoila][proc][stdWrap]">' . htmlspecialchars($insertDataArray['tx_templavoila']['proc']['stdWrap']) . '</textarea></dd>

						<dt><label>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEnablePreview') . ':</label></dt>
						<dd>
							<input type="radio" class="radio" id="tv_preview_enable" value="" name="' . $formFieldName . '[tx_templavoila][preview]" ' . ($insertDataArray['tx_templavoila']['preview'] != 'disable' ? 'checked="checked"' : '') . '> <label for="tv_preview_enable">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEnablePreview.enable') . '</label><br/>
							<input type="radio" class="radio" id="tv_preview_disable" value="disable" name="' . $formFieldName . '[tx_templavoila][preview]" ' . ($insertDataArray['tx_templavoila']['preview'] == 'disable' ? 'checked="checked"' : '') . '> <label for="tv_preview_disable">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEnablePreview.disable') . '</label>
						</dd>';
					if ($insertDataArray['tx_templavoila']['eType'] === 'ce') {
						if (!isset($insertDataArray['tx_templavoila']['oldStyleColumnNumber'])) {
							$insertDataArray['tx_templavoila']['oldStyleColumnNumber'] = $this->oldStyleColumnNumber++;
						}
						$form .= '
							<dt>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapOldStyleColumnNumber') . '</dt>
							<dd>
								<label for="tv_oldstylecolumnnumber">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapOldStyleColumnNumber_label') . ':</label><br />
								<input type="text" id="tv_oldstylecolumnnumber" name="' . $formFieldName . '[tx_templavoila][oldStyleColumnNumber]" value="' . (int)$insertDataArray['tx_templavoila']['oldStyleColumnNumber'] . '" />

							</dd>';

						$form .= '
							<dt><label for="tv_enabledragdrop_">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEnableDragDrop') . '</label></dt>
							<dd>
								<input type="checkbox" class="checkbox" id="tv_enabledragdrop_" value="1" ' . (($insertDataArray['tx_templavoila']['enableDragDrop'] === '0') ? '' : 'checked="checked"') . ' onclick="$(\'tv_enabledragdrop\').value=(this.checked ? 1 : 0);" />
								<input type="hidden" id="tv_enabledragdrop" name="' . $formFieldName . '[tx_templavoila][enableDragDrop]" value="' . (int)$insertDataArray['tx_templavoila']['enableDragDrop'] . '" />
							</dd>';
					}
					$form .= '</dl>';

					/* The basic XML-structure of an TCEforms-entry is:
					 *
					 * <TCEforms>
					 * 	<label>			-> TCE-label for the BE
					 * 	<config>		-> TCE-configuration array
					 * </TCEforms>
					 */
					if ($insertDataArray['tx_templavoila']['eType'] != 'TypoScriptObject') {
						$form .= '
					<dl id="dsel-tce" class="DS-config">
						<dt><label>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapTCElabel') . ':</label></dt>
						<dd><input type="text" size="40" name="' . $formFieldName . '[TCEforms][label]" value="' . htmlspecialchars($insertDataArray['TCEforms']['label']) . '" /></dd>

						<dt><label>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapTCEconf') . ':</label></dt>
						<dd><textarea class="xml" cols="40" rows="10" name="' . $formFieldName . '[TCEforms][config]">' . htmlspecialchars($this->pObj->flattenarray($insertDataArray['TCEforms']['config'])) . '</textarea></dd>

						<dt><label>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapTCEextras') . ':</label></dt>
						<dd><input type="text" size="40" name="' . $formFieldName . '[TCEforms][defaultExtras]" value="' . htmlspecialchars($insertDataArray['TCEforms']['defaultExtras']) . '" /></dd>
					</dl>';
					}
				} else {
					$form .= '
						<dl id="dsel-proc" class="DS-config">
							<dt><label>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEnablePreview') . ':</label></dt>
							<dd>
								<input type="radio" class="radio" id="tv_preview_enable" value="" name="' . $formFieldName . '[tx_templavoila][preview]" ' . ($insertDataArray['tx_templavoila']['preview'] != 'disable' ? 'checked="checked"' : '') . '> <label for="tv_preview_enable">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEnablePreview.enable') . '</label><br/>
								<input type="radio" class="radio" id="tv_preview_disable" value="disable" name="' . $formFieldName . '[tx_templavoila][preview]" ' . ($insertDataArray['tx_templavoila']['preview'] == 'disable' ? 'checked="checked"' : '') . '> <label for="tv_preview_disable">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEnablePreview.disable') . '</label>
							</dd>
						</dl>';
				}

				$formSubmit = '
					<input type="hidden" name="DS_element" value="' . htmlspecialchars($this->pObj->DS_cmd == 'add' ? $this->pObj->DS_element . '[el][' . $autokey . ']' : $this->pObj->DS_element) . '" />
					<input type="submit" name="_updateDS" value="' . ($this->pObj->DS_cmd == 'add' ? \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('buttonAdd') : \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('buttonUpdate')) . '" />
					<!--	<input type="submit" name="' . $formFieldName . '" value="' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('buttonDelete') . ' (!)" />  -->
					<input type="submit" name="_" value="' . ($this->pObj->DS_cmd == 'add' ? \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('buttonCancel') : \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('buttonCancelClose')) . '" onclick="document.location=\'' . $this->pObj->linkThisScript() . '\'; return false;" />
				';

				/* The basic XML-structure of an entry is:
				 *
				 * <element>
				 * 	<tx_templavoila>	-> entries with informational character belonging to this entry
				 * 	<TCEforms>		-> entries being used for TCE-construction
				 * 	<type + el + section>	-> subsequent hierarchical construction
				 *	<langOverlayMode>	-> ??? (is it the language-key?)
				 * </element>
				 */

				// Icons:
				$info = $this->pObj->dsTypeInfo($insertDataArray);

				// Find "select" style. This is necessary because Safari
				// does not support paddings in select elements but supports
				// backgrounds. The rest is text over background.
				$selectStyle = 'margin: 4px 0; width: 150px !important; display: block;';
				$userAgent = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_USER_AGENT');
				if (strpos($userAgent, 'WebKit') === FALSE) {
					// Not Safai (Can't have "padding" for select elements in Safari)
					$selectStyle .= 'padding: 1px 1px 1px 30px; background: 0 50% url(' . $info[3] . ') no-repeat;';
				}

				$addEditRows = '<tr class="tv-edit-row">
					<td valign="top" style="padding: 0.5em; padding-left: ' . (($level) * 16 + 3) . 'px" nowrap="nowrap" rowspan="2">
						<select style="' . $selectStyle . '" title="Mapping Type" name="' . $formFieldName . '[type]">
							<optgroup class="c-divider" label="' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapElContainers') . '">
								<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->pObj->dsTypes['sc'][3] . ') no-repeat;" value="section"' . ($insertDataArray['type'] == 'section' ? ' selected="selected"' : '') . '>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapSection') . '</option>
								<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->pObj->dsTypes['co'][3] . ') no-repeat;" value="array"' . ($insertDataArray['type'] == 'array' ? ' selected="selected"' : '') . '>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapContainer') . '</option>
							</optgroup>
							<optgroup class="c-divider" label="' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapElElements') . '">
								<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->pObj->dsTypes['el'][3] . ') no-repeat;" value=""' . ($insertDataArray['type'] == '' ? ' selected="selected"' : '') . '>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapElement') . '</option>
								<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->pObj->dsTypes['at'][3] . ') no-repeat;" value="attr"' . ($insertDataArray['type'] == 'attr' ? ' selected="selected"' : '') . '>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapAttribute') . '</option>
							</optgroup>
							<optgroup class="c-divider" label="' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapPresetGroups_other') . '">
								<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->pObj->dsTypes['no'][3] . ') no-repeat;" value="no_map"' . ($insertDataArray['type'] == 'no_map' ? ' selected="selected"' : '') . '>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapNotMapped') . '</option>
							</optgroup>
						</select>
						<div style="margin: 0.25em;">' .
					($this->pObj->DS_cmd == 'add' ? $autokey . ' <strong>(new)</strong>:<br />' : $key) .
					'</div>
					<input id="dsel-act" type="hidden" name="dsel_act" />
					<ul id="dsel-menu" class="DS-tree">
						<li><a id="dssel-general" class="active" href="#" onclick="" title="' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEditConfiguration') . '">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapConfiguration') . '</a>
								<ul>
									<li><a id="dssel-proc" href="#" title="' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEditDataProcessing') . '">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapDataProcessing') . '</a></li>
									<li><a id="dssel-ts" href="#" title="' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEditTyposcript') . '">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapTyposcript') . '</a></li>
									<li class="last-child"><a id="dssel-extra" href="#" title="' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEditExtra') . '">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapExtra') . '</a></li>
								</ul>
							</li>
							<li class="last-child"><a id="dssel-tce" href="#" title="' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEditTCEform') . '">' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapTCEform') . '</a></li>
						</ul>
						' . $this->pObj->cshItem('xMOD_tx_templavoila', 'mapping_editform', $this->pObj->doc->backPath, '', FALSE, 'margin-bottom: 0px;') . '
					</td>

					<td valign="top" style="padding: 0.5em;" colspan="2">
						' . $form . '
						<script type="text/javascript">
							var dsel_act = "' . (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('dsel_act') ? \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('dsel_act') : 'general') . '";
							var dsel_menu = [
								{"id" : "general",		"avail" : true,	"label" : "' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapConfiguration') . '",	"title" : "' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEditConfiguration') . '",	"childs" : [
									{"id" : "ts",		"avail" : true,	"label" : "' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapDataProcessing') . '",	"title" : "' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEditDataProcessing') . '"},
									{"id" : "extra",	"avail" : true,	"label" : "' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapTyposcript') . '",		"title" : "' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEditTyposcript') . '"},
									{"id" : "proc",		"avail" : true,	"label" : "' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapExtra') . '",	"title" : "' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEditExtra') . '"}]},
								{"id" : "tce",			"avail" : true,	"label" : "' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapTCEform') . '",		"title" : "' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEditTCEform') . '"}
							];

							function dsel_menu_construct(dsul, dsmn) {
								if (dsul) {
									while (dsul.childNodes.length)
										dsul.removeChild(dsul.childNodes[0]);
									for (var el = 0, pos = 0; el < dsmn.length; el++) {
										var tab = document.getElementById("dsel-" + dsmn[el]["id"]);
										var stl = "none";
										if (tab) { if (dsmn[el]["avail"]) {
											var tx = document.createTextNode(dsmn[el]["label"]);
											var ac = document.createElement("a"); ac.appendChild(tx);
											var li = document.createElement("li"); li.appendChild(ac);
											ac.title = dsmn[el]["title"]; ac.href = "#dsel-menu"; ac.rel = dsmn[el]["id"];
											ac.className = (dsel_act == dsmn[el]["id"] ? "active" : "");
											ac.onclick = function() { dsel_act = this.rel; dsel_menu_reset(); return false; };
											if (dsmn[el]["childs"]) {
												var ul = document.createElement("ul");
												dsel_menu_construct(ul, dsmn[el]["childs"]);
												li.appendChild(ul);
											}
											dsul.appendChild(li);
											stl = (dsel_act == dsmn[el]["id"] ? "" : "none");
										} tab.style.display = stl; }
									}
									if (dsul.lastChild)
										dsul.lastChild.className = "last-child";
								}
							}

							function dsel_menu_reset() {
								dsel_menu_construct(document.getElementById("dsel-menu"), dsel_menu);
								document.getElementById("dsel-act").value = dsel_act;
							}

							dsel_menu_reset();
						</script>
					</td>
					<td>' . ($this->pObj->DS_cmd == 'add' ? '' : $rowCells['htmlPath']) . '</td>
					<td>' . ($this->pObj->DS_cmd == 'add' ? '' : $rowCells['cmdLinks']) . '</td>
					<td>' . ($this->pObj->DS_cmd == 'add' ? '' : $rowCells['tagRules']) . '</td>
					<td colspan="2"></td>
				</tr>
				<tr class="tv-edit-row">
					<td class="edit-ds-actioncontrols" colspan="6">
					' . $formSubmit . '
					</td>
				</tr>';
			} elseif (($value['type'] == 'array' || $value['type'] == 'section') && !$this->pObj->mapElPath) {
				$addEditRows = '<tr class="bgColor4">
					<td colspan="7"><img src="clear.gif" width="' . (($level + 1) * 16) . '" height="1" alt="" />' .
					'<input type="text" name="' . md5($formPrefix . '[' . $key . ']') . '" value="[' . htmlspecialchars(\Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEnterNewFieldname')) . ']" onfocus="if (this.value==\'[' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapEnterNewFieldname') . ']\'){this.value=\'field_\';}" />' .
					'<input type="submit" name="_" value="Add" onclick="document.location=\'' . $this->pObj->linkThisScript(array('DS_element' => $formPrefix . '[' . $key . ']', 'DS_cmd' => 'add')) . '&amp;fieldName=\'+document.pageform[\'' . md5($formPrefix . '[' . $key . ']') . '\'].value; return false;" />' .
					$this->pObj->cshItem('xMOD_tx_templavoila', 'mapping_addfield', $this->pObj->doc->backPath, '', FALSE, 'margin-bottom: 0px;') .
					'</td>
				</tr>';
			}
		}

		// Return edit row:
		return array($addEditRows, $placeBefore);
	}

	/**
	 * Renders extra form fields for configuration of the Editing Types.
	 *
	 * @param string $type Editing Type string
	 * @param string $formFieldName Form field name prefix
	 * @param array $curValue Current values for the form field name prefix.
	 * @param string $key Templavoila field name.
	 *
	 * @return string HTML with extra form fields
	 * @access private
	 * @see drawDataStructureMap_editItem()
	 */
	public function drawDataStructureMap_editItem_editTypeExtra($type, $formFieldName, $curValue, $key = '') {
		$curValue = (array)$curValue;
		// If a user function was registered, use that instead of our own handlers:
		if (isset ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['cm1']['eTypesExtraFormFields'][$type])) {
			$_params = array(
				'type' => $type,
				'formFieldName' => $formFieldName . '[tx_templavoila][eType_EXTRA]',
				'curValue' => $curValue,
			);
			$output = \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['cm1']['eTypesExtraFormFields'][$type], $_params, $this);
		} else {
			switch ($type) {
				default:
				case 'TypoScriptObject':
					$value = $curValue['objPath'] ? $curValue['objPath'] : 'lib.' . $key;
					$output = '
						<table border="0" cellpadding="2" cellspacing="0">
							<tr>
								<td>' . \Extension\Templavoila\Utility\GeneralUtility::getLanguageService()->getLL('mapObjectPath') . ':</td>
								<td>
									<input type="text" name="' . $formFieldName . '[tx_templavoila][eType_EXTRA][objPath]" value="' . htmlspecialchars($value) . '" onchange="$(\'hiddenTypoScriptObjPath\').value=this.value;" />
									<input type="hidden" id="hiddenTypoScriptObjPath" name="' . $formFieldName . '[tx_templavoila][TypoScriptObjPath]" value="' . htmlspecialchars($value) . '" />

								</td>
							</tr>
						</table>';
					break;
			}
		}

		return $output;
	}
}
