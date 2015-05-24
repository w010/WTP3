<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
t3lib_extMgm::addStaticFile($_EXTKEY,'static/ods_plaintext/', 'Plaintext for direct_mail with templavoila');
?>