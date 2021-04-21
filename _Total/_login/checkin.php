<?php

// ---------------------------------------------------------------------------
// This code is part of Total.HTM Easy. See: http://exa.run/Total.HTM 
// ---------------------------------------------------------------------------

header('Content-type: text/plain');
define('MODE_STRICT', 1);
error_reporting(E_ALL | E_STRICT); 
set_time_limit(1);  // can only run 1 seconds. Thats far too much anyway.

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

$GLOBALS['fp_log'] = $GLOBALS['config']['dir_sec_l'].'log_'.date('ymd').'_login_checkin_php.y7';

ini_set('log_errors', 1);
ini_set('error_log', $GLOBALS['fp_log']);


// --------------------------------------------- from here on logging shall work

$c = file_get_contents('php://input');
if(strlen($c) < 47)                      // looks like: {a: 685266623, m: "total"} 
{
    $d = json_decode($c, true);

    if(isset($d['a']) && isset($d['m']))
    {
        $m = $d['m'];
        if($m != "app" && $m != "total" && $m != "ro") die();

        $a = (int)($d['a']);
        $b = rand(1000, 100000000);

        session_start();
        $_SESSION = array();  // This is potent: An overwrite of all and any server-side session-variables

        $_SESSION['sec'] = array("a" => $a,
                                "b" => $b,
                                "mode" => $m);
        echo $b;
        usleep(rand(10000, 20000));  // thats between 10ms and 20ms 
        die();
    }
}

if(strlen($c) > 1023)
{
    $c = substr($c, 0, 1023); // Just forget the stupid rest of it
}

err("ALARM: Stupid Cracker! Params: ".$c);
die();

