<?php

// WTP DUMP/BACKUP TOOL - wolo.pl '.' studio
// v0.5
// 2015
//
// dump / zapalniczka73

// WARNING! THIS SCRIPT IS FOR PRIVATE DEV USE ONLY, DO NOT PUT IT ON PUBLIC!
// IT DOES LOW LEVEL FILE OPERATIONS AND HAS NO USER-INPUT SECURITY CHECK.
// IF THIS IS YOUR SITE AND RUNNING IN PUBLIC/PRODUCTION ENVIRONMENT AND YOU NOT SURE IF THE FILE HAS TO BE HERE, PLEASE DELETE IT

error_reporting(E_ALL ^ E_NOTICE);

define('PATH_thisScript', str_replace('//', '/', str_replace('\\', '/',
	(PHP_SAPI == 'fpm-fcgi' || PHP_SAPI == 'cgi' || PHP_SAPI == 'isapi' || PHP_SAPI == 'cgi-fcgi') &&
	($_SERVER['ORIG_PATH_TRANSLATED'] ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) ?
	($_SERVER['ORIG_PATH_TRANSLATED'] ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) :
	($_SERVER['ORIG_SCRIPT_FILENAME'] ? $_SERVER['ORIG_SCRIPT_FILENAME'] : $_SERVER['SCRIPT_FILENAME']))));

define('PATH_site', dirname(PATH_thisScript).'/');
define('PATH_work', realpath(PATH_site.'../').'/');
define('TYPO3_MODE', 'BE');


if (file_exists(PATH_work.'typo3conf/localConfiguration.php'))	{
	define('V6', true);
	$GLOBALS['TYPO3_CONF_VARS'] = require_once(PATH_work.'typo3conf/LocalConfiguration.php');
	include_once(PATH_work.'typo3conf/AdditionalConfiguration.php');
	//$localconf = array_replace_recursive($localconf, $additionalLocalconf);
	//var_dump($GLOBALS['TYPO3_CONF_VARS']);
	$typo_db_username = $GLOBALS['TYPO3_CONF_VARS']['DB']['username'];
	$typo_db_password = $GLOBALS['TYPO3_CONF_VARS']['DB']['password'];
	$typo_db_host = $GLOBALS['TYPO3_CONF_VARS']['DB']['host'];
	$typo_db = $GLOBALS['TYPO3_CONF_VARS']['DB']['database'];
}
else /**/
	require_once(PATH_work.'typo3conf/localconf.php');
	//require_once(PATH_work.'typo3conf/localConfiguration.php');

/*var_dump(LOCAL);
var_dump(DEV);
var_dump(DEV2);
var_dump(DEV47);
var_dump(DEV62);*/

print '<h5>running on '.(DEV62?'DEV62':(DEV2?'DEV2':(DEV?'DEV':'PUBLIC !!!!'))).(LOCAL?' LOCAL':'').'</h5>';

//if (!DEV)
//	die('access denied');

/*		$typo_db = 'baza1298_petrol';
		$typo_db_host = '1298.m.tld.pl';
		$typo_db_host = 'localhost';
		$typo_db_password = '';
		$typo_db_username = 'admin1298_petrol';
*/
// message/error to show
$msg = '';
// stored commands executed to debug
$cmds = '';

main();


function main()	{

	global $typo_db_username;
	global $typo_db_password;
	global $typo_db_host;
	global $typo_db;

	if (!$typo_db_username || !$typo_db_password || !$typo_db_host || !$typo_db)
		msg('CHECK DATABASE CONFIG. Looks like some authorization data is missed. Check localconf');

	// to tu chyba jest zbedne
	//global $msg;

	$projectName = 		$_GET['name'];
	$projectVersion = 	$_GET['v'];
	$action = 			$_GET['action'];
	$dbFilename = 		$_GET['dbFilename']?$_GET['dbFilename']:$_GET['dbFilenameSel'];

	//$projectDirectory = $projectName?$projectName:'public_html';

	/*if (!is_dir(PATH_work.$projectDirectory.'/'))	{
		msg('error: no "'.$projectDirectory.'" directory found in PATH_work (not running from root?)');
		return;
	}*/

	print "database name: ".$typo_db."<br>";

	if (!paramsRequiredPass(array('action'=>$action))) return;


	// BACKUP FILESYSTEM
	if ($action == 'backup')    {
		$backupDir = PATH_work.'../'.$projectName.'_backup'.time().'/';
		exec('mkdir '.$backupDir);
		echr('mkdir '.$backupDir);
		exec('cp -R '.PATH_work.'/* '.$backupDir);
		echr('cp -R '.PATH_work.'/* '.$backupDir);
	}

	// EMPTY specified backup folder. use this, if you know why to delete a backup.
	if ($action == 'backupclean')    {
		if (!is_set($_GET['backupdir']))
			die('error: backup remove - directory namepart not given');
		$backupDir = 'backup'.$_GET['backupdir'];
		die('backup remove disabled');
	//	exec ('rm -R '.$backupDir.'/*');
		echr ('rm -R '.$backupDir.'/* [DISABLED FOR NOW]');
	}

	// DUMP EXPORT SITE FILES AND DATABASE
	if ($action == 'dump' || $action == 'quickdump' || $action == 'databasedump')	{
		if (!paramsRequiredPass(array('projectName'=>$projectName, 'projectVersion'=>$projectVersion)))	return;
		$dumpFilename = str_replace(' ', '_', $projectName);
		
		if ($action == 'quickdump')	{
			exec ('tar -zcf '.PATH_work.'DUMP/'.$dumpFilename.'-v'.$projectVersion.'.tgz ./../* --exclude="typo3temp" --exclude="DUMP" --exclude="uploads" -exclude="typo3_src-*"  ');
			echr ('tar -zcf '.PATH_work.'DUMP/'.$dumpFilename.'-v'.$projectVersion.'.tgz ./../* --exclude="typo3temp" --exclude="DUMP" --exclude="uploads" -exclude="typo3_src-*"  ');
		} else if ($action == 'databasedump')	{
			// do nothing, don't dump filesystem
		} else	{
			exec ('tar -zcf '.PATH_work.'DUMP/'.$dumpFilename.'-v'.$projectVersion.'.tgz ./../* --exclude="DUMP" ');
			echr ('tar -zcf '.PATH_work.'DUMP/'.$dumpFilename.'-v'.$projectVersion.'.tgz ./../* --exclude="DUMP" ');
		}

		exec ('mysqldump --complete-insert --add-drop-table --no-create-db --skip-set-charset --quick --lock-tables --add-locks --default-character-set=utf8 --host='.$typo_db_host.' --user='.$typo_db_username.' --password="'.$typo_db_password.'" '.$typo_db.' > "'.PATH_work.'DUMP/'.$projectName.'-v'.$projectVersion.'.sql"');
		echr ('mysqldump --complete-insert --add-drop-table --no-create-db --skip-set-charset --quick --lock-tables --add-locks --default-character-set=utf8 --host='.$typo_db_host.' --user='.$typo_db_username.' --password="'.$typo_db_password.'" '.$typo_db.' > "'.PATH_work.'DUMP/'.$projectName.'-v'.$projectVersion.'.sql"');

		exec ('tar -zcf '.PATH_work.'DUMP/'.$dumpFilename.'-v'.$projectVersion.'.sql.tgz '.PATH_work.'DUMP/'.$projectName.'-v'.$projectVersion.'.sql');
		echr ('tar -zcf '.PATH_work.'DUMP/'.$dumpFilename.'-v'.$projectVersion.'.sql.tgz '.PATH_work.'DUMP/'.$projectName.'-v'.$projectVersion.'.sql');

		echr( '<a href="'.$dumpFilename.'-v'.$projectVersion.'.sql.tgz">'.$dumpFilename.'-v'.$projectVersion.'.sql.tgz</a>' );
	}

	// IMPORT DATABASE
	if ($action == 'importdb')	{
		if (!paramsRequiredPass(array('dbFilename'=>$dbFilename))) return;
		//if (!$dbFilename)	$dbFilename = 'wtpack_dump.sql';
		exec ('mysql --batch --quick --host='.$typo_db_host.' --user='.$typo_db_username.' --password="'.$typo_db_password.'" --database='.$typo_db.' --execute="SET NAMES \'utf8\'; SET collation_connection = \'utf8_unicode_ci\'; SET collation_database = \'utf8_unicode_ci\'; SET collation_server = \'utf8_unicode_ci\'; source '.PATH_work.'DUMP/'.$dbFilename.';"');
		echr ('mysql --batch --quick --host='.$typo_db_host.' --user='.$typo_db_username.' --password="'.$typo_db_password.'" --database='.$typo_db.' --execute="SET NAMES \'utf8\'; SET collation_connection = \'utf8_unicode_ci\'; SET collation_database = \'utf8_unicode_ci\'; SET collation_server = \'utf8_unicode_ci\'; source '.PATH_work.'DUMP/'.$dbFilename.';"');
	}
}

/* put message/notice */
function msg($message)	{
	global $msg;
	$msg = $message;
}

/* dump called command */
function echr($str)	{
	/*print $str .'<br>';
	return;*/
	global $cmds;
	$cmds .= $str.'<br>';
}

/* list files */
function getFilesFromDirectory($dir = 'DUMP', $ext = 'sql')	{
	return glob('*.'.$ext);
}

/* control required params for action */
function paramsRequiredPass($params)	{
	$message = '';
	foreach ($params as $param => $value)	{
		if (!$value)
			$message .= 'error: <b>'.$param.'</b> must be given. ';
	}
	if ($message)	{
		msg($message);
		return false;
	}
	return true;
}


?>
<html>
<head>
<style type='text/css'>
label	{display: block; clear: both;}
label span	{float: left; width: 140px;}
label input	{float: left;}
.clear	{clear: both;}
.error	{color: red;}
.actions label:hover	{color: red;}
</style>
</head>
<body>
<p>PATH_site = <?php print PATH_site; ?></p>
<p>PATH_work = <?php print PATH_work; ?></p>
<?php if ($cmds) print "<p>commands executed:</p><p>".$cmds."</p>"; ?>
<p class='error'><?php print $msg; ?></p>
<form action='' method='get'>
	<div class="actions"><h3>action:</h3>
		<label> <span>dump WHOLE SITE</span><input name='action' type='radio' value='dump'> <br class='clear'></label>
		<label> <span>quickdump (exclude temp,uploads,src)</span><input name='action' type='radio' value='quickdump'> <br class='clear'></label>
		<label> <span>export/dump DB SQL</span><input name='action' type='radio' value='databasedump'> <br class='clear'></label>
		<label> <span>import DB SQL</span> <input name='action' type='radio' value='importdb'> <br class='clear'></label>
		<label> <span>backup filesystem</span> <input name='action' type='radio' value='backup'> <br class='clear'></label>
		<label> <span>backup clean</span> <input name='action' type='radio' value='backupclean'> <br class='clear'></label>
	</div>
	<div>
		<h3>project / dir name:</h3>
		<input name='name' type='text'>
	</div>
	<div>
		<h3>version:</h3>
		<input name='v' type='text'>
	</div>
	<div>
		<h3>database filename:</h3>
		<select name='dbFilenameSel'>
			<option></option>
		<?php
		foreach ((array)getFilesFromDirectory() as $file)
			print "<option>".$file.'</option>';
		?>
		</select>
		<input name='dbFilename' type='text'>
	</div>
	
	<br>
	<input type='submit' value='go!'>
</form>
</body>
</html>