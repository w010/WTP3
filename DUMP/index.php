<?php

//die('no access');


/**
 *  WTP DUMP/BACKUP TOOL FOR TYPO3 - wolo.pl '.' studio
 *  2013-2018
 */

// ! you should change default password !

//					  WARNING!   CRITICAL
//
// THIS SCRIPT IS FOR PRIVATE DEV USE ONLY, DO NOT PUT IT ON PUBLIC UNSECURED!
// IT DOES LOW LEVEL DB / FILESYSTEM OPERATIONS AND DOESN'T HAVE ANY USER-INPUT SECURITY CHECK.
// IF THIS IS YOUR SITE AND RUNNING IN PUBLIC/PRODUCTION ENVIRONMENT AND YOU ARE
// NOT SURE IF THIS FILE SHOULD BE HERE, PLEASE DELETE THIS SCRIPT IMMEDIATELY


define ('DUMP_VERSION', '2.0.0');
//
// dump / ..za....ka si tr lub o dw




error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_STRICT ^ E_DEPRECATED);

// try to use native path detection
@include('../typo3/sysext/core/Classes/Core/SystemEnvironmentBuilder.php');
if (class_exists('\TYPO3\CMS\Core\Core\SystemEnvironmentBuilder'))   {
	class SystemEnvironmentBuilder extends \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder	{
		public static function run($relativePathPart = '') {
			self::defineBaseConstants();
			self::definePaths($relativePathPart);
		}
	}
	SystemEnvironmentBuilder::run('/DUMP');
	define('PATH_dump', PATH_site . 'DUMP/');
}
// do the classic init
else	{
	define('PATH_thisScript', str_replace('//', '/', str_replace('\\', '/',
		(PHP_SAPI == 'fpm-fcgi' || PHP_SAPI == 'cgi' || PHP_SAPI == 'isapi' || PHP_SAPI == 'cgi-fcgi') &&
		($_SERVER['ORIG_PATH_TRANSLATED'] ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) ?
		($_SERVER['ORIG_PATH_TRANSLATED'] ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) :
		($_SERVER['ORIG_SCRIPT_FILENAME'] ? $_SERVER['ORIG_SCRIPT_FILENAME'] : $_SERVER['SCRIPT_FILENAME']))));
	define('PATH_dump', dirname(PATH_thisScript).'/');
	define('PATH_site', realpath(PATH_dump.'../').'/');
}

// in some projects needed to be defined
define('TYPO3_MODE', 'BE');


// branch 6.x <=
if (file_exists(PATH_site.'typo3conf/LocalConfiguration.php'))	{
	$GLOBALS['TYPO3_CONF_VARS'] = include_once(PATH_site.'typo3conf/LocalConfiguration.php');
	define('VERSION', 6);

	// may be used sometimes in AdditionalConfiguration
	@include_once(PATH_site.'typo3/sysext/core/Classes/Utility/ExtensionManagementUtility.php');
	@include_once(PATH_site.'typo3/sysext/core/Classes/Utility/GeneralUtility.php');
	include_once(PATH_site.'typo3conf/AdditionalConfiguration.php');
	// for q3i (is already included in AdditionalConfiguration)
	//if (file_exists(PATH_site.'typo3conf/AdditionalConfiguration_host.php'))
	//	include_once(PATH_site.'typo3conf/AdditionalConfiguration_host.php');
}
// branch 4.x
else if (file_exists(PATH_site.'typo3conf/localconf.php'))	{
	include_once(PATH_site . 'typo3conf/localconf.php');
	define('VERSION', 4);

	// compatibility with old typo conf
	$GLOBALS['TYPO3_CONF_VARS']['DB']['username'] = $typo_db_username;
	$GLOBALS['TYPO3_CONF_VARS']['DB']['password'] = $typo_db_password;
	$GLOBALS['TYPO3_CONF_VARS']['DB']['host'] = $typo_db_host;
	$GLOBALS['TYPO3_CONF_VARS']['DB']['database'] = $typo_db;
}



if (!defined('DEV'))				define('DEV',   false);
if (!defined('LOCAL'))			  define('LOCAL', false);

if (!defined('TYPO3_CONTEXT'))	  define('TYPO3_CONTEXT',	 getenv('TYPO3_CONTEXT'));
if (!defined('INSTANCE_CONTEXT'))   define('INSTANCE_CONTEXT',  getenv('INSTANCE_CONTEXT'));


// default options for this script operation
$optionsDefault = [
	// script only displays generated command line, but doesn't exec it
	'dontExecCommands' => 0,

	// exec commands, but don't show them on PUB
	'dontShowCommands' => 0,

	// default tables for "Dump with omit" action, if not specified
	'defaultOmitTables' => ['index_rel', 'sys_log', 'sys_history', 'index_fulltext', 'sys_refindex', 'index_words', 'tx_extensionmanager_domain_model_extension'],

	// default project name is generated from subdomain, but it's not always ok
	'defaultProjectName' => '',

	// adds docker exec on container to command line
	'docker' => false,

	// if docker=true, container name must be specified
	'docker_containerSql' => ''
];

// custom options may be included to override
$optionsCustom = [];
include_once ('conf.php');
$options = array_replace($optionsDefault, $optionsCustom);






$Dump = new Dump();
$Dump->main();


class Dump  {

	// define some global variables. all are public, because this script is for private use and no need to control such things.

	public $options = [];
	private $dbConf = [];

	// html content to display before form
	public $contentHeader = '';

	// message/error to show
	public $messages = [];

	// stored commands to show and/or execute
	public $cmds = [];

	// input values
	public $projectName = '';
	public $projectVersion = '';
	public $dbFilename = '';
	public $action = '';

	// directories as variables
	public $PATH_site;
	public $PATH_dump;

	// docker exec cmd prefix
	private $dockerContainerCmd = [];


	function __construct() {
		global $options;
		$this->options = &$options;
		$this->dbConf = &$GLOBALS['TYPO3_CONF_VARS']['DB'];

		$this->PATH_site = PATH_site;
		$this->PATH_dump = PATH_dump;

		// if docker is used, docker exec CONTAINER must be prepended before mysqldump etc.
		if ($this->options['docker'])   {
			$this->dockerContainerCmd['sql'] = "docker exec {$this->options['docker_containerSql']} ";
			$this->dockerContainerCmd['php'] = "docker exec {$this->options['docker_containerPhp']} ";
		}
	}

	function main() {

		if (!$this->dbConf['username'] || !$this->dbConf['password'] || !$this->dbConf['host'] || !$this->dbConf['database'])
			$this->msg('CHECK DATABASE CONFIG. Looks like authorization data is missed. Check '.(VERSION === 4 ? 'localconf' : 'LocalConfiguration'), 'error');

		$this->addEnvironmentMessage();

		// set input values
		$this->projectName = $_POST['name'];
		$this->projectVersion = $_POST['v'];
		$this->action = $_POST['action'];
		$this->dbFilename = $_POST['dbFilename'] ? $_POST['dbFilename'] : $_POST['dbFilenameSel'];

		// predicted project name, taken from domain name or conf (only when not submitted, on first run)
		if (!$_POST['submit']  &&  !$_POST['name']) {
			list($this->projectName) = preg_split('@\.@', $_SERVER['HTTP_HOST']);
			if ($this->options['defaultProjectName'])
				$this->projectName = $this->options['defaultProjectName'];
		}

		// add some header system & conf informations
		$this->contentHeader .= '<p>- database: <span class="info"><b>' . $this->dbConf['database'] . '</b></span></p>';
		if ($this->options['docker'])   {
			$this->contentHeader .= '<p>- docker sql: <span class="info"><b>' . $this->options['docker_containerSql'] . '</b></span></p>';
			$this->contentHeader .= '<p>- docker php: <span class="info"><b>' . $this->options['docker_containerPhp'] . '</b></span></p>';
		}
		$this->contentHeader .= '<p>- version detected: <span class="info"><b>' . (defined('TYPO3_version') ? TYPO3_version : (defined('VERSION') ? VERSION : 'none')) . '</b></span></p>';

		// check if action is given if submitted
		if (!$_POST['submit']  ||  ($_POST['submit'] && !$this->paramsRequiredPass(['action' => $this->action])))
			return;

		$this->contentHeader .= '<h4><b>ACTION CALLED:</b> <span class="info"><b>' . $this->action . '</b></span></h4>';



		// RUN
		switch ($this->action) {

			// IMPORT DATABASE
			case 'databaseimport':
				$this->action_databaseimport();
				break;

			// DUMP DATABASE
			case 'databasedump':
				$this->action_databasedump();
				break;

			// PACK FILESYSTEM
			case 'filesystempack':
				$this->action_filesystempack();
				break;

			// DUMP ALL
			case 'dump_all':
				$this->action_databasedump(true);
				$this->action_filesystempack(true);
				break;

			// BACKUP FILESYSTEM
			case 'backup':
				$this->action_backup();
				break;

			// UPDATE DOMAINS
			case 'update_domains':
				$this->action_updatedomains();
				break;
		}
	}


	// ACTIONS

	/**
	 * DATABASE IMPORT
	 */
	private function action_databaseimport()	{
		if (!$this->paramsRequiredPass(['dbFilename' => $this->dbFilename]))
			return;

		// docker: sprawdzic, dziala tak: docker exec -i berglanddev_mysql_1 mysql --user=www_devel --password="www_devel" --database=project_app < kultur-bergischesland-v06-dev.sql  (przynajmniej bedac w tym katalogu) [ jest problem z kodowaniem! sprawdzic to, zobaczyc, jak wywolac to z utf ]

		// slashes even on windows have to be unix-style in execute source
        $query = "SET NAMES 'utf8'; SET collation_connection = 'utf8_unicode_ci'; SET collation_database = 'utf8_unicode_ci'; SET collation_server = 'utf8_unicode_ci'; source " . str_replace('\\', '/', $this->PATH_dump . $this->dbFilename);
		$this->exec_control($this->dockerContainerCmd['php'] . "mysql --batch --quick --host={$this->dbConf['host']} --user={$this->dbConf['username']} --password=\"{$this->dbConf['password']}\" --database={$this->dbConf['database']}  --execute=\"{$query}\"");
	}


	/**
	 * DATABASE DUMP
	 */
	private function action_databasedump($all = false)	{

		if (!$this->paramsRequiredPass(['projectName' => $this->projectName, 'projectVersion' => $this->projectVersion]))
		//if (!$this->paramsRequiredPass(['projectName' => $this->projectName, 'projectVersion' => $this->projectVersion, 'omitTables' => $_POST['omitTables']]))
			return;
		$dumpFilename = str_replace(' ', '_', $this->projectName);
		$omitTables = array_diff(explode(chr(10), $_POST['omitTables']), ['', "\r", "\n"]);



		// todo: czy PATH_dump jest potrzebny, czy dziala pod linuxem, pod win, w dockerze
		// todo: for docker try  /var/www/htdocs/_docker/dump_local_db.sh
		// old full dump
		//$this->exec_control($this->dockerCmdPart['sql'] . "mysqldump --complete-insert --add-drop-table --no-create-db --skip-set-charset --quick --lock-tables --add-locks --default-character-set=utf8 --host={$this->dbConf['host']} --user={$this->dbConf['username']} --password=\"{$this->dbConf['password']}\"  {$this->dbConf['database']}  >  \"{$this->PATH_dump}{$this->projectName}-v{$this->projectVersion}.sql\"; ");


		// example - ignored without data but structure:
		// mysqldump -u user -p db --ignore-table=ignoredtable1 --ignore-table=ignoredtable2 > structure.sql;  mysqldump -u user -p --no-data db ignoredtable1 ignoredtable2 >> structure.sql;

		$ignoredTablesPart = '';
		$dumpOnlyStructureQuery = '';

		if ($_POST['omitTablesIncludeInQuery']  &&  $omitTables  &&  !$all) {
			$ignoredTablesPart = chr(10) . "--ignore-table={$this->dbConf['database']}."
				. implode ("--ignore-table={$this->dbConf['database']}.", $omitTables);

			$dumpOnlyStructureQuery = chr(10) . chr(10)
                . ' ; '     // end previous command only if needed
				. $this->dockerContainerCmd['sql'] . "mysqldump --complete-insert --add-drop-table --no-create-db --skip-set-charset --quick --lock-tables --add-locks --default-character-set=utf8 --host={$this->dbConf['host']} --user={$this->dbConf['username']} --password=\"{$this->dbConf['password']}\"  {$this->dbConf['database']}  "
				. " --no-data "
				. chr(10) . implode(' ', $omitTables)
				. "  >>  \"{$this->PATH_dump}{$this->projectName}-v{$this->projectVersion}.sql\" ";
		}

		// dziala na dockerze (wywolany recznie)
		// na win teoretycznie powinno tez ze stream output
		$cmd = $this->dockerContainerCmd['sql'] . "mysqldump --complete-insert --add-drop-table --no-create-db --skip-set-charset --quick --lock-tables --add-locks --default-character-set=utf8 --host={$this->dbConf['host']} --user={$this->dbConf['username']} --password=\"{$this->dbConf['password']}\"  {$this->dbConf['database']}  "
			. $ignoredTablesPart
			. "  >  \"{$this->PATH_dump}{$this->projectName}-v{$this->projectVersion}.sql\" "
			. $dumpOnlyStructureQuery;


		$this->exec_control($cmd);


		// TAR

		// todo: ktory dziala pod linuxem, ktory pod win, ktory w dockerze?
		// dziala na dockerze (wywolany recznie)
		$this->exec_control("cd \"{$this->PATH_dump}\";  tar  -zcf  \"{$dumpFilename}-v{$this->projectVersion}.sql.tgz\"  \"{$this->projectName}-v{$this->projectVersion}.sql\" ");
		//$this->exec_control("tar  -zcf  \"{$this->PATH_dump}{$dumpFilename}-v{$this->projectVersion}.sql.tgz\"  \"{$this->PATH_dump}{$this->projectName}-v{$this->projectVersion}.sql\" ");
		//$this->exec_control("tar -C \"{$this->PATH_dump}\" -zcf  {$dumpFilename}-v{$this->projectVersion}.sql.tgz  {$this->projectName}-v{$this->projectVersion}.sql");
		//$this->exec_control("tar -C \"{$this->PATH_dump}\" -zcf ./{$dumpFilename}-v{$this->projectVersion}.sql.tgz  {$this->projectName}-v{$this->projectVersion}.sql");


		// display download link
		$this->cmds[] = "<br><a href=\"{$dumpFilename}-v{$this->projectVersion}.sql.tgz\">{$dumpFilename}-v{$this->projectVersion}.sql.tgz</a><br>";
	}


	/**
	 * FILESYSTEM PACK
	 */
	private function action_filesystempack($all = false)	{

		if (!$this->paramsRequiredPass(['projectName' => $this->projectName, 'projectVersion' => $this->projectVersion]))
			return;

		$dumpFilename = str_replace(' ', '_', $this->projectName);

		// todo: ktory dziala pod linuxem, ktory pod win, ktory w dockerze?
		// dziala w dockerze prawidlowo
		$cmd = "tar -C \"{$this->PATH_site}\" -zcf {$dumpFilename}-v{$this->projectVersion}.tgz ";

		if (!$all  &&  !$_POST['omitTablesIncludeInQuery']) {

			$included = $_POST['filenameSelectionInclude'];
			$excluded = $_POST['filenameSelectionExclude'];

			if ($included)
				$cmd .= implode(' ', $included) . ' ';
			else
				$cmd .= ' .  --exclude="DUMP"';

			if ($excluded)  {
				$cmd .= ' --exclude="';
				$cmd .= implode('" --exclude="', $excluded);
				$cmd .= '" ';
			}
		}
		else	{
			$cmd .= ' .  --exclude="DUMP"';
		}

		// todo: deprecation preg powinien byc wykluczany tutaj, a nie tylko z widoku

		$this->exec_control($cmd);
		/* $this->exec_control ('tar -zcf '.{$this->PATH_site}.'DUMP/'.$dumpFilename.'-v'.$this->projectVersion.'.tgz ./../* --exclude="typo3temp" --exclude="DUMP" --exclude="uploads" -exclude="typo3_src-*"  '); */

		// display download link
		$this->cmds[] = "<br><a href=\"{$dumpFilename}-v{$this->projectVersion}.sql.tgz\">{$dumpFilename}-v{$this->projectVersion}.tgz</a><br>";
	}


	/**
	 * BACKUP
	 */
	private function action_backup()   {
		$backupDir = "{$this->PATH_site}../{$this->projectName}_backup_".time()."/";
		$this->exec_control("mkdir $backupDir");
		$this->exec_control("cp -R $this->PATH_site/* $backupDir");
	}


	/**
	 * UPDATE DOMAINS
	 */
	private function action_updatedomains() {

		if (!$this->paramsRequiredPass(['domainsFrom' => $_POST['domainsFrom'], 'domainsTo' => $_POST['domainsTo']]))
			return;

		//	UPDATE sys_domain SET domainName = 'blueprint.localhost' WHERE domainName = 'blue-print.de';
		//	UPDATE pages SET url = REPLACE(url, 'blue-print.de', 'blueprint.localhost') WHERE url LIKE 'blue-print.de%';


		$query = '';

        $domains_from = array_diff(explode(chr(10), $_POST['domainsFrom']), ['', "\r"]);
        $domains_to = array_diff(explode(chr(10), $_POST['domainsTo']), ['', "\r"]);


        if (count($domains_from) !== count($domains_to)) {
	        $this->msg('error: number of items in <b>domainsFrom</b> is different than <b>domainsTo</b>  ', 'error');
	        return;
        }

        foreach($domains_from as $i => $from)   {
            $from = trim($from);
            $to = trim($domains_to[$i]);
            $query .= "UPDATE sys_domain SET domainName = '{$to}' WHERE domainName = '{$from}'; ";
            $query .= "UPDATE pages SET url = REPLACE(url, '{$from}', '{$to}') WHERE url LIKE '{$from}/%'; ";
        }

        // todo: sprawdzic!

		$this->exec_control($this->dockerContainerCmd['php'] . "mysql --batch --quick --host={$this->dbConf['host']} --user={$this->dbConf['username']} --password=\"{$this->dbConf['password']}\" --database={$this->dbConf['database']}  --execute=\"{$query}\"");
	}



	/* exec shell command */
	private function exec_control($cmd, $saveCmd = true) {
		global $options;
		if ($options['dontExecCommands'])
			$this->msg('command not executed - exec is disabled - @see option dontExecCommands', 'info');
		elseif ($_POST['dontExec'])
			$this->msg('(command not executed)', 'info');
		else
			// exec($cmd, $output, $return);
			system($cmd, $output);

		var_dump($output);
		// var_dump($return);

		if ($this->options['docker'])
			$this->msg('running on docker - cmd probably didn\'t run. execute manually', 'info');

		if ($saveCmd)
			$this->cmds[] = $cmd;
	}


	// INPUT CONTROL

	/* control required params for action */
	function paramsRequiredPass($params) {
		$pass = true;
		foreach ($params as $param => $value) {
			if (!$value) {
				$this->msg('error: <b>' . $param . '</b> must be set. ', 'error', $param);
				$pass = false;
			}
		}
		return $pass;
	}

	/* check if error of given field exists */
	function checkFieldError($param)	{
		if (array_key_exists($param, $this->messages))
			return true;
	}

	/* prints error class on form input, if present */
	function checkFieldError_printClass($param)	{
		if ($this->checkFieldError($param))
			return ' class="error"';
	}


	// INFO DISPLAY

	private function addEnvironmentMessage()	{
		$environment = '';
		if (defined('DEV') && DEV)		  $environment = 'DEV';
		if (defined('LOCAL') && LOCAL)	  $environment = 'LOCAL';
		if (getenv('TYPO3_CONTEXT') == 'Development')   $environment = 'Development';
		if (getenv('TYPO3_CONTEXT') && !$environment)   $environment = getenv('TYPO3_CONTEXT');
		if (!$environment)							  $environment = 'PUBLIC';
		if ($environment == 'Production' || $environment == 'PUBLIC')
			$environment = '<span class="error">'.$environment.' !!!!</span>';
		else
			$environment = '<span class="info">'.$environment.'</span>';
		if (INSTANCE_CONTEXT)
			$environment .= ' - instance: <span class="info"><b>' . INSTANCE_CONTEXT . '</b></span>';
		$this->contentHeader .= '<h4>running on ' . $environment . '</h4>';
	}

	/**
	 * add message/notice
	 * @param string $message
	 * @param string $class - class for notice p, may be error or info
	 * @param string $index - index can be checked in tag markup, to indicate error class in form element
	 */
	function msg($message, $class = '', $index = '') {
		//$this->msg .= $message . '<br>';
		if ($index)	 $this->messages[$index] = [$message, $class];
		else			$this->messages[] = [$message, $class];
	}

	/* display generated messages with class if set */
	function displayMessages()  {
		$content = '';
		foreach ($this->messages as $message) {
			$content .= '<p'.($message[1] ? ' class="'.$message[1].'">':'>') . $message[0] . '</p>';
		}
		return $content;
	}


	// CONFIG

	/* returns omit tables value to display in textarea */
	function getOmitTables()	{
		if ($_POST['omitTables'])
			return htmlspecialchars($_POST['omitTables']);
		return implode(chr(10), $this->options['defaultOmitTables']);
	}


	// SYSTEM

	/* list files */
	function getFilesFromDirectory($dir = 'DUMP', $ext = 'sql') {
		$files = glob('*.' . $ext);
		if (!is_array($files))  $files = [];
		return $files;
	}

	/* list directories */
	function getFilesAndDirectories($dir = '', $skip = []) {
		$files = scandir ($this->PATH_site . $dir);
		if (!is_array($files))  $files = [];
		foreach ($files as $file) {
			if (preg_match("/(^(([\.]){1,2})$|(\.(svn|git|md))|(Thumbs\.db|\.DS_STORE))$|^deprecation_/iu", $file, $match))
				$skip[] = $file;
		}
		$files = array_diff($files, $skip);
		return $files;
	}

	/* returns dump filenames, to see what version number should be next */
	function getExistingDumpsFilenames()	{
		return chr(10) . implode (chr(10), $this->getFilesFromDirectory());
	}


	// TEMPLATING

	/* typo3-like standard replace marker method */
	function substituteMarkerArray($subject, $markerArray)	{
		return str_replace(array_keys($markerArray), array_values($markerArray), $subject);
	}
}

?>
<html>
<head>
	<title>DUMP - <?php print $_SERVER['HTTP_HOST']; ?></title>
	<style type='text/css'>
		label	{clear: both;}
		label span	{float: left; width: 260px; cursor: pointer;}
		label input	{float: left; cursor: pointer;}
		.actions li + label  {float: left;}
		.clear	{clear: both;}
		.error	{color: #d00;}
		.info	{color: #282; font-style: italic;}
		ul  {list-style: none; float: left; margin-top: 0; padding: 0;}
		.actions ul   {background: #eee; padding: 20px;}
		.actions li > label:hover   {color: darkorange;}
		.actions li.active > label  {color: #03d;}
		.radio-sub-options  {display: none; padding: 10px 20px 20px; background: gainsboro; margin: 10px 0 0 20px;}
		input[type=radio]:checked + .radio-sub-options  {display: block;}
		.selector-tables textarea   {overflow-wrap: normal;}
		.tooltip	{cursor: help;}
		footer	  {font-size: 80%;}
		pre		 {line-height: 1em; white-space: pre-wrap;}
		header p	{margin: 8px 0;}
		.to-left	{float: left;}
		.hidden	 {display: none !important;}
		.indent	 {margin-left: 40px;}
	</style>
</head>
<body>

	<header>
		<h2>WTP Dump tool</h2>

		<pre>
PATH_site = <?php  print PATH_site;  ?><br>
PATH_dump = <?php  print PATH_dump;  ?>
		</pre>

		<?php   print $Dump->contentHeader;	 ?>
	</header>


	<?php  if ($Dump->cmds  &&  !$options['dontShowCommands']) print "<p>- commands:</p><p><pre>".implode('<br><br>', $Dump->cmds)."</pre></p>";  ?>
	<?php  print $Dump->displayMessages();  ?>
	<form action='' method='post'>
		<div class="actions">
			<h3<?php print $Dump->checkFieldError('action'); ?>>action:</h3>
			<div class="indent">
				<ul>
					<?php
						$actions = [
							[
								'label' => 'Database - IMPORT &DoubleLeftArrow;',
								'name' => 'databaseimport',
								'options' => [
									[
										'label' => "<label for='dbFilenameSel'>Database filename:</label>",
										'valid' => !$Dump->checkFieldError('dbFilename'),
										'class' => 'selector-database',
										'content' => function() use ($Dump) {
											$code = "<select name='dbFilenameSel' id='dbFilenameSel'>
														<option></option>";
											foreach ($Dump->getFilesFromDirectory() as $file)
												$code .= "<option>".$file.'</option>';
											return $code . "</select>
												or type: <input name='dbFilename' type='text'>";
										}
									]
								],
							],
							[
								'label' => 'Database - EXPORT &DoubleRightArrow;',
								'name' => 'databasedump',
								'options' => [
									[
										'label' => 'Write tablenames to omit:',
										'class' => 'selector-tables',
										'content' => function() use ($Dump) {
											return "<textarea name='omitTables' cols='32' rows='6'>" . $Dump->getOmitTables() . "</textarea>"
												. "<br><label><input type='checkbox' name='omitTablesIncludeInQuery'".($_POST['omitTablesIncludeInQuery'] ? " checked" : '').">Omit these tables (export only structure)</label>";
										}
									],
								],
							],
							[
								'label' => 'FILESYSTEM pack',
								'name' => 'filesystempack',
								'options' => [
									[
										'label' => "<label for='dbFilenameSel'>Database filename:</label>",
										'valid' => !$Dump->checkFieldError('filenameSel'),
										'class' => 'selector-pickfiles',
										'content' => function() use ($Dump) {

											$code = "
												<div class='to-left'>
													INCLUDE<br>
													<select name='filenameSelectionInclude[]' id='filenameSelectionInclude' size='8' multiple>";

														foreach ($Dump->getFilesAndDirectories('', ['DUMP']) as $dir)  {
															$included = ['typo3conf'];
															$selected = in_array($dir, $included) ? ' selected' : '';
															$code .= "<option{$selected}>".$dir.'</option>';
														}

											$code .= "</select>
												</div>
												<div class='to-left'>
													EXCLUDE<br>
													<select name='filenameSelectionExclude[]' id='filenameSelectionExclude' size='8' multiple>";

														// todo: make this configurable from options (per project)
														$listSubdirsOf = ['fileadmin', 'typo3conf'];
														foreach ($listSubdirsOf as $dir)
															foreach ($Dump->getFilesAndDirectories($dir) as $subdir) {
																$subdirPath = $dir . '/' . $subdir;
																$excluded = ['fileadmin/content', 'fileadmin/_processed_', 'fileadmin/_temp_', 'fileadmin/user_upload', 'typo3conf/AdditionalConfiguration_host.php'];
																$selected = in_array($subdirPath, $excluded) ? ' selected' : '';
																$code .= "<option{$selected}>" . $subdirPath . '</option>';
															}

											return $code . "</select>
												</div>
												<br><label><input type='checkbox' name='omitTablesIncludeInQuery'".($_POST['omitTablesIncludeInQuery'] ? " checked" : '').">Ignore selection and pack all</label>
												<div class='clear'></div>";
										}
									]
								],
							],
							[
								'label' => 'Dump ALL',
								'name' => 'dump_all',
							],
							[
								'label' => 'Backup project dir',
								'name' => 'backup',
							],

							[
								'label' => 'Domains update',
								'name' => 'update_domains',
								'options' => [
									[
										'label' => "<label for='domains-from'>Update domains in database</label>",
										'valid' => !$Dump->checkFieldError('domainsFrom'),
										'class' => 'selector-pickfiles',
										'content' => function() use ($Dump) {
											$code = "
                                                <i>Replace domains in sys_domain records and pages external urls</i>
                                                <br><br>
												<div>
													Domains FROM:<br>
													<textarea name='domainsFrom' id='domainsFrom' rows='5' cols='50'></textarea>
												</div>
											    <div>
											        Domains TO:<br>
													<textarea name='domainsTo' id='domainsTo' rows='5' cols='50'></textarea>
											    </div>";
											return $code;
										}
									]
								],
							],
						];

						$actionTmpl = "
							<li class='###ACTION_CLASS###'>
								<label for='action_###ACTION_NAME###'>
									<span>###ACTION_LABEL###</span>
								</label>
								<input name='action' id='action_###ACTION_NAME###' type='radio' value='###ACTION_NAME###'###ACTION_CHECKED###>
								<div class='radio-sub-options clear ###ACTION_OPTIONS_CLASS###'>
									###OPTIONS###
								</div>
								<br class='clear'>
							</li>";
						$actionOptionTmpl = "
									<div class='option ###OPTION_CLASS### ###OPTION_VALID_CLASS###'>
										<h3>###OPTION_LABEL###</h3>
										###OPTIONS_CONTENT###
									</div>
									";

						$codeActions = '';
						foreach ($actions as $action)   {

							$codeOptions = '';
							if (is_array($action['options']))
							foreach ($action['options'] as $option)   {

								$codeOptions .= $Dump->substituteMarkerArray(
									$actionOptionTmpl,
									[
										'###OPTION_LABEL###' => $option['label'],
										'###OPTION_CLASS###' => $option['class'],
										'###OPTION_VALID_CLASS###' => defined($option['valid']) && !$option['valid'] ? ' error' : '',
										'###OPTIONS_CONTENT###' => $option['content'](),
									]
								);
							}

							$codeActions .= $Dump->substituteMarkerArray(
								$actionTmpl,
								[
									'###ACTION_NAME###' => $action['name'],
									'###ACTION_LABEL###' => $action['label'],
									'###OPTIONS###' => $codeOptions,
									'###ACTION_OPTIONS_CLASS###' => $codeOptions ? '' : ' hidden',
									'###ACTION_CLASS###' => $_POST['action'] === $action['name'] ? 'active' : '',
									'###ACTION_CHECKED###' => $_POST['action'] === $action['name'] ? ' checked' : '',
								]
							);
						}

						print $codeActions;
					?>

				</ul>
			</div>
			<div class="clear"></div>
		</div>

		<div>
			<h3<?php print $Dump->checkFieldError_printClass('projectName'); ?>><label for='name'>project / dir name:</label></h3>
			<div class="indent">
				<input name='name' id='name' type='text' size='30' value='<?php print htmlspecialchars($Dump->projectName); ?>'>
			</div>
		</div>
		<div>
			<h3<?php print $Dump->checkFieldError_printClass('projectVersion'); ?>><label for='v'>version:</label></h3>
			<div class="indent">
				<input name='v' id='v' type='text' size='10' value='<?php print htmlspecialchars($Dump->projectVersion); ?>'>
				<i class="tooltip" title="Existing dumps: <?php print $Dump->getExistingDumpsFilenames(); ?>">[ i ]</i>
			</div>
		</div>

		<br>
		<div>
			<label>
				<input name='dontExec' id='dontExec' type='checkbox'<?php print ($_POST['dontExec'] ? ' checked' : ''); ?>> don't exec generated command
			</label>
		</div>

		<br>
		<input type='submit' name='submit' value=' go! '>
	</form>
	<br>
	<br>

	<footer>
		<i>WTP DUMP/BACKUP TOOL FOR TYPO3<br> v<?php print DUMP_VERSION; ?> - wolo.pl '.' studio 2013-2018</i>
	</footer>

</body>
</html>