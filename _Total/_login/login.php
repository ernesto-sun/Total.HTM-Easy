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

    $ip = preg_replace('/[\W]/', '_', $_SERVER['REMOTE_ADDR']);
    $fp = $GLOBALS['config']['dir_sec_l'].'_ip_ERR_LOGIN_cc_'.$ip.'__'.date('ymd').'.y7';
    $cc = 0;
    if(file_exists($fp))
    {
      $cc = (int)file_get_contents($fp);
    }
    $cc++;
    file_put_contents($fp, ''.$cc);
    if($cc > 10)
    {
      // TODO Lock IP after 10 failed attempts today
    }
    die();
}


$ok_come_from_api=1;
include('../config_dont_touch.php');

$GLOBALS['fp_log'] = $GLOBALS['config']['dir_sec_l'].'log_'.date('ymd').'_login_php.y7';

ini_set('log_errors', 1);
ini_set('error_log', $fp_log);


$ok = 0;

$d = json_decode(file_get_contents('php://input'), true);  // TODO: Check if input size seems valid before reading... 

if(isset($d['a']) && isset($d['b']) && isset($d['r']) && isset($d['m']))
{
  $a = (int)($d['a']);
  $b = $d['b'];  // this must be the hash of SESSION[b]

  session_start();

  if(isset($_SESSION['sec']) && is_array($_SESSION['sec']))
  {
    if(isset($_SESSION['sec']['a']) && isset($_SESSION['sec']['b']))
    {
      $b2 = hash('sha256', $_SESSION['sec']['b'], false);
      if($a == $_SESSION['sec']['a'] && $b == $b2)  
      {
        if(isset($_SESSION['sec']['c']))
        {
            $ok=1;
        }
        else err("Failed: C-Check");
      }
      else err("Failed: HASH-Check");
    }
    else err("Failed: Session isset");
  }
  else err("Failed: Session array isset");
}

if(!$ok) err("Param Error");

$ok_come_from_login = 815;
$GLOBALS["indexOk"] = 815;

include("util_sec.php");

$result = array();

$ok = 0;

$c = $_SESSION['sec']['c'];
$fv = "f".$c;

if(("".$d['m']) == "I_confirm_to_be_an_authentic_person_with_respect_and_dignity")
{
  // step 0

  $_SESSION['rand1'] = $rand1 = (int) $d['r'];

  $a = $_SESSION['sec']['a'];
  $b0 = $_SESSION['sec']['b'];

  if( $rand1 > 0 && isset($d[$fv]))
  {
    $v_in = $d[$fv];

    $msg0 =  hash('sha256', "".$rand1.$a.$b0.$c, false);   
    
    $user_hash = DECRYPT($msg0, $v_in);

    $ok_step1 = 0;
    if($_SESSION['sec']['mode'] == "ro")
    {
      $ok_come_from_api=1;
      @include('../_api/config_dont_touch.php');

      if($GLOBALS['config']['ro_un'] == $user_hash)
      {
         $ok_step1 = 1;
      }
    }
    else if($_SESSION['sec']['mode'] == "app")
    {
      $ok_come_from_api=1;
      @include('../_api/config_dont_touch.php');
      @include('../_api/api_version.php');

      $v_str = 'v'.$GLOBALS['v'].'_';
      $GLOBALS['config']['dir_sec_u'] .= $v_str;

      $fp = $GLOBALS['config']['dir_sec_u']."_u_".$user_hash.".y7";

      if(file_exists($fp))
      {
        $ok_step1 = 1;
      }
    }
    else if($_SESSION['sec']['mode'] == "total")
    {
      $ok_come_from_api=1;
      @include('../config_dont_touch.php');

      $fp = $GLOBALS['config']['dir_sec_u']."_u_".$user_hash.".y7";

      if(file_exists($fp))
      {
        $ok_step1 = 1;
      }
    }

    if($ok_step1)
    {
      $_SESSION['msg0'] = $msg0;
      $_SESSION['user'] = $user_hash;
      $_SESSION['uok0'] = 1;

      $result['uid'] = $_SESSION['uid'] = rand(1,1000000);
      $ok=1;
    }
    else
    {
      err("LOGIN FAILED, tried with unknown user: ".$user_hash);
    }
  }
  else err("Failed: fv missing");
}
else if(("".$d['m']) == "My_privacy_is_my_natural_right")
{

  $_SESSION['rand2'] = $rand2 = (int)$d['r'];

  if(isset($d['u']) && isset($d[$fv]))
  {
    $v_in = $d[$fv];
    $uid = (int)$d['u'];
    $un = "";

    if($uid == $_SESSION['uid'])
    {
      if(isset($_SESSION['msg0']) && isset($_SESSION['user']) && isset($_SESSION['uok0']) && $_SESSION['uok0'] == 1)
      {
        $user_hash = $_SESSION['user'];

        $p = "";
        $ok_step2 = 0;
        $GLOBALS['v'] = 1;
        $uy = 1;

        $arr = array();

        if($_SESSION['sec']['mode'] == "ro")
        {
          @include('../_api/api_ro_version.php'); // here  $GLOBALS['v'] is set

          $ok_come_from_api=1;
          @include('../_api/config_dont_touch.php');

          if($GLOBALS['config']['ro_un'] == $user_hash)
          {
             $p = $GLOBALS['config']['ro_pwd'];
             $ok_step2 = 1;

             $un = "ro_user";
          }
        }
        else if($_SESSION['sec']['mode'] == "app")
        {
          $ok_come_from_api=1;
          @include('../_api/config_dont_touch.php');
          @include('../_api/api_version.php');

          $v_str = 'v'.$GLOBALS['v'].'_';
          $GLOBALS['config']['dir_sec_u'] .= $v_str;

          $fp = $GLOBALS['config']['dir_sec_u']."_u_".$user_hash.".y7";

          $un = "";
          $fp_un = $GLOBALS['config']['dir_sec_u']."_un_".$user_hash.".y7";
          if(file_exists($fp_un)) $un = file_get_contents($fp_un);

          $fp_uy = $GLOBALS['config']['dir_sec_u']."_uy_".$user_hash.".y7";
          if(file_exists($fp_uy)) $uy = (int)(file_get_contents($fp_uy));

          $fp_is_ok = $GLOBALS['config']['dir_sec_u']."_uok_".$user_hash.".y7";

          if(file_exists($fp) && file_exists($fp_is_ok))
          {
            $p = file_get_contents($fp);
            $ok_step2 = 1;

            $GLOBALS['config']['dir_sec_d'] .= $v_str;

            $fp_htm = $GLOBALS['config']['dir_sec_d']."app_htm_".$uy.".y7";
            if(file_exists($fp_htm)) $arr['html'] = file_get_contents($fp_htm);

            $fp_js = $GLOBALS['config']['dir_sec_d']."app_js_".$uy.".y7";
            if(file_exists($fp_js)) $arr['json'] = json_decode(file_get_contents($fp_js), true);

            $fp_htm_menu = $GLOBALS['config']['dir_sec_d']."app_menu_htm.y7";
            if(file_exists($fp_htm_menu)) $arr['html-menu'] = file_get_contents($fp_htm_menu);

            $fp_preload = $GLOBALS['config']['dir_sec_d']."app_preload_priority.y7";  // Non priority is done at get_app_preload.php
            if(file_exists($fp_preload))
            {
               $arr['preload'] = json_decode(file_get_contents($fp_preload), true);
               foreach($arr['preload'] as &$work_preload)
               {
                 $tn = $work_preload[2];
                 $n = $work_preload[7];
                 if(substr($n,0,4) == "com-")
                 {
                   $fp_cache_v = $GLOBALS['config']['dir_sec_d'].'CACHE_com_'.$tn.'_vt_v.y7';
                   $vt = file_get_contents($fp_cache_v);
                   //$work_preload[10] = $vt; 
                   $fp_cache = $GLOBALS['config']['dir_sec_d'].'CACHE_com_'.$tn.'_vt'.$vt.'.y7';
                   $work_preload[11] = json_decode(file_get_contents($fp_cache), true);
                 }
                 else if(substr($n,0,6) == "table-")
                 {
                   $fp_cache_v = $GLOBALS['config']['dir_sec_d'].'CACHE_table_'.$tn.'_vt_v.y7';
                   $vt = file_get_contents($fp_cache_v);
                   //$work_preload[10] = $vt; 
                   $fp_cache = $GLOBALS['config']['dir_sec_d'].'CACHE_table_'.$tn.'_vt'.$vt.'.y7';
                   $work_preload[11] = json_decode(file_get_contents($fp_cache), true);
                 }
               }
            }

            include('../version.php');
            $arr['vtotal'] = $GLOBALS['v'];

            // ---------- now, set a entry to ro, so that the login gets into Database

            // [id,0,tn,cmd,status,m,p,n,com,x,v,d]   // 341 is CMD_LOGIN
            $data = get_client_info();
            $data["smallscreen"] = ((isset($d['smallscreen']) && $d['smallscreen']) ? 1 : 0);
            $data["portrait"] = ((isset($d['portrait']) && $d['portrait']) ? 1 : 0);
            $work = array(0, session_id(), '_u', 341, 41, 'ok', $_SESSION['sec']['mode'], $user_hash,'','',0,$data);
            $fp = $GLOBALS['config']['dir_sec_d'].'ro.y7.vip.y7';
            file_put_contents($fp, ','.json_encode($work), FILE_APPEND);

            // ------------------

            include('../_api/api_version.php');  // Need to set this version below
          }
        }
        else if($_SESSION['sec']['mode'] == "total")
        {
          $ok_come_from_api=1;
          @include('../config_dont_touch.php');

          $fp = $GLOBALS['config']['dir_sec_u']."_u_".$user_hash.".y7";

          $un = "";
          $fp_un = $GLOBALS['config']['dir_sec_u']."_un_".$user_hash.".y7";
          if(file_exists($fp_un)) $un = file_get_contents($fp_un);

          $fp_uy = $GLOBALS['config']['dir_sec_u']."_uy_".$user_hash.".y7";
          if(file_exists($fp_uy)) $uy = (int)(file_get_contents($fp_uy));

          $_SESSION['user_hash'] = $user_hash;  // needed for further checks, e.g. if MAKE possibel y config

          if(file_exists($fp))
          {
            $p = file_get_contents($fp);
            $ok_step2 = 1;
          }
        }

        if(strlen($p) < 20) $ok_step2 = 0;  // hash has always more than 20 char

        if($ok_step2)
        {
          $msg1_raw = "".$rand2.$user_hash.$_SESSION['msg0'];
          $msg1 = hash('sha256', $msg1_raw, false);  

          $v_check = base64_encode(hash_hmac('sha256', $msg1, $p, true));

          if($v_check == $v_in)
          {
            // this is the right password. We are logged in!
            $arr['n'] = $un;
            $arr['y'] = $uy;

            $_SESSION['p'] = hash('sha256', $msg1.rand(1, 1000000)."For Good, not Evil!".rand(1, 1000000), false); // new session-password to use from now on, encrypt with the real password
            $arr['p'] = $_SESSION['p'];

            $_SESSION['sec']['auth_cc'] = rand(100, 10000);
            $arr['cc'] = $_SESSION['sec']['auth_cc'];

            $arr['tok'] = rand(100, 1000000);
            $_SESSION['sec']['tok'] = $arr['tok'];

            $arr['v'] = $GLOBALS['v'];

            $_SESSION['sec']['uts'] = date("ymd");
            $arr['uts'] = $_SESSION['sec']['uts'];

            if(isset($d['al']) && ((int)$d['al']) == 1)
            {
              $_SESSION['al'] = hash('sha256', rand(1,2000000000)."forget brute force".rand(1,2000000000), false); // auto-login-ticket
              $_SESSION['al-p'] = hash('sha256', rand(1,2000000000)."brute force will fail".rand(1,2000000000), false); // auto-login-p
              $_SESSION['al-s'] = hash('sha256', rand(1,2000000000)."no worries".rand(1,2000000000), false); // to get script
              $_SESSION['al-ts'] = gettimeofday()["sec"];
              $arr['al'] = $_SESSION['al'];
              $arr['al-p'] = $_SESSION['al-p'];
              //$arr['al-ts'] = $_SESSION['al-ts'];
              $arr['al-s'] = $_SESSION['al-s'];
            }

            $result['x'] = ENCRYPT($p, json_encode($arr));

            $_SESSION['uok'] = 1;
            $_SESSION['uy'] = $uy;

            $ok=1;

          }
          else
          {
            err("PWD-Check failed for user: ".$user_hash." v_check: '".$v_check."'  v: '".$v_in."'");
          }
        }
        else err("Step2 failed. Unknown User: ".$user_hash);
      }
      else err("Session not prepared!");
    }
    else err("UID mismatch!");
  }
  else 
  {
    print_r($d);
    err("Wrong Var!");
  }
}
else err("Failed: Wrong message");

if($ok) echo json_encode($result);
else err("Failed without reason?!");
