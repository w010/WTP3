<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "direct_mail_userfunc".
 *
 * Auto generated 10-06-2015 04:15
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'External Providers for Direct Mail',
	'description' => 'Adds support for external providers to Direct Mail. This extension extends the types of recipient lists handled by Direct Mail with an entry for parameterized custom lists. These custom lists are prepared by user functions and may easily reuse your own business logic.',
	'category' => 'module',
	'version' => '1.4.3',
	'state' => 'stable',
	'uploadfolder' => true,
	'createDirs' => '',
	'clearcacheonload' => true,
	'author' => 'Xavier Perseguers (Causal)',
	'author_email' => 'xavier@causal.ch',
	'author_company' => 'Causal SÃ rl',
	'constraints' => 
	array (
		'depends' => 
		array (
			'php' => '5.3.7-5.5.99',
			'typo3' => '4.5.0-6.2.99',
			'direct_mail' => '3.1.0-4.0.99',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);

