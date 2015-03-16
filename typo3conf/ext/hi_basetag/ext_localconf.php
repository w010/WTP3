<?php

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc'][] = 'EXT:hi_basetag/class.tx_hibasetag.php:tx_hibasetag->setConfig';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][] = 'EXT:hi_basetag/class.tx_hibasetag.php:tx_hibasetag->setBaseUrl';
