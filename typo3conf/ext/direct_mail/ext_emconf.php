<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "direct_mail".
 *
 * Auto generated 01-04-2015 05:30
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Direct Mail',
	'description' => 'Advanced Direct Mail/Newsletter mailer system with sophisticated options for personalization of emails including response statistics.',
	'category' => 'module',
	'version' => '4.0.1',
	'state' => 'stable',
	'uploadfolder' => false,
	'createDirs' => '',
	'clearcacheonload' => true,
	'author' => 'Ivan Kartolo',
	'author_email' => 'ivan.kartolo@dkd.de',
	'author_company' => 'd.k.d Internet Service GmbH',
	'constraints' => 
	array (
		'depends' => 
		array (
			'cms' => '',
			'tt_address' => '',
			'php' => '5.3.0',
			'typo3' => '6.2.2-6.2.99',
		),
		'conflicts' => 
		array (
			'sr_direct_mail_ext' => '',
			'it_dmail_fix' => '',
			'plugin_mgm' => '',
			'direct_mail_123' => '',
		),
		'suggests' => 
		array (
		),
	),
);

