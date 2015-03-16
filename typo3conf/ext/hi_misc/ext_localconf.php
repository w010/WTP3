<?php
if (!defined ("TYPO3_MODE"))    die ("Access denied.");

$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',tx_himisc_cssclass';
$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',keywords,description'; // to add SEO stuff from TS

$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['determineId-PostProc'][] = 'EXT:hi_misc/class.tx_himisc_div.php:tx_himisc_div->storeTemplaVoilaTO';
