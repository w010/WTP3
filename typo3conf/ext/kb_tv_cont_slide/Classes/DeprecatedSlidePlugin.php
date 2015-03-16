<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2014 Bernhard Kraft (kraftb@think-open.at)
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
 * Deprecated pi1-style plugin class for the 'kb_tv_cont_slide' extension.
 *
 * $Id$
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

class tx_kbtvcontslide_pi1 extends thinkopen_at\KbTvContSlide\SlideController {

	/**
	 * The constructor is used for marking "tx_kbtvcontslide_pi1" as deprecated
	 *
	 * @deprecated Calling of "tx_kbtvcontslide_pi1->main" is deprecated. Replace it with "thinkopen_at\KbTvContSlide\SlideController->main". This migration class will get replaced by a stub in version 0.5.1 and get removed in version 0.5.2 of kb_tv_cont_slide.
	 */
	public function __construct() {
		\TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();
		// throw new Exception('kb_tv_cont_slide: Configuration Error. Using "tx_kbtvcontslide_pi1" is deprecated. Edit your TemplaVoila/Flexform DS-XML files to call "thinkopen_at\KbTvContSlide\SlideController->main" instead of "tx_kbtvcontslide_pi1->main"');
	}

}

