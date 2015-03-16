<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "extension_builder".
 *
 * Auto generated 05-03-2015 07:20
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Extension Builder',
	'description' => 'The Extension Builder helps you build and manage your Extbase based TYPO3 extensions.',
	'category' => 'module',
	'version' => '6.2.0',
	'state' => 'beta',
	'uploadfolder' => true,
	'createDirs' => 'uploads/tx_extensionbuilder/backups',
	'clearcacheonload' => false,
	'author' => 'Nico de Haen',
	'author_email' => 'mail@ndh-websolutions.de',
	'author_company' => '',
	'constraints' => 
	array (
		'depends' => 
		array (
			'typo3' => '6.1.0-6.2.99',
		),
		'suggests' => 
		array (
			'phpunit' => '',
		),
		'conflicts' => 
		array (
		),
	),
);

