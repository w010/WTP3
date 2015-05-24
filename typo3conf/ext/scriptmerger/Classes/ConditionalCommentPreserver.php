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

/**
 * This class contains the basic stuff required for preserving conditional comments
 */
class ScriptmergerConditionalCommentPreserver {
	/**
	 * holds the conditional comments
	 *
	 * @var array
	 */
	protected $conditionalComments = array();

	/**
	 * Callback function to replace conditional comments with placeholders
	 *
	 * @param array $hits
	 * @return string
	 */
	protected function save($hits) {
		$this->conditionalComments[] = $hits[0];
		return '###conditionalComment' . (count($this->conditionalComments) - 1) . '###';
	}

	/**
	 * Callback function to restore placeholders for conditional comments
	 *
	 * @param array $hits
	 * @return string
	 */
	protected function restore($hits) {
		$results = array();
		preg_match('/\d+/is', $hits[0], $results);
		$result = '';
		if (count($results) > 0) {
			$result = $this->conditionalComments[$results[0]];
		}
		return $result;
	}

	/**
	 * This method parses the output content and saves any found conditional comments
	 * into the "conditionalComments" class property. The output content is cleaned
	 * up of the found results.
	 *
	 * @return void
	 */
	public function read() {
		$pattern = '/<!--\[if.+?<!\[endif\]-->/is';
		$GLOBALS['TSFE']->content = preg_replace_callback(
			$pattern,
			array($this, 'save'),
			$GLOBALS['TSFE']->content
		);
	}

	/**
	 * This method writes the conditional comments back into the final output content.
	 *
	 * @return void
	 */
	public function writeBack() {
		$pattern = '/###conditionalComment\d+###/is';
		$GLOBALS['TSFE']->content = preg_replace_callback(
			$pattern,
			array($this, 'restore'),
			$GLOBALS['TSFE']->content
		);
	}
}

?>