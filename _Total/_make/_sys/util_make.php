<?php

// TODO: Improve this script

// --------------
function msg($msg,$msg1 = null,$msg2 = null,$msg3 = null,$msg4 = null,$msg5 = null,$msg6 = null,$msg7 = null,$msg8 = null,$msg9 = null,$append=0)
{
  if(isset($msg1))
  {
    $msg.=$msg1;
    if(isset($msg2))
    {
      $msg.=$msg2;
      if(isset($msg3))
      {
        $msg.=$msg3;
        if(isset($msg4))
        {
          $msg.=$msg4;
          if(isset($msg5))
          {
            $msg.=$msg5;
            if(isset($msg6))
            {
              $msg.=$msg6;
              if(isset($msg7))
              {
                $msg.=$msg7;
                if(isset($msg8))
                {
                  $msg.=$msg8;
                  if(isset($msg9)) $msg.=$msg9;
                }
              }
            }
          }
        }
      }
    }
  }

  if(!isset($append))$append=0;

  if($GLOBALS['debug']) echo ($append?"":"\n\r").$msg;

  if(!$append) $GLOBALS['msg'][] = $msg;
  else $GLOBALS['msg'][count($GLOBALS['msg'])-1] .= $msg;
}

// --------------
function msg_append($msg,$msg1=null,$msg2=null,$msg3=null,$msg4=null,$msg5=null,$msg6=null,$msg7=null,$msg8=null,$msg9=null)
{
  msg($msg,$msg1,$msg2,$msg3,$msg4,$msg5,$msg6,$msg7,$msg8,$msg9,1);
}

// --------------
function warn($msg)
{
  @msg("WARNING: ",$msg);

  error_log(date('Y-m-d H:i:s').'.'.round((microtime(true) * 1000) % 1000).': Warning MAKE: '.$msg);
}

// --------------
function important($msg)
{
  @msg("IMPORTANT: ",$msg);

  error_log(date('Y-m-d H:i:s').'.'.round((microtime(true) * 1000) % 1000).': MAKE: '.$msg);
}

// ------------------------------------------------------
/*

<link rel="alternate"    for other versions, print, rss, ...

<link rel="alternate" href="" hreflang="de" />


// -------------------------------------------

<meta name="twitter:site" content="@TotalHTM" />
<meta name="twitter:creator" content="@ernestosun" />

<meta property="og:image" content="image/_icon/icon-144x144.png" />
<meta name="twitter:image" content="image/_icon/icon-144x144.png" />

<meta name="twitter:card" content="summary_large_image" />
<meta property="og:type" content="website" />



<meta name="application-name" content="Total.HTM" />
<meta name="apple-mobile-web-app-title" content="Total.HTM" />



<!-- the following values get overwritten by the above if left empty, otherwise what you write -->

<meta property="og:title" content="" />
<meta property="og:description" content="" />

<meta name="twitter:title" content="" />
<meta name="twitter:description" content="" />



// -----------------

		<meta property="og:locale" content="es" />



// ------- READ meta name="theme-color"

<meta name="msapplication-TileColor" content="#003344"/>



// ------- READ link rel="canonical"

<meta property="og:url" content="http://exalot.com/Total.HTM" />
<meta property="og:site_name" content="exalot.com/Total.HTM" />





<!-- --------------------------------------- PWA Web App Stuff, recommended in Web Starter Kit by google -->

<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta name="msapplication-tap-highlight" content="no"/>
<link rel="manifest" href="manifest.json"/>
<meta name="mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black"/>
<link rel="apple-touch-icon" href="image/_icon/icon-152x152.png"/>
<link rel="icon" sizes="192x192" href="image/_icon/icon-192x192.png"/>
<meta name="msapplication-TileImage" content="image/_icon/icon-144x144.png"/>



*/


// ------------------------------------------------------
function mini_htm($code)
{
  return preg_replace(array('/<!--(.*)-->/Uis',"/[[:blank:]]+/"),array('',' '),str_replace(array("\n","\r","\t"),'',$code));
}

function mini_css($CSS)
{
	$CSS = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $CSS);
	$CSS = str_replace(array("\r","\n","\t", '  ', '    ', '    '), '', $CSS);
	return $CSS;
}

// ------------------------------------------------------------------------
function clean_htm($txt)
{
  // Should cover all special chars from DE, EN; ES, HU

 $txt = str_replace("ä", "&auml;", $txt);
 $txt = str_replace("ü", "&uuml;", $txt);
 $txt = str_replace("ö", "&ouml;", $txt);
 $txt = str_replace("Ä", "&Auml;", $txt);
 $txt = str_replace("Ü", "&Uuml;", $txt);
 $txt = str_replace("Ö", "&Ouml;", $txt);
 $txt = str_replace("ß", "&szlig;", $txt);
 $txt = str_replace("Ñ", "&Ntilde;", $txt);
 $txt = str_replace("ñ", "&ntilde;", $txt);
 $txt = str_replace("á", "&aacute;", $txt);
 $txt = str_replace("é", "&eacute;", $txt);
 $txt = str_replace("í", "&iacute;", $txt);
 $txt = str_replace("ó", "&oacute;", $txt);
 $txt = str_replace("ú", "&uacute;", $txt);
 $txt = str_replace("ý", "&yacute;", $txt);
 $txt = str_replace("Á", "&Aacute;", $txt);
 $txt = str_replace("É", "&Eacute;", $txt);
 $txt = str_replace("Í", "&Iacute;", $txt);
 $txt = str_replace("Ó", "&Oacute;", $txt);
 $txt = str_replace("Ú", "&Uacute;", $txt);
 $txt = str_replace("Ő", "&#336;", $txt);
 $txt = str_replace("ő", "&#337;", $txt);
 $txt = str_replace("Ű", "&#368;", $txt);
 $txt = str_replace("ű", "&#369;", $txt);

 $txt = str_replace("</source>", "", $txt);   // this is a bug of pq, W3C-validator says error stray </source> otherwise

 return $txt;
}


// -----------------------------------------
function rmdir_rec($dir) 
{ 
  if (is_dir($dir)) 
  { 
    if(substr($dir, -1) != "/") $dir.="/";
    $af = scandir($dir);
    foreach ($af as $fn) 
    { 
      if ($fn != '.' && $fn != '..' && $fn != '_Total' && $fn != 'www' && $fn != 'htdocs' && $fn != 'public_html')  // Do not delete the system in case it's inside release 
      { 
        if(is_dir($dir.$fn) && !is_link($dir.$fn))
        {
          rmdir_rec($dir.$fn);
        }
        else
        {
          unlink($dir.$fn); 
        }
      } 
    }
    rmdir($dir); 
  } 
}


// -----------------------------------------
function delete_dir_content($dir)
{
	if(substr($dir, -1) != "/") $dir.="/";
	@array_map('unlink', glob($dir."*"));
}

// -----------------------------------------
function delete_dir_release($dir)
{
	if(!file_exists($dir)) return;
	foreach($GLOBALS['config']['dir_release_sub'] as $dir_sub)
	{
		delete_dir_content($dir.$dir_sub."/");
	}
	foreach($GLOBALS['config']['file_release_sub'] as $file_sub)
	{
		@unlink($dir.$file_sub);
	}
}

// -----------------------------------------
function copy_recurse($src, $dst)
{
  if(substr($dst, -1) != '/') $dst .= '/';
  dir_ensure($dst);

  if(substr($src, -1) != '/') $src .= '/';
  $dir = opendir($src);

  while(($file = readdir($dir)) !== false) 
  {
      if (($file != '.') && ($file != '..')) 
      {
          if (is_dir($src.$file)) 
          {
              copy_recurse($src.$file, $dst.$file);
          }
          else 
          {
              copy($src.$file, $dst.$file);
          }
      }
  }
  closedir($dir);
}



// -----------------------------------------
function copy_files($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ($file = readdir($dir))) 
    {
        if (($file != '.') && ($file != '..')) 
        {
            if (!is_dir($src.'/'.$file)) 
            {
                copy($src.'/'. $file, $dst.'/'.$file);
            }
        }
    }
    closedir($dir);
}


// -----------------------------------------
function dir_ensure($fn)
{
  if(file_exists($fn)) return;

	$fn = str_replace('\\', '/', $fn);

  $pos_dot = strpos($fn, '.');
  $pos_slash = strpos($fn, '/');

  $dir = $fn;
  if($pos_slash > 0 && $pos_dot > $pos_slash)
  {
    $dir = dirname($fn); // $fn seems to be a file
  }

  $pdir = dirname($dir);  // parent-dir
  if(strlen($pdir) > 0 && strlen($pdir) < strlen($dir))  // avoid endless recursion
  {
    dir_ensure($pdir);
  }

  if(!file_exists($dir))
  {
    mkdir($dir);

    if(!file_exists($dir))
    {
      err("Could not create folder: ".$dir);
    } 
  }
}


// -----------------------------------------
function copy_ensurepath($src, $dst)
{
  dir_ensure($dst);
  copy($src, $dst);
}


// -----------------------------------------
function windows_filename($fn)
{
	//$fn = str_replace('/', '\\', $fn);
	$fn = iconv('utf-8', 'cp1252', $fn);
	return $fn;
}


// -----------------------------------------
function latestFileModifiedTime_DEPRI($dir,$rec)
{
    if(!isset($rec))$rec=0;
    $latest = 0;
    if(substr($dir, -1) != "/") $dir.="/";

    $d = dir($dir);
    while($entry = $d->read())
    {
      if ($entry != "." && $entry != "..")
      {
        $GLOBALS['latest_crc'] ^= crc32($entry);

        $fn = $dir.$entry;
        if (!is_dir($fn))
        {
            $current = filemtime($fn);
        }
        else if ($rec && is_dir($fn))
        {
            $current = latestFileModifiedTime($fn,1);
        }

        if ($current > $latest)
        {
            $latest = $current;
        }

        $GLOBALS['latest_cc']++;
      }
    }
    $d->close();
    return $latest;
}
