<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "devlog".
 *
 * Auto generated 27-08-2015 05:21
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Developer Log',
	'description' => 'The Developer log extension provides development logging/debugging functionality for usage of t3lib_div::devlog() and a BE module to browse the logs.',
	'category' => 'misc',
	'version' => '2.11.4w',
	'state' => 'stable',
	'uploadfolder' => true,
	'createDirs' => '',
	'clearcacheonload' => false,
	'author' => 'Rene Fritz, Francois Suter',
	'author_email' => 'typo3@cobweb.ch',
	'author_company' => '',
	'constraints' => 
	array (
		'depends' => 
		array (
			'typo3' => '4.5.0-7.9.99',
			'cms' => '',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);

