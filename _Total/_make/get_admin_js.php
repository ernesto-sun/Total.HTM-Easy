<?php

ob_start();

header('Content-type: text/javascript');
error_reporting(E_ALL | E_STRICT); 
define('MODE_STRICT', 1);
set_time_limit(1);  // can only run 1 seconds. Thats much anyway.

$GLOBALS['debug'] = 0;

ini_set('display_errors', $GLOBALS['debug']);   

// ------------------------------------------------------
function MS()
{
	return intval(microtime(true) * 1000);	
}

$GLOBALS['sts'] = MS();

// ------------------------------------------------------
function TIMESTAMP()
{
	return date('Y-m-d H:i:s').'.'.sprintf('%03d', (MS() % 1000));	
}

// --------------------------------------------------------------
function AGENT_INFO()
{
    $info = array();
    $info['ip'] = $_SERVER['REMOTE_ADDR'];
    $info['host'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $info['agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '?';
    $info['lang'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '?';  // gives info about the language of the user
    return $info; 
}

// --------------------------------------------------------------
function AGENT_INFO_STR()
{
    return var_export(AGENT_INFO(), true);
}

// --------------
function err($msg)
{
    @error_log(TIMESTAMP().': ERROR: '.$msg);
    @error_log('AGENT: '.AGENT_INFO_STR());
    $ts = MS() - $GLOBALS['sts'];
    @error_log('Script Runtime: '.$ts.'ms');
    @session_destroy();
    usleep(rand(100000, 300000));  // thats between 100ms and 300ms 
    ob_end_clean();
    if($GLOBALS['debug']) echo 'ERROR: ', $msg;
    die();
}


$ok_come_from_api = 1;
require('../config_dont_touch.php');

$GLOBALS['fp_log'] = $GLOBALS['config']['dir_sec_l'].'log_'.date('ymd').'_make_get_admin_js_php.y7';

ini_set('log_errors', 1);
ini_set('error_log', $fp_log);

//-------------------------------------------------
// ---------------------------- from here on logging shall work
//-------------------------------------------------


include("../version.php");
$ok_come_from_api = 1;
include("../config_dont_touch.php");

$d = json_decode(file_get_contents('php://input'), true);

$GLOBALS["indexOk"] = 815;
include("../_login/util_sec.php");
if(!AUTH($d , $GLOBALS["v"])) die("Au!");
if($_SESSION['sec']['mode'] != 'total') die("Invalid Login Mode!");

// ---------

echo "_h.CE('link').A({type:'text/css',rel:'stylesheet',href: _xr_ut + '_make/font_fa/fontawesome.css'});";
echo "_h.CE('link').A({type:'text/css',rel:'stylesheet',href: _xr_ut + 'css_lazy/admin.css'});";

echo file_get_contents('../js/_sys/admin_lang.js');
echo file_get_contents('../js/_sys/admin_util.js');
echo file_get_contents('../js/_sys/admin.js');

usleep(rand(10000, 50000));  // thats between 10ms and 50ms 
ob_end_flush();
die();
