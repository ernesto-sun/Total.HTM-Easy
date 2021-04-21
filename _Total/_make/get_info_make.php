<?php

header('Content-type: application/json');
define('MODE_STRICT', 1);
error_reporting(E_ALL | E_STRICT); 
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


// --------------------------------------------------------------
function err($msg)
{
    @error_log(TIMESTAMP().': ERROR: '.$msg);
    @error_log('AGENT: '.AGENT_INFO_STR());
    $ts = MS() - $GLOBALS['sts'];
    @error_log('Script Runtime: '.$ts.'ms');
    @session_destroy();
    usleep(rand(100000, 300000));  // thats between 100ms and 300ms 
    if($GLOBALS['debug']) echo 'ERROR: ',$msg;
    die();
}


$ok_come_from_api=1;
include('../config_dont_touch.php');

$GLOBALS['fp_log'] = $GLOBALS['config']['dir_sec_l'].'log_'.date('ymd').'_make_get_info_make_php.y7';

ini_set('log_errors', 1);
ini_set('error_log', $fp_log);


// --------------------------------------------- from here on logging shall work

include("../version.php");

$d = json_decode(file_get_contents('php://input'), true);

$GLOBALS["indexOk"] = 815;
include("../_login/util_sec.php");
if(!AUTH($d , $GLOBALS["v"])) err("Au!");
if($_SESSION['sec']['mode'] != 'total') err("Invalid Login Mode!");

// --------
$dir_make = "../_make/";

$dir_v = $GLOBALS['config']['dir_sec_d']."make_v/";

$result = array();
$result['last_release'] = '';
$result['version'] = 0;
$result['last_release_ms'] = 0;

$fn_json_v = $dir_v."_v.json.y7";
if (!file_exists($fn_json_v))
{
    echo json_encode($result);
    die();    
}

$json_v = file_get_contents($fn_json_v);
$dv = json_decode($json_v, true);
if($dv == null) err('dvn');
if(!isset($dv["ts"])) err('ts');
if(!isset($dv["tss"])) err('tss');
if(!isset($dv["vi"])) err('vi');

$result = array();
$result['last_release_ms'] = MS() - $dv["ts"];
$result['last_release'] = $dv["tss"];
$result['version'] = $dv['vi'];
echo json_encode($result);
die();
