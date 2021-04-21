<?php

// ---------------------------------------------------------------------------
// This code is part of Total.HTM Easy. See: http://exa.run/Total.HTM 
// ---------------------------------------------------------------------------

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();

//-------------------------------------------------
function AUTH_ERR($msg, $returnFalseOnError = 0)
{
  $info = array();
  $info['ip'] = $_SERVER['REMOTE_ADDR'];
  $info['host'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
  $info['agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "?";
  $info['lang'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : "?";  // gives info about the language of the user

  if(!isset($GLOBALS['config']['dir_sec_l']))
  {
    // try to include a config...
    $ok_come_from_api=1;
    include('../config_dont_touch.php');
    if(!isset($GLOBALS['config']['dir_sec_l']))
    {
      include('../../config_dont_touch.php');
      if(!isset($GLOBALS['config']['dir_sec_l']))
      {
        include('config_dont_touch.php');
        if(!isset($GLOBALS['config']['dir_sec_l']))
        {
          $GLOBALS['config']['dir_sec_l'] = "./";  // ultimate fallback, should not happen
        }
      }
    }
  }

  $fp_log = $GLOBALS['config']['dir_sec_l'].'log_'.date('ymd').'_login.y7';

  file_put_contents($fp_log,
      date('Y-m-d H:i:s').'.'.round((microtime(true) * 1000) % 1000).
      ': ERROR AUTH: '.$msg." \r\n".json_encode($info));

  $ip = preg_replace('/[\W]/','_',$_SERVER['REMOTE_ADDR']);
  $fp = $GLOBALS['config']['dir_sec_l'].'_ip_ERR_LOGIN_cc_'.$ip.'__'.date('ymd').'.y7';
  $cc = 0;
  if(file_exists($fp))
  {
    $cc = (int)file_get_contents($fp);
  }
  $cc++;
  file_put_contents($fp, "".$cc);
  if($cc > 10)
  {
    // TODO Lock IP after 10 failed attempts today
  }

  @session_destroy();

  if(isset($returnFalseOnError) && $returnFalseOnError)
  {
    usleep(rand(20000, 150000)); // sleep between 20 and 150 microseconds
    return false;
  }
  else
  {
    usleep(rand(200000, 1500000)); // sleep between 200 and 1500 microseconds
    die();
  }
}



// --------------------------------------------------------------
function AUTH($d, $v, $returnFalseOnError = 0)
{
  $err="";
  $ok = 0;

  if(isset($d['a']) && isset($d['b']) && isset($d['r0']) && isset($d['r1']))
  {
    session_start();

    $uid = (int)($d['a']); //  uid
    $hmac = "".$d['b'];    // hmac-message
    $r0 = (int)($d['r0']);  // new random at beginning
    $r1 = (int)($d['r1']);  // new random at end

    if(isset($_SESSION['uid']) && isset($_SESSION['sec']['uts']) && isset($_SESSION['uok']) && $_SESSION['uok']==1)
    {
      if($uid > 0 && $uid == $_SESSION['uid'] && strlen($hmac) > 20)
      {
        $msg_raw = "".$r0."-".$v."-".$_SESSION['sec']['uts']."-".$uid."-".$r1;
        $msg = hash('sha256', $msg_raw, false);    
        $hmac_check = base64_encode(hash_hmac('sha256', $msg, $_SESSION['p'], true));
        if($hmac_check == $hmac)
        {
           // auth ok
           $ok=1;
        }
        else
        {
          $err = "HMAC-Mismatch! M: ".$msg_raw." M-in: ".(isset($d['m_test']) ? $d['m_test'] : "?");
        }
      }
      else $err = "ERR: SESSION2";
    }
    else $err = "ERR: SESSION1";
  }
  else $err = "ERR: PARAM";

  if(!$ok) AUTH_ERR($err, $returnFalseOnError);  // includes a die();
  return $ok;
}


// --------------------------------------------------------------
function DECRYPT($pwd, $jsondata)
{
    try
    {
        $salt = hex2bin($jsondata["s"]);
        $iv  = hex2bin($jsondata["i"]);
    } 
    catch(Exception $ex) 
    { 
      return null; 
    }

    $ciphertext = base64_decode($jsondata["c"]);

    //echo "C: ", $ciphertext, "\r\nS: ", $salt, "\r\nI: ",$iv, "\r\n";   
    //echo "C: ", $jsondata["c"], "\r\nS: ", $jsondata["s"], "\r\nI: ",$jsondata["i"], "\r\n";   

    $iterations = 999; //same as js encrypting

    $key = hash_pbkdf2("sha256", $pwd, $salt, $iterations, 64);
    $key_bin = hex2bin($key);

    $decrypted = openssl_decrypt($ciphertext , 'aes-256-cbc', $key_bin, OPENSSL_RAW_DATA, $iv);

    //echo "\r\nRES: ", $decrypted, "\r\n"; 

    return $decrypted;
}


// --------------------------------------------------------------
function ENCRYPT($pwd, $plain_text)
{
    $salt = openssl_random_pseudo_bytes(256);
    $iv = openssl_random_pseudo_bytes(16);

    $iterations = 999;
    $key = hash_pbkdf2("sha256", $pwd, $salt, $iterations, 64);

    $encrypted_data = openssl_encrypt($plain_text, 'aes-256-cbc', hex2bin($key), OPENSSL_RAW_DATA, $iv);

    $data = array("c" => base64_encode($encrypted_data), "i" => bin2hex($iv), "s" => bin2hex($salt));
    return json_encode($data);
}
