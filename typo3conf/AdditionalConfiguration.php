<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

/**
 * WTP2 v2.62.1
 * Wolo TYPO3 Pack 2
 * 2015 wolo.pl '.' studio
 *
 * (version convention explain: vA.B.C means: A=2 because WTP2,  B=nn is like T3-branch n.n,  C is WTP version itself.)
 */

// global statics define, do not touch

define('LOCAL', intval(
	preg_match('/(localhost)/', $_SERVER['HTTP_HOST'])
));

define('DEV', intval(
	preg_match('/(dev\.)/', $_SERVER['HTTP_HOST'])
	||	LOCAL
));

putenv("DEV=".intval(DEV)); // make them available from TS: [globalVar= ENV:DEV=1]
putenv("LOCAL=".intval(LOCAL));

if (LOCAL)  {
	//$GLOBALS['TYPO3_CONF_VARS']['DB']['username'] = '';
	//$GLOBALS['TYPO3_CONF_VARS']['DB']['password'] = '';
	//$GLOBALS['TYPO3_CONF_VARS']['DB']['database'] = '';
	//$GLOBALS['TYPO3_CONF_VARS']['DB']['host'] = 'localhost';
}
if (CEDRIS)  {
	//$GLOBALS['TYPO3_CONF_VARS']['DB']['username'] = 'admin833_cedris';
	//$GLOBALS['TYPO3_CONF_VARS']['DB']['password'] = '7Diov}FnaU';
	//$GLOBALS['TYPO3_CONF_VARS']['DB']['database'] = 'baza833_cedris';
}



$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] = (DEV?'DEV ':'') . (LOCAL?'LOCAL ':'') . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = DEV?true:false;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] = -1;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['exceptionalErrors'] = E_ALL ^ E_STRICT ^ E_NOTICE ^ E_WARNING ^ E_USER_ERROR ^ E_USER_NOTICE ^ E_USER_WARNING;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['sqlDebug'] = DEV ? 1 : 0;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = NO_DEBUG?'':implode(',', array(
	'all' => DEV?'*':'',
	'wolo-pzn' => '85.221.134.155',
));



$GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword'] = DEV ? md5('123') : md5('KD84nx(B34899c3C#@h&');

if (LOCAL)	{
	$GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] = 'D:\xampp\ImageMagick-6.8.1-9\\';
	$GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw'] = 'D:\xampp\ImageMagick-6.8.1-9\\';
	$GLOBALS['TYPO3_CONF_VARS']['GFX']['im_version_5'] = '6';
	
	// tu zmienilem, jakby logowanie be lub fe padlo to cos z tym katalogiem 
	$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rsaauth'] = 'a:1:{s:18:"temporaryDirectory";s:13:"D:\xampp\tmp";}';
	
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['binSetup'] = 'openssl=d:\xampp\apache\bin\openssl.exe';
}

$GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = DEV && NO_DEBUG==FALSE ? TRUE : FALSE;
$GLOBALS['TYPO3_CONF_VARS']['FE']['compressionDebugInfo'] = DEV ? true : false;
$GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime'] = (LOCAL?7*24:3) * 3600;

	// not used in 6?
	$GLOBALS['TYPO3_CONF_VARS']['EXT']['extCache'] = $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] ? 0 : 3;
$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['beko_debugster'] = serialize(array('ip_mask'=>DEV?(NO_DEBUG?'':'*'):$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'], 'steps_back'=>3, 'useDevIpMask' => DEV?1:1));

// enable memcache in 6.2
/*
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pages']['backend'] = 'TYPO3\\CMS\\Core\\Cache\\Backend\\MemcachedBackend';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pages']['options'] = array('servers' => array('localhost:11211'),);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pagesection']['backend'] = 'TYPO3\\CMS\\Core\\Cache\\Backend\\MemcachedBackend';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pagesection']['options'] = array('servers' => array('localhost:11211'),);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_hash']['backend'] = 'TYPO3\\CMS\\Core\\Cache\\Backend\\MemcachedBackend';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_hash']['options'] = array('servers' => array('localhost:11211'),);
/**/

/**
 * user func for ts condition for env static check
 * @param $env
 * @return bool
 */
/*function user_envCheck($env)    {
	if ( constant($env) )    {
			return TRUE;
	}

	return FALSE;
}
*/
?>