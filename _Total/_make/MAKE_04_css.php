<?php

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();


@msg("--------------------------------------------------------- CSS TREATMENT ... ");

// Any CSS can link to images of all sort. Here all references under /image/ are ensured 
// to be copied over to the release. 
// Ergo: Links to other locations are not ensured by MAKE. If you link to /img/ make sure 
// the link goes to one of the system-folders inside /img/ that start with underline, such
// as '_but', '_sym' or '_sys' 


$css_complete = "";

// --------------------------------------------------------------------
// Inline CSS in case of minify_html as well

$arr_style = $doc["style"];
foreach ($arr_style as $dom_style)
{
	$d_style = pq($dom_style);
	$css = $d_style->html();

	if($do_minify_html || $do_minify_css)
	{
		$css = mini_css($css);
		$d_style->html($css);
	}

	$css_complete .= $css;
}


// --------------------------------------------------------------------
// CSS Management

$dir_release_css = $dir_release."css/";
if(!is_dir($dir_release_css)) mkdir($dir_release_css);

$arr_style = $doc["head link[rel='stylesheet']"];

$d_style_sys = 0;
$is_style_sys = 0;

$css_default = "";
$css_lazy = "";

$done_theme_def = 0;

$is_lazy = 0;

foreach ($arr_style as $dom_style)
{
	$d_style = pq($dom_style);
	$src = $d_style->attr("href");

	$is_default = (substr($src, 0, 7) == '../css/') ? 1 : 0;
	$is_style_sys = 0;

	if(!$is_default)
	{
		$is_default = (substr($src, 0, 11) == '../css_sys/') ? 1 : 0;
		
		if($src == '../css_sys/01_system.css')
		{
			$d_style_sys = $d_style; 						
			$is_style_sys = 1;
		}
	}

	if(!$is_default)
	{
		// ignoring other external CSS-Files
		warn("Ignoring CSS-file because it's not under ../css : ".$src);
		continue;
	}

	$is_theme = (substr($src, 0, 13) == '../css/theme_') ? 1 : 0;

	if($is_theme)
	{
		if(!$done_theme_def)
		{
			$theme_name = substr($src, 13, strlen($src) - 17); // .css  
			$theme_name = preg_replace('/[^a-zA-Z0-9_.]/','-', $theme_name);

			if(array_search($theme_name, $arr_theme) !== false)
			{
				$theme_default = $theme_name; 
				$done_theme_def = 1;

				$css_th = trim(mini_css(file_get_contents($dir_total.substr($src, 3))));
				if(strlen($css_th) < 1)
				{
					$d_style->remove();
					msg("Detected and removed empty default-theme CSS-File: ".$theme_name);
				}
				else
				{
					$d_style->attr("id", "link-theme-".$theme_name);
					msg("Detected default-theme CSS-File: ".$theme_name);
				}
			}
			else
			{
				warn("Invalid theme-CSS-file: ".$theme_name);
			}
		}
		else
		{
			// remove multiple theme CSS files, only one at a time
			$d_style->remove();

			msg("Removing additional theme CSS-File: ".$src);
		}
		continue;
	}

	$fn = $dir_total.substr($src, 3);
	$css = file_get_contents($fn);
	$css_complete .= $css;

	if($lazy_css_byclass)
	{
		if($d_style->hasClass("lazy-css-begin"))
		{
			@msg("Lazy CSS-Files by class .lazy-css-begin BEGIN!");
			$is_lazy = true;
		}
	}

	if($is_lazy)
	{
		@msg("Lazy CSS-File: ", $src);
		$css_lazy .= $css;
	}
	else
	{
		@msg("Default CSS-File: ", $src);
		$css_default .= $css;
	}
	if(!$is_style_sys) $d_style->remove();
}

if($do_minify_css)
{
	$css_default = mini_css($css_default);
	$css_lazy = trim(mini_css($css_lazy));
	$css_complete = mini_css($css_complete);
}

$fnCSS = "f0.css";
$fnCSS_lazy = "f1.css";


if(isset($GLOBALS['config']['minify_css_add_timestamp']) && $GLOBALS['config']['minify_css_add_timestamp'])
{
	$fnCSS = "f0_".date('ymdHis').".css";
	$fnCSS_lazy = "f1_".date('ymdHis').".css";
}

@msg("Writing CSS File: ", $fnCSS);
file_put_contents($dir_release_css.$fnCSS, $css_default);


if(strlen($css_lazy) > 0)
{
	@msg("Writing Lazy CSS File: ", $fnCSS_lazy);
	file_put_contents($dir_release_css.$fnCSS_lazy, $css_lazy);	

	$js_extra = '_h.APs(\'<link href="../css/'.$fnCSS_lazy.'" rel="stylesheet" type="text/css"/>\');'.$js_extra;
}


if(!$d_style_sys)
{
	err("The system.js file was not found. Total.HTM seems installed wrongly.");
}

$d_style_sys->before('<link rel="preload" href="../css/'.$fnCSS.'" as="style">');	
$d_style_sys->attr('href', '../css/'.$fnCSS);


// --------------------------------------------------------------------
// ------        care for THEMES
// --------------------------------------------------------------------

if(empty($theme_default))
{
	$theme_default = $theme_default_by_set;  // thats the fallback
}

if(count($arr_theme) > 0)
{
	foreach($arr_theme as $name_theme)
	{
		$fn_theme = 'css/theme_'.preg_replace('/[^a-zA-Z0-9_.]/','_',$name_theme).'.css';
		if(file_exists($dir_total.$fn_theme))
		{
			$css = file_get_contents($dir_total.$fn_theme);
			if($do_minify_css)
			{
				$css = mini_css($css);
			}
			$css_complete .= $css;			
			file_put_contents($dir_release.$fn_theme, $css);
		}
		else
		{
			warn("Invalid theme given, CSS-file not found: ".$fn_theme);
		}
	}
}


if($do_minify_css_lazy)
{
	// this refers to the 'very lazy' css-scripts inside ../css_lazy/

	$fp_css_lazy = $dir_release."css_lazy/";

	if(file_exists($fp_css_lazy) && is_dir($fp_css_lazy))
	{
		$dir = opendir($fp_css_lazy);
		while(false !== ($fn = readdir($dir))) 
		{
			if (($fn != '.') && ($fn != '..')) 
			{
				if(strtolower(substr($fn, -4)) == ".css")
				{
					$fp = $fp_css_lazy.'/'.$fn;
					$css = file_get_contents($fp);
					$css = mini_css($css);
					file_put_contents($fp, $css);

					msg("Did minify file in css_lazy/: ".$fn);
				}
			}
		}
		closedir($dir);
	}
}

