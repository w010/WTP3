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
 * This class contains the basic stuff required by both processors, css and javascript.
 */
abstract class ScriptmergerBase {
	/**
	 * directories for minified, compressed and merged files
	 *
	 * @var array
	 */
	protected $tempDirectories = '';

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->tempDirectories = array(
			'main' => PATH_site . 'typo3temp/scriptmerger/',
			'temp' => PATH_site . 'typo3temp/scriptmerger/temp/',
			'minified' => PATH_site . 'typo3temp/scriptmerger/uncompressed/',
			'compressed' => PATH_site . 'typo3temp/scriptmerger/compressed/',
			'merged' => PATH_site . 'typo3temp/scriptmerger/uncompressed/'
		);

		foreach ($this->tempDirectories as $directory) {
			if (!is_dir($directory)) {
				t3lib_div::mkdir($directory);
			}
		}
	}

	/**
	 * Injects the extension configuration
	 *
	 * @param array $configuration
	 * @return void
	 */
	public function injectExtensionConfiguration(array $configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * Controller for the dedicated script type processing
	 *
	 * @return void
	 */
	abstract function process();

	/**
	 * Gets a file from an external resource (e.g. http://) and caches them
	 *
	 * @param string $source Source address
	 * @param boolean $returnContent
	 * @return string cache file or content (depends on the parameter)
	 */
	protected function getExternalFile($source, $returnContent = FALSE) {
		$filename = basename($source);
		$hash = md5($source);
		$cacheFile = $this->tempDirectories['temp'] . $filename . '-' . $hash;
		$externalFileCacheLifetime = intval($this->configuration['externalFileCacheLifetime']);
		$cacheLifetime = ($externalFileCacheLifetime > 0) ? $externalFileCacheLifetime : 3600;

		// check the age of the cache file (also fails with non-existent file)
		$content = '';
		if ((int) @filemtime($cacheFile) <= ($GLOBALS['EXEC_TIME'] - $cacheLifetime)) {
			if ($source{0} === '/' && $source{1} === '/') {
				$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === TRUE ? 'https:' : 'http:';
				$source = $protocol . $source;
			}

			$content = t3lib_div::getURL($source);
			if ($content !== FALSE) {
				$this->writeFile($cacheFile, $content);
			} else {
				$cacheFile = '';
			}
		} elseif ($returnContent) {
			$content = file_get_contents($cacheFile);
		}

		$returnValue = $cacheFile;
		if ($returnContent) {
			$returnValue = $content;
		}

		return $returnValue;
	}

	/**
	 * Writes $content to the file $file
	 *
	 * @param string $file file path to write to
	 * @param string $content Content to write
	 * @return boolean TRUE if the file was successfully opened and written to.
	 */
	protected function writeFile($file, $content) {
		$result = t3lib_div::writeFile($file, $content);

		// hook here for other file system operations like syncing to other servers etc.
		$hooks = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['scriptmerger']['writeFilePostHook'];
		if (is_array($hooks)) {
			foreach ($hooks as $classReference) {
				$hookObject = t3lib_div::getUserObj($classReference);
				$hookObject->writeFilePostHook($file, $content, $this);
			}
		}

		return $result;
	}
}

?>