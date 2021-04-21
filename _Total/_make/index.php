<?php

/* ---------------------------------- Total.HTM Easy MAKE Script -------------------
//
//  @license: JSON License == Open Source, MIT + Ethics    
//
//  @authors: Ernesto Sun
//  @version: 20210420 V0.41
*/

header('Content-type: application/json');
define('MODE_STRICT', 1);
error_reporting(E_ALL | E_STRICT); 
set_time_limit(300);  // 5 minutes is a hard limit here, TODO: what makes sense?!

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

	if($GLOBALS['debug'])
	{
		$result = array();
		if($GLOBALS['verbose']) $result['msg'] = $GLOBALS['msg'];
		$result["err"] = $msg;
		echo json_encode($result);
	} 

	usleep(rand(100000, 300000));  // thats between 100ms and 300ms 
    die();
}


$ok_come_from_api=1;
include('../config_dont_touch.php');

$GLOBALS['fp_log'] = $GLOBALS['config']['dir_sec_l'].'log_'.date('ymd').'_make_index_php.y7';

ini_set('log_errors', 1);
ini_set('error_log', $GLOBALS['fp_log']);

//-------------------------------------------------
// ---------------------------- from here on logging shall work
//-------------------------------------------------

// ------------------------------------------------------
$GLOBALS['ts_last'] = 0;
$ts = 0;
function TS($msg)
{
	$ms = intval(microtime(true) * 1000);	
	if($GLOBALS['ts_last']) 
	{
		$msd = $ms - $GLOBALS['ts_last']; 
		@msg('TS: '.$msg.' took: '.$msd."ms");
	}
	$GLOBALS['ts_last'] = $ms; 
}
// ------------------------------------------------------

$ts0 = intval(microtime(true) * 1000);

if($GLOBALS['debug'])
{
	$ts = 1;
}

if($ts) TS("init");

require_once("_sys/util_make.php");

// ------------------------------------------------------------------------
function MINIFY_JS($js, $rem_log = 1)
{
	if ($rem_log)
	{
		$js = str_replace("console.","; // console.", $js); // TODO: ensure not to brake JS (slider.js!!!)
	}

	$js = JSMin::minify($js);
	return $js;
}

//-------------------------------------------------

important("MAKE started!");

$dir_total = "../";  // Hardcoded because better never change dir-structure within _Total/

$dir_here = dirname(__FILE__);
$dir_here = str_replace('\\','/',$dir_here);
if(substr($dir_here, -1) != '/') $dir_here .= '/';

$fp_total_config = $dir_total.'config_dont_touch.php';
$ok_come_from_api=1;
include($fp_total_config);

$GLOBALS['config']['dir_release_sub'] = array('css','font','image','img','js','s');
$GLOBALS['config']['file_release_sub'] = array('favicon.ico','humans.txt','robots.txt','index.php','manifest.json','_sw.js','sitemap.xml');

$dir_cache = $GLOBALS['config']['dir_sec_d']."make_cache/";
if(!file_exists($dir_cache)) mkdir($dir_cache);
if(!file_exists($dir_cache)) err("Could not create cache directory");

$GLOBALS['verbose'] = 0;
$GLOBALS['msg'] = array();

$is_test = 0;

$GLOBALS["indexOk"] = 815;

// ------------------------------------------------------
if(isset($_REQUEST['ak']))
{
	$GLOBALS['verbose'] = 1;

	if(!$GLOBALS['config']['api_key_make_allow']) err("allow_api_key is false");
	if(strlen($GLOBALS['config']['api_key_make']) < 10) err("api key not set or not long enough");
	if(trim($_REQUEST['ak']) === trim($GLOBALS['config']['api_key_make']))
	{
		// api-key seems valid
		important("MAKE started using API-KEY");
	}
	else err("Invalid API-Key");

	if(isset($_REQUEST['test']) && $_REQUEST['test'] == 'test')
	{
		$is_test = 1;
	}
}
else
{

	include("../version.php");
	$ok_come_from_api = 1;
	include("../config_dont_touch.php");

	$d = json_decode(file_get_contents('php://input'), true);

	include("../_login/util_sec.php");
	if(!AUTH($d , $GLOBALS["v"])) die("Au!");
	if($_SESSION['sec']['mode'] != 'total') die("Invalid Login Mode!");

	if($_SESSION['uy'] < $GLOBALS['config']['protected_write'] || $_SESSION['uy'] < 2) err("Invalid Login UY: ".$_SESSION['uy']);

	if(isset($d['verbose'])) $GLOBALS['verbose'] = 1;

	if(isset($d['test']) && $d['test'] == 'test')
	{
		$is_test = 1;
	}

	important('MAKE started using valid Login');
}


// ---------------------------------------------------------------------------

if($ts) TS('login');  // last work

if(!file_exists($dir_total."_HTM/Total.HTM")) err("no valid dir_total set in config or found in ../");
if(preg_match('/^[a-zA-Z0-9_\/\.]+$/',$dir_total) !== 1 ) err("Invalid DIR");
if(substr($dir_total, -1) != '/') $dir_total .= "/";

// ---------------------------------------------------------------------------

$dir_htm = $dir_total."_HTM/";
$filename_total = $dir_htm."Total.HTM";

if (!file_exists($filename_total)) err("Total.HTM main file doesnt exist: ".$filename_total);

// ---------------------------------------------------------------------------

$url = "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
$pos_make = strrpos($url, '/_make/');
if($pos_make < 10) err('Total.HTM installed wrongly somehow');
$url_total =  substr($url, 0, $pos_make + 1);

$xr = $url_total;

if($is_test)
{
	msg('Mode: Test');

	$xr_testing = trim($GLOBALS['config']['xr_testing']);
	if(!empty($xr_testing))
	{
		$xr = $xr_testing;
	}
}
else
{
	msg("Mode: RELEASE");

	$xr_release = trim($GLOBALS['config']['xr_release']);
	if(!empty($xr_release))
	{
		$xr = $xr_release;
	}
}

$xr_js = '_xr_ut="'.$xr.'"';
msg("Writing XR-Dir: ", $xr_js);

// ------------------------------------------------------


$dir_make = $dir_total."_make/";
if (!file_exists($dir_make) || !is_dir($dir_make)) err("Total.HTM _make installed wrongly ");

$dir_v = $GLOBALS['config']['dir_sec_d']."make_v/";

if(!file_exists($dir_v))
{
	mkdir($dir_v);
}

if (!file_exists($dir_v) || !is_dir($dir_v)) err("Could not create version directory");

// ------------------------------------------------------

$fn_json_v = $dir_v."_v.json.y7";

$vi = 1;
if (file_exists($fn_json_v))
{
	$json_v = file_get_contents($fn_json_v);
	$dv = json_decode($json_v, true);
	if($dv == null) err("Invalid version main file: ".$fn_json_v." error: ".json_last_error());
	$vi = 1 + ((int) $dv['vi']);
}

$vnow = 'latest';
if($is_test)
{
	$vnow = "test";
}

$dir_vnow = $dir_v.$vnow."/";

rmdir_rec($dir_vnow);
mkdir($dir_vnow);
if (!file_exists($dir_vnow)) err("Could not create version directory");

// ------------------------------

$dir_release = $dir_vnow;					//  For 'historic reasons' we keep referring to dir_release
$_GLOBALS['dir_vnow'] = $dir_release;

// ----------------------------------------------------------

$markup = file_get_contents($filename_total);

require('_3p/phpQuery-onefile.php');

$doc = phpQuery::newDocumentHTML($markup);


// ------------------------------------------------------

require_once("_3p/jsMin.php");


include("MAKE_01_setting.php");

if($ts) TS("prepare");  // last work

include("MAKE_02_copy.php");

if($ts) TS("copy");  // last work

// ----------------

$_b = $doc["body"];

if(isset($GLOBALS['config']['login_at_start']) && $GLOBALS['config']['login_at_start'])
{
	$_b->after('<script id="script-login" src="'.$xr.'/_login/login_init.js"></script>');
}

if(!$_b->hasClass("menu-left") && !$_b->hasClass("menu-top")) $_b->addClass("menu-onclick");

$js_extra = '';

$arr_image = [];  		 // Holds the filenames of all images used
$arr_image_small = [];   // Holds the filenames of those images, from <img> '.resize-small' 
$arr_image_mob = [];   	 // Those images that have <picture> '.make-mob'   
$arr_image_copy = [];    // Those srcset-images used within <picture>, only copied,    

include("MAKE_03_js.php");

if($ts) TS("js");  // last work

include("MAKE_04_css.php");

if(!empty($js_extra))
{
	$doc["#div-script-main"]->append('<script>function I01(){'.$js_extra.'}</script>');
}

if($ts) TS("css");  // last work

include("MAKE_05_topindex.php");

include("MAKE_06_box_grid.php");

if($ts) TS("box-grid");  // last work

require_once "_3p/ImageResize.php";	

include("MAKE_07_image.php");

$doc_nojs = 0;
if($make_nojs)
{
	include("MAKE_08_nojs.php");
	if($ts) TS("nojs");  // last work
}

include("MAKE_09_lazy_image.php");

if($ts) TS("image");  // last work

include("MAKE_10_sitemap_prepare.php");

if($ts) TS("sitemap-prepare");  // last work

include("MAKE_11_html_dyn.php");

if($ts) TS("HTML dyn");  // last work

include("MAKE_12_section_work.php");

if($ts) TS("section work");  // last work

include("MAKE_13_section_lang.php");

if($ts) TS("section languages");  // last work

include("MAKE_14_sitemap_write.php");

if($ts) TS("sitemap write");  // last work

// ------------------------------------------------------------
// ------------------------------------------------------------

$dir_final = $dir_total."../release/";

if($is_test)
{
	$dir_final = $dir_total."../test/";

	if(isset($GLOBALS['config']['path_to_test']) && strlen(trim($GLOBALS['config']['path_to_test'])))
	{
		$dir_final = ''.trim($GLOBALS['config']['path_to_test']);
	}

	important("Making TEST-Version into: ".$dir_final);

	if(substr($dir_final, -1) != '/') $dir_final .= '/';	

	dir_ensure($dir_final);  // in fact to ensure parent dir's
	rmdir_rec($dir_final);

	rename($dir_release, $dir_final);  // may be faster than copy
}
else
{
	if(isset($GLOBALS['config']['path_to_release']) && strlen(trim($GLOBALS['config']['path_to_release'])))
	{
		$dir_final = ''.trim($GLOBALS['config']['path_to_release']);
	}

	important("Making RELEASE into: ".$dir_final);

	if(substr($dir_final, -1) != '/') $dir_final .= '/';	
	
	if(!is_dir($dir_final)) dir_ensure($dir_final);
	if(!is_dir($dir_final)) err("Could not create release directory: ".$dir_final);

	if(isset($GLOBALS['config']['empty_release_before_copy']) && $GLOBALS['config']['empty_release_before_copy'])
	{
		rmdir_rec($dir_final);
		mkdir($dir_final);
	}
	else
	{
		delete_dir_release($dir_final);
	}

	copy_recurse($dir_release, $dir_final);
	
	// ----------------------- archive management

	$cc_archive = 10;
	if(isset($GLOBALS['config']['cc_version_prev_keep']))
	{
		$cc_archive = (int) $GLOBALS['config']['cc_version_prev_keep'];
	}

	$vn = "NO_ARCHIVE"; 

	if($cc_archive > 0)
	{
		$vn = "v_".date('Ymd_His');

		$dir_archive =  $dir_v.'archive/';
		dir_ensure($dir_archive);
		if (!file_exists($dir_archive)) err("Could not create archive directory: ".$dir_archive);

		$arr_v = glob($dir_archive.'v_*');

		function cmp_ft_rec($a, $b) { return filemtime($b) - filemtime($a); }		
		usort($arr_v, 'cmp_ft_rec');  // shall sort from latest down

		$cc = 0;
		foreach($arr_v as $dir_v)
		{
			$cc++;
			if($cc >= $cc_archive)
			{
				rmdir_rec($dir_v);
			}
		}

		$dir_archive_v =  $dir_archive.$vn.'/';
		if (file_exists($dir_archive_v)) err("Archive directory already exists: ".$dir_archive);

		rename($dir_release, $dir_archive_v);  // may be faster than copy
	}

	file_put_contents($fn_json_v, "{\"vi\":".$vi.",\"ts\":\"".MS()."\",\"tss\":\"".TIMESTAMP()."\",\"dir\":\"".$vn."\"}");
}


// ---------------------------------------------------------------------------
@msg("------------------------------------");

if($is_test)
{
	@msg("Total.HTM Make of Test-Version DONE!");
}
else
{
	@msg("Total.HTM Make of Release DONE!");
}

// ------------------------------------------------------------
include($GLOBALS['config']['dir_sec']."meta.y7");

$dir_final_abs = '';

$arr_fin = explode('/', $dir_final);
if(($GLOBALS['meta']['os'] != 'win' && empty($arr_fin[0])) ||
   ($GLOBALS['meta']['os'] == 'win' && strpos($arr_fin[0],':') > 0))
{
	// seems absolute
	$dir_final_abs = $dir_final;

}
else
{
	// dir_final is relative
	$arr_here = explode('/', $dir_here);
	array_pop($arr_here); // empty element at the end because we have the slash at the end
	$i = 0;
	while($arr_fin[$i++] == "..")
	{
		if(!count($arr_here)) err("Finally recognized invalid directory structure!!");
		array_pop($arr_here);
	}
	array_splice($arr_fin, 0, $i-1);
	$dir_final_abs = implode('/', $arr_here).'/'.implode('/', $arr_fin);
}

$len_dir_web = strlen($GLOBALS['meta']['dir_web']);

if(strlen($dir_final_abs) < $len_dir_web)
{
	err('Invalid file structure recognized finally!');
}

$link = substr($dir_final_abs, $len_dir_web);

$url = 'http'.(!empty($_SERVER['HTTPS'])?'s':'').'://'.$GLOBALS['meta']['host'].'/';

$result = array();
if($GLOBALS['verbose']) $result['msg'] = $GLOBALS['msg'];

$result['link'] = $url.$link;

if($ts) TS("finish");  // last work

if($ts0) msg('MAKE took: '.(intval(microtime(true) * 1000) - $ts0).'ms');

important('MAKE finished successful.');

echo json_encode($result);
die();
