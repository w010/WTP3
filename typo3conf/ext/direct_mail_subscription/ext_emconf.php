<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "direct_mail_subscription".
 *
 * Auto generated 01-04-2015 05:31
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Direct Mail Subscription',
	'description' => 'Adds a plugin for subscription to direct mail newsletters (collecting subscriptions in the tt_address table). wtp wolo mod, search for "wolo mod"',
	'category' => 'plugin',
	'version' => '992.0.2',
	'state' => 'stable',
	'uploadfolder' => false,
	'createDirs' => '',
	'clearcacheonload' => true,
	'author' => 'Ivan Kartolo',
	'author_email' => 'ivan.kartolo@dkd.de',
	'author_company' => 'dkd Internet Service GmbH',
	'constraints' => 
	array (
		'depends' => 
		array (
			'tt_address' => '',
			'typo3' => '4.5.0-6.2.99',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);

