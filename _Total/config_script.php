<?php

define("API_KEY",   "jjksd787878d7878sssshjss");   // Change and use the API_KEY e.g.: http://.../config_script.php?ak=API_KEY

define("PATH_HARDCODED",  "");   // Absolute Path! Leave empty to make script search for a path  // TODO: Detect and allow relative path for PATH_HARDCODED 

define("ALLOW_ADMIN", true);     // This allows for the __ADMIN__ setting in config_set_sec.php to create the admin-user at the very first call

// ---------------------------------------------------------
// ---------------------------------------------------------

define("MODE_STRICT",0);
error_reporting(E_ALL | E_STRICT);

header('Content-Type: text/plain');

// --------------
function err($msg)
{
  echo "\n\rERROR: ",$msg;
  die();
}

if(!isset($_GET['ak']) || $_GET['ak'] != API_KEY) err("ak");
if(basename(__FILE__) != "config_script.php") err("sk");

$GLOBALS['cwd'] = str_replace('\\','/',dirname(__FILE__));
if(substr($GLOBALS['cwd'], -1) != "/") $GLOBALS['cwd'] .= "/";

$fp_config = $GLOBALS['cwd']."config_dont_touch.php";

$is_first_time = false;

if(!file_exists($fp_config))
{
  if(createConfig())
  {
    echo "Ok! Config was created.";
    $is_first_time = true;
  }
  else
  {
    err("FAILED!! Did not find any directory with any write access!");
  }
}

$ok_come_from_api = 1;
include($fp_config);

$ok_come_from_config_script = 1;

$cc_change=0;
$cc_change_total=0;
$had_delete=0;
$had_clear=0;
$fp_set_cc=0;
$fp_set_cc_change=0;

foreach (glob($GLOBALS['cwd']."config_set_*.php") as $fp_config_set)
{
  if(is_dir($fp_config_set)) continue;
  $fn_config_set = basename($fp_config_set);
		   

  $is_secret = 0;
  if($fn_config_set == 'config_set_sec.php')
  {
    $is_secret = 1;
  }

   echo "\r\nReading Config File: ",$fn_config_set,"\r\n"; 	

  unset($set_config);
  include($fp_config_set);
  if(!isset($set_config))
  {
    echo "One file that seemed like config_set_*.php failed, please check the file: ",$fp_config_set;
    continue;
  }

  $fp_set_cc++;

  $cc_change=0;
  $had_delete=0;
  $had_clear=0;

  foreach ($set_config as $key => $value)
  {
    if($key == "__ADMIN__")
    {
      if(ALLOW_ADMIN && $is_secret)
      {
        if(strlen($value) >= 4 && !preg_match('/\s/',$value))  // no white-space
        {
          $uh = hash('sha256', 'admin', false);  
          if($is_first_time)
          {
            echo "Creating Admin User.\r\n";
            file_put_contents($GLOBALS['config']['dir_sec_u']."_un_".$uh.".y7", "Admin"); 
            file_put_contents($GLOBALS['config']['dir_sec_u']."_uy_".$uh.".y7", 6);   // user-level 6 actually means admin
          }
          echo "Setting Admin User Password.\r\n";
          file_put_contents($GLOBALS['config']['dir_sec_u']."_u_".$uh.".y7", hash('sha256', $value, false));  
        }
      }
      $set_config[$key] = "";
      continue;
    }  

    $is_num = 0;
    if($is_num = is_numeric($value))
    {
      if(!isset($GLOBALS['config'][$key]))
      {
        echo "New (numeric) key: ", $key,"\r\n";

        $GLOBALS['config'][$key] = $value;
        $cc_change++;
        $cc_change_total++;
      }
      else if($GLOBALS['config'][$key] != $value)
      {
        echo "Changed (numeric) setting, key: ", $key,"\r\n";
        $GLOBALS['config'][$key] = $value;
        $cc_change++;
        $cc_change_total++;
      }
      else
      {
        // ignore same same numeric
		// echo "\r\nUnchanged numeric: ",$key,": ", $value,"\r\n"; 	
      }
    }
    else if($value == '__OK_DELETED__')
    {
      // ignore
    }
    else if($value == '__DELETE__')
    {
      echo "Delete key: ", $key,"\r\n";

      unset($GLOBALS['config'][$key]);
      $cc_change++;
      $cc_change_total++;
      $had_delete=1;
    }
    else if($value == '__CLEAR__')
    {
      echo "Clear key: ", $key,"\r\n";

      $GLOBALS['config'][$key] = "";
      $cc_change++;
      $cc_change_total++;
      $had_clear=1;
    }
    else if(!isset($GLOBALS['config'][$key]))
    {
      echo "New key: ", $key,"\r\n";

      $GLOBALS['config'][$key] = $value;
      $cc_change++;
      $cc_change_total++;
    }
    else
    {
      if(!empty($value))
      {
        if($GLOBALS['config'][$key] != $value)
        {
          echo "Changed setting, key: ", $key,"\r\n";
          $GLOBALS['config'][$key] = $value;
          $cc_change++;
          $cc_change_total++;
        }
        else
        {
          // same same value are ignored.
		  // echo "\r\nUnchanged: ",$key,": ", $value,"\r\n"; 	
        }
      }
      else
      {
        // empty values are ignored, use __CLEAR__ instead
		// echo "\r\nEmpty (ignore): ",$key,": ", $value,"\r\n"; 	
      }
    }
    if($is_secret) $set_config[$key] = ($is_num ? 0 : "");
  }

  if($cc_change)
  {
    copy($fp_config_set, $GLOBALS['config']['dir_sec']."_backup_".$fn_config_set.".".date("Ymd_His")."_".md5(rand()).".y7");
    $fp_set_cc_change++;
  }

  if($is_secret)
  {
    file_put_contents($fp_config_set,
                     "<?php if(!isset(\$ok_come_from_config_script)) die(); \r\n// IMPORTANT: Only set values here. At call of config-script, this file will be overwritten!\n\r\$set_config = ".var_export($set_config,1).";");
  }

  if($had_clear)
  {
    $file_str = file_get_contents($fp_config_set);
    file_put_contents($fp_config_set, str_replace('__CLEAR__', '',$file_str));
  }

  if($had_delete)
  {
    $file_str = file_get_contents($fp_config_set);
    file_put_contents($fp_config_set, str_replace('__DELETE__', '__OK_DELETED__',$file_str));
  }
}

if($fp_set_cc < 1)
{
  err("No config_set.php file found! Nothing done.");
}

if($cc_change_total < 1)
{
  echo "\r\nNo changes found, nothing done.\r\n";
}
else
{
  file_put_contents($config['dir_sec']."config.y7",
                 "<?php if(!isset(\$ok_come_from_api)) die(); \r\n\$GLOBALS['config']=".var_export($GLOBALS['config'],1).";");

  echo "Done! ",$cc_change_total," changes from ",$fp_set_cc_change," files written!\r\n";
}

// -------------------------------------------
function createConfig()
{
  $config = array();
  $dir_here = dirname(__FILE__);

  $meta = array();

  $can_write = 0;
  $dir_here = str_replace('\\','/',$dir_here);
  if(substr($dir_here,-1) != "/") $dir_here .= "/";

  $dir_arr = explode("/",substr($dir_here,0,-1));

  $dir = "/";
  if(substr($dir_arr[0],-1) == ":")
  {
    $meta['os'] = "win";
    // Windows
    $dir = array_shift($dir_arr);

    $meta['drive'] = $dir;
    $dir .= "/";
  }
  else
  {
    $meta['os'] = "linux";
    $meta['drive'] = "";
    // linux
    if(!strlen($dir_arr[0])) array_shift($dir_arr);  // remove first empty one from //
  }

  $meta['dir_script'] = $dir_here;
  $meta['script_config'] = basename(__FILE__);

  $meta['host'] = $_SERVER['HTTP_HOST'];
  $config['path'] = dirname($_SERVER['PHP_SELF']);
  $config['path'] = str_replace('\\','/',$config['path']);
  if(substr($config['path'],-1) != "/") $config['path'] .= "/";

  $meta['dir_web'] = substr($dir_here, 0, strlen($dir_here) - strlen($config['path']) + 1);

  $dir_hardcoded = trim(PATH_HARDCODED);
  if(!empty($dir_hardcoded))
  {
	if(!file_exists($dir_hardcoded))
	{
		echo "\r\nERROR: Hardcoded Directory does not exist!\r\n";
		return false;
	}
	  
	$cw = canWriteDir($dir_hardcoded);
	  
	$config['can_write_dir'] = ($cw > 1 ? 1 : 0);
	$can_write_file = ($cw > 0 ? 1 : 0);
	$config['dir_sec'] = $dir_hardcoded;
	$config['is_dir_sec_online'] = 0;  // TODO: check	

	if(!$can_write_file)
	{
		echo "\r\nERROR: Hardcoded Directory is not writeable!\r\n";
		return false;
	}	
  } 
  else
  {
	  $done = 0;
	  $cw = 0;
	  $len_dir_web = strlen($meta['dir_web']);

	  $dir_highest_cw1="";
	  $dir_highest_cw2="";
	  $dir_highest_cw2_offline="";
	  $dir_highest_cw1_offline="";


	  do
	  {
		$cw = canWriteDir($dir);
		if($cw > 1)
		{
		  if(strlen($dir) < $len_dir_web)
		  {
			$dir_highest_cw2_offline = $dir;
		  }
		  $dir_highest_cw2 = $dir;
		}
		else if($cw == 1)
		{
		  if(strlen($dir) < $len_dir_web)
		  {
			$dir_highest_cw1_offline = $dir;
		  }
		  $dir_highest_cw1 = $dir;
		}

		if(count($dir_arr)) $dir .= array_shift($dir_arr)."/";
		else $done=1;
	  }
	  while (!$done);

	  $can_write_file = 0;
	  if(strlen($dir_highest_cw2_offline))
	  {
		$config['can_write_dir'] = 1;
		$can_write_file = 1;
		$config['dir_sec'] = $dir_highest_cw2_offline;
		$config['is_dir_sec_online'] = 0;
	  }
	  else if(strlen($dir_highest_cw1_offline))
	  {
		$config['can_write_dir'] = 0;
		$can_write_file = 1;
		$config['dir_sec'] = $dir_highest_cw1_offline;
		$config['is_dir_sec_online'] = 0;
	  }
	  else if(strlen($dir_highest_cw2))
	  {
		$config['can_write_dir'] = 1;
		$can_write_file = 1;
		$config['dir_sec'] = $dir_highest_cw2;
		$config['is_dir_sec_online'] = 1;
	  }
	  else if(strlen($dir_highest_cw1))
	  {
		$config['can_write_dir'] = 0;
		$can_write_file = 1;
		$config['dir_sec'] = $dir_highest_cw1;
		$config['is_dir_sec_online'] = 1;
	  }
	  else
	  {
		// no write access at all!!
		$config['can_write_dir'] = 0;
		$can_write_file = 0;
		$config['dir_sec'] = "";
		$config['is_dir_sec_online'] = 0;
	  }
  }

  if($can_write_file && strlen($config['dir_sec']))
  {
    $is_ok_filebased = 1;
  }
  else
  {
    $is_ok_filebased = 0;
  }

  if(!$is_ok_filebased) return false;

  if($config['can_write_dir'])
  {
    $dir_sec = $config['dir_sec'].md5("".rand()."-".rand())."/";
    mkdir($dir_sec);
    if(!file_exists($dir_sec)) die("ERROR!! Nr: 3222");
    $meta['dir_sec_root'] = $config['dir_sec'];
    $config['dir_sec'] = $dir_sec;

    $dir_sec_u = $config['dir_sec']."_u_".md5("".rand()."-".rand())."/";
    mkdir($dir_sec_u);
    if(!file_exists($dir_sec_u)) die("ERROR!! Nr: 3222432");
    $config['dir_sec_u'] = $dir_sec_u;

    $dir_sec_d = $config['dir_sec']."_d_".md5("".rand()."-".rand())."/";
    mkdir($dir_sec_d);
    if(!file_exists($dir_sec_d)) die("ERROR!! Nr: 321432");
    $config['dir_sec_d'] = $dir_sec_d;

    $dir_sec_l = $config['dir_sec']."_l_".md5("".rand()."-".rand())."/";
    mkdir($dir_sec_l);
    if(!file_exists($dir_sec_l)) die("ERROR!! Nr: 3432");
    $config['dir_sec_l'] = $dir_sec_l;

    // if($config['is_dir_sec_online'])  do it anyway
    ensureHtaccessY7($config['dir_sec']);
    ensureHtaccessY7($config['dir_sec_u']);
    ensureHtaccessY7($config['dir_sec_d']);
    ensureHtaccessY7($config['dir_sec_l']);
  }
  else
  {
     // if($config['is_dir_sec_online'])
     ensureHtaccessY7($config['dir_sec']); // at least

     $config['dir_sec'] = $config['dir_sec']."_s_";
     $config['dir_sec_l'] = $config['dir_sec']."_l_";
     $config['dir_sec_u'] = $config['dir_sec']."_u_";
     $config['dir_sec_d'] = $config['dir_sec']."_d_";
  }


  $GLOBALS['config'] = $config;
  $GLOBALS['meta'] = $meta;

  file_put_contents($config['dir_sec']."config.y7",
                   "<?php if(!isset(\$ok_come_from_api)) die(); \r\n\$GLOBALS['config']=".var_export($config,1).";");

  file_put_contents($config['dir_sec']."meta.y7",
                   "<?php if(!isset(\$ok_come_from_api)) die(); \r\n\$GLOBALS['meta']=".var_export($meta,1).";");


  $config_php = "<?php if(!isset(\$ok_come_from_api)) die(); \r\ninclude('{$config['dir_sec']}config.y7'); \r\n";

  if(canWriteDir($dir_here) < 1)
  {
	echo "\r\nPut this into the file ./config_dont_touch.php  ....\r\n\r\n";
	echo $config_php;
	echo "\r\n\r\n\r\n";
  }
  else
  {
	file_put_contents($dir_here."config_dont_touch.php", $config_php);
  }



  return true;
}


// ---------------------------------------
function canWriteDir($dir)
{
  $can_write = 0;
  $rand = floor(rand() * 100000) + 1001;
  $test = $dir."_test_".$rand;
  if(@mkdir($test, 0700))
  {
    if(@file_exists($test) && @is_dir($test))
    {
      //ok, can write here
      @rmdir($test);
      $can_write = 2;
    }
  }

  if(!$can_write)
  {
    $test .= ".y7";
    @file_put_contents($test,$test);
    $test_echo = @file_get_contents($test);
    if($test_echo == $test)
    {
      $can_write = 1;
      @unlink($test);
    }
    else
    {
      // no write possible here
    }
  }

  //echo "DIR: ",$dir," cw: ", $can_write, "\r\n";

  return $can_write;
}

// ---------------------------------------
function ensureHtaccessY7($dir)
{
  $htaccess = "\r\n<Files ~ \"\.y7$\">\r\nOrder allow,deny\r\nDeny from all\r\n</Files>";
  if(file_exists($dir.".htaccess"))
  {
    $htaccess_exist = file_get_contents($dir.".htaccess");
    if(strpos($htaccess_exist, $htaccess) === false)
    {
      file_put_contents($dir.".htaccess", $htaccess, FILE_APPEND);
    }
    else
    {
      // haccess already has the entry
    }
  }
  else
  {
    file_put_contents($dir.".htaccess",$htaccess);
  }
}
