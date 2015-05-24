<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) Stefan Galinski <stefan@sgalinski.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

$pathToScriptmerger = t3lib_extMgm::extPath('scriptmerger');
require_once($pathToScriptmerger . 'Classes/Base.php');
require_once($pathToScriptmerger . 'Classes/Css.php');
require_once($pathToScriptmerger . 'Classes/Javascript.php');
require_once($pathToScriptmerger . 'Classes/ConditionalCommentPreserver.php');

/**
 * This class contains the output hooks that trigger the scriptmerger process
 */
class user_ScriptmergerOutputHook {
	/**
	 * holds the extension configuration
	 *
	 * @var array
	 */
	protected $extensionConfiguration = array();

	/**
	 * This method fetches and prepares the extension configuration.
	 *
	 * @return void
	 */
	protected function prepareExtensionConfiguration() {
		$this->extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['scriptmerger']);

		$tsSetup = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_scriptmerger.'];
		if (is_array($tsSetup)) {
			foreach ($tsSetup as $key => $value) {
				$this->extensionConfiguration[$key] = $value;
			}
		}

		// no compression allowed if content should be added inside the document
		if ($this->extensionConfiguration['css.']['addContentInDocument'] === '1') {
			$this->extensionConfiguration['css.']['compress.']['enable'] = '0';
		}

		if ($this->extensionConfiguration['javascript.']['addContentInDocument'] === '1') {
			$this->extensionConfiguration['javascript.']['compress.']['enable'] = '0';
		}

		// prepare ignore expressions
		if ($this->extensionConfiguration['css.']['minify.']['ignore'] !== '') {
			$this->extensionConfiguration['css.']['minify.']['ignore'] = '/.*(' .
				str_replace(',', '|', $this->extensionConfiguration['css.']['minify.']['ignore']) .
				').*/isU';
		}

		if ($this->extensionConfiguration['css.']['compress.']['ignore'] !== '') {
			$this->extensionConfiguration['css.']['compress.']['ignore'] = '/.*(' .
				str_replace(',', '|', $this->extensionConfiguration['css.']['compress.']['ignore']) .
				').*/isU';
		}

		if ($this->extensionConfiguration['css.']['merge.']['ignore'] !== '') {
			$this->extensionConfiguration['css.']['merge.']['ignore'] = '/.*(' .
				str_replace(',', '|', $this->extensionConfiguration['css.']['merge.']['ignore']) .
				').*/isU';
		}

		if ($this->extensionConfiguration['javascript.']['minify.']['ignore'] !== '') {
			$this->extensionConfiguration['javascript.']['minify.']['ignore'] = '/.*(' .
				str_replace(',', '|', $this->extensionConfiguration['javascript.']['minify.']['ignore']) .
				').*/isU';
		}

		if ($this->extensionConfiguration['javascript.']['compress.']['ignore'] !== '') {
			$this->extensionConfiguration['javascript.']['compress.']['ignore'] = '/.*(' .
				str_replace(',', '|', $this->extensionConfiguration['javascript.']['compress.']['ignore']) .
				').*/isU';
		}

		if ($this->extensionConfiguration['javascript.']['merge.']['ignore'] !== '') {
			$this->extensionConfiguration['javascript.']['merge.']['ignore'] = '/.*(' .
				str_replace(',', '|', $this->extensionConfiguration['javascript.']['merge.']['ignore']) .
				').*/isU';
		}
	}

	/**
	 * This hook is executed if the page contains *_INT objects! It's called always as the
	 * last hook before the final output. This isn't the case if you are using a
	 * static file cache like nc_staticfilecache.
	 *
	 * @return bool
	 */
	public function contentPostProcOutput() {
		/** @var $tsfe tslib_fe */
		$tsfe = $GLOBALS['TSFE'];
		if (!$tsfe->isINTincScript() || intval(t3lib_div::_GP('disableScriptmerger')) === 1) {
			return TRUE;
		}

		$this->prepareExtensionConfiguration();
		$this->process();
		return TRUE;
	}

	/**
	 * The hook is only executed if the page does not contains any *_INT objects. It's called
	 * always if the page was not already cached or on first hit!
	 *
	 * @return bool
	 */
	public function contentPostProcAll() {
		/** @var $tsfe tslib_fe */
		$tsfe = $GLOBALS['TSFE'];
		if ($tsfe->isINTincScript() || intval(t3lib_div::_GP('disableScriptmerger')) === 1) {
			return TRUE;
		}

		$this->prepareExtensionConfiguration();
		$this->process();
		return TRUE;
	}

	/**
	 * Contains the process logic of the whole plugin!
	 *
	 * @return void
	 */
	protected function process() {
		$javascriptEnabled = $this->extensionConfiguration['javascript.']['enable'] === '1';
		$cssEnabled = $this->extensionConfiguration['css.']['enable'] === '1';
		if ($cssEnabled || $javascriptEnabled) {
			/** @var ScriptmergerConditionalCommentPreserver $conditionalCommentPreserver */
			$conditionalCommentPreserver = t3lib_div::makeInstance('ScriptmergerConditionalCommentPreserver');
			$conditionalCommentPreserver->read();

			if ($cssEnabled) {
				/** @var ScriptmergerCss $cssProcessor */
				$cssProcessor = t3lib_div::makeInstance('ScriptmergerCss');
				$cssProcessor->injectExtensionConfiguration($this->extensionConfiguration);
				$cssProcessor->process();
			}

			if ($javascriptEnabled) {
				/** @var ScriptmergerJavascript $javascriptProcessor */
				$javascriptProcessor = t3lib_div::makeInstance('ScriptmergerJavascript');
				$javascriptProcessor->injectExtensionConfiguration($this->extensionConfiguration);
				$javascriptProcessor->process();
			}

			$conditionalCommentPreserver->writeBack();
		}

		if (is_array($this->extensionConfiguration['urlRegularExpressions.']) &&
			count($this->extensionConfiguration['urlRegularExpressions.'])
		) {
			$this->executeUserDefinedRegularExpressionsOnContent(
				$this->extensionConfiguration['urlRegularExpressions.']
			);
		}
	}

	/**
	 * Executes user defined regular expressions on the href/src urls for e.g. use an cookie-less asset domain.
	 *
	 * @param array $expressions
	 * @return void
	 */
	protected function executeUserDefinedRegularExpressionsOnContent($expressions) {
		foreach ($expressions as $index => $expression) {
			if (strpos($index, '.') !== FALSE || !isset($expressions[$index . '.']['replacement'])) {
				continue;
			}

			$replacement = trim($expressions[$index . '.']['replacement']);
			if ($replacement === '') {
				continue;
			}

			if ($expressions[$index . '.']['useWholeContent'] === '1') {
				$GLOBALS['TSFE']->content = preg_replace($expression, $replacement, $GLOBALS['TSFE']->content);
			} else {
				$userExpression = trim(str_replace('/', '\/', $expression));
				$expression = '/<(?:img|link|style|script|meta|input)' .
					'(?=[^>]+?(?:content|href|src)="(' . $userExpression . ')")[^>]*?>/iU';
				preg_match_all($expression, $GLOBALS['TSFE']->content, $matches);
				if (is_array($matches[1])) {
					foreach ($matches[1] as $match) {
						if (trim($match) === '') {
							continue;
						}

						$changedUrl = preg_replace('/' . $userExpression . '/is', $replacement, $match);
						$GLOBALS['TSFE']->content = str_replace($match, $changedUrl, $GLOBALS['TSFE']->content);
					}
				}
			}
		}
	}
}

?>