<?php
// WTP DUMP/BACKUP TOOL FOR TYPO3 - wolo.pl '.' studio
// 2013-2018

defined ('TYPO3_MODE') or die ('Access denied.');



// options for this script operation
$optionsCustom = [
	// script only displays generated command line, but doesn't exec it
	'dontExecCommands' => getenv('TYPO3_CONTEXT') === 'Development' ? 0 : 0,

	// exec commands, but don't show them on LIVE
	'dontShowCommands' => getenv('TYPO3_CONTEXT') === 'Production' ? 1 : 0,

	//'defaultOmitTables' => [],

	'defaultProjectName' => 'myproject',

	'docker' => INSTANCE_CONTEXT == 'local-docker' ? 'true' : false,
	'docker_containerSql' => INSTANCE_CONTEXT == 'local-docker' ?  'myproject_mysql_1'  : '',
	'docker_containerPhp' => INSTANCE_CONTEXT == 'local-docker' ?  'myproject_php_1'  : '',
	
	'fetchFiles_defaultSourceDomain' => 'http://mydomain.com',
];



// here you may hardcode db access data if not using typo3 config

/*
$GLOBALS['TYPO3_CONF_VARS']['DB']['username'] = 'user';
$GLOBALS['TYPO3_CONF_VARS']['DB']['password'] = 'pass';
$GLOBALS['TYPO3_CONF_VARS']['DB']['host'] = 'localhost';
$GLOBALS['TYPO3_CONF_VARS']['DB']['database'] = 'database';
*/
