<?php

// ---------------------------------------------------------------------------
// This code is part of Total.HTM Easy. See: http://exa.run/Total.HTM 
// ---------------------------------------------------------------------------

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

$GLOBALS['fp_log'] = $GLOBALS['config']['dir_sec_l'].'log_'.date('ymd').'_login_checkin_al_php.y7';

ini_set('log_errors', 1);
ini_set('error_log', $fp_log);


// --------------------------------------------- from here on logging shall work

$d = json_decode(file_get_contents('php://input'), true);

if(!isset($d['m'])) die("{\"err\":\"m1\"}");
$m = $d['m'];
if($m != "app" && $m != "total") die("{\"err\":\"m2\"}");
if(!isset($d["x"]) || strlen($d["x"]) < 20) die("{\"err\":\"x1\"}"); 

session_start();

if($m != $_SESSION['sec']['mode']) die("{\"err\":\"m3\"}");

if(!isset($_SESSION['al']) || strlen($_SESSION['al']) != 64) die("{\"err\":\"al1\"}");
if(!isset($_SESSION['al-p']) || strlen($_SESSION['al-p']) != 64) die("{\"err\":\"al2\"}");
if(!isset($_SESSION['al-ts'])) die("{\"err\":\"al-ts1\"}");

if(gettimeofday()["sec"] - $_SESSION['al-ts'] > (3600 * 72)) die("{\"err\":\"timeout\"}");  // 3 days to get over a weekend

$GLOBALS["indexOk"] = 815;
include("util_sec.php");

$p0 = $_SESSION['al-p'];

$hmac_check = base64_encode(hash_hmac('sha256', $_SESSION['al'], $p0, true));

if($hmac_check != $d['x']) die("{\"err\":\"al3\"}");

$_SESSION['al'] = hash('sha256', rand(1,2000000000)."forget brute force".rand(1,2000000000), false); // auto-login-ticket
$_SESSION['al-p'] = hash('sha256', rand(1,2000000000)."brute force will fail".rand(1,2000000000), false); // auto-login-p
$_SESSION['al-s'] = hash('sha256', rand(1,2000000000)."no worries".rand(1,2000000000), false); // to get script
$_SESSION['al-ts'] = gettimeofday()["sec"];

$_SESSION['p'] = md5(rand(1,200000000)."some random text between randoms".rand(1,200000000)); 
$_SESSION['sec']['auth_cc'] = rand(100,10000);
$_SESSION['sec']['tok'] = rand(100,1000000);
$_SESSION['sec']['uts'] = date("ymd");

$v = 1;
$is_app = 0;
if($_SESSION['sec']['mode'] == "app")
{
  @include('../_api/config_dont_touch.php');
  @include('../_api/api_version.php');
  $v = $GLOBALS['v'];
  $is_app = 1;

  $v_str = 'v'.$GLOBALS['v'].'_';
  $GLOBALS['config']['dir_sec_u'] .= $v_str;
  $GLOBALS['config']['dir_sec_d'] .= $v_str;
}

$user_hash = $_SESSION['user'];

$un = "";
$fp_un = $GLOBALS['config']['dir_sec_u']."_un_".$user_hash.".y7";
if(file_exists($fp_un)) $un = file_get_contents($fp_un);

$uy = 1;
$fp_uy = $GLOBALS['config']['dir_sec_u']."_uy_".$user_hash.".y7";
if(file_exists($fp_uy)) $uy = (int)(file_get_contents($fp_uy));

$arr = array('n' => $un,
    'y' => $uy,
    'p' => $_SESSION['p'],
    'cc' => $_SESSION['sec']['auth_cc'],
    'tok' => $_SESSION['sec']['tok'],
    'v' =>  $v,
    'uts' => $_SESSION['sec']['uts'],
    'al' => $_SESSION['al'],
    'al-p' => $_SESSION['al-p'],
    'al-s' => $_SESSION['al-s'],
    'al-ts' => $_SESSION['al-ts'],
    'uid' => $_SESSION['uid']); 

if($is_app)
{
    // TODO: Add App-HTM and -JS to result (all thats needed, as in login-ok-case)
    die("{\"err\":\"apfxx\"}");
}


$result = array();
$result['x'] = ENCRYPT($p0, json_encode($arr));

echo json_encode($result);

