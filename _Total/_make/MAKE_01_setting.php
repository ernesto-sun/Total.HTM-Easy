<?php

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();

$do_minify_html = 0;
if($GLOBALS['config']['minify_any'] && $GLOBALS['config']['minify_htm']) 
{
	$do_minify_html = 1;
}

$do_minify_css = 0;
if($GLOBALS['config']['minify_any'] && $GLOBALS['config']['minify_css'])
{
	$do_minify_css = 1;
}

$do_minify_js = 0;
if($GLOBALS['config']['minify_any'] && $GLOBALS['config']['minify_js'])
{
	$do_minify_js = 1;
}


$do_minify_js_lazy = 0;
if($do_minify_js && isset($GLOBALS['config']['minify_js_lazy']) && $GLOBALS['config']['minify_js_lazy'])
{
	$do_minify_js_lazy = 1;
}

$do_minify_css_lazy = 0;
if($do_minify_css && isset($GLOBALS['config']['minify_css_lazy']) && $GLOBALS['config']['minify_css_lazy'])
{
	$do_minify_css_lazy = 1;
}

$do_rem_log = 0;
if(isset($GLOBALS['config']['minify_js_console']) && $GLOBALS['config']['minify_js_console'])
{
	$do_rem_log = 1;
}


$do_lazy_main = 0;
if(isset($GLOBALS['config']['lazy_main']) && $GLOBALS['config']['lazy_main'])
{
	$do_lazy_main = 1;
}

$do_lazy_footer = 0;
if(isset($GLOBALS['config']['lazy_footer']) && $GLOBALS['config']['lazy_footer'])
{
	$do_lazy_footer = 1;
}

$do_lazy_mm = 0;
if(isset($GLOBALS['config']['lazy_menu_main']) && $GLOBALS['config']['lazy_menu_main'])
{
	$do_lazy_mm = 1;
}

$do_lazy_mh = 0;
if(isset($GLOBALS['config']['lazy_menu_hamburger']) && $GLOBALS['config']['lazy_menu_hamburger'])
{
	$do_lazy_mh = 1;
}

$do_lazy_ll0 = 0;
if(isset($GLOBALS['config']['lazy_lang_list_0']) && $GLOBALS['config']['lazy_lang_list_0'])
{
	$do_lazy_ll0 = 1;
}


$make_nojs = 0;
if(isset($GLOBALS['config']['make_nojs_htm_file']) && $GLOBALS['config']['make_nojs_htm_file'])
{
	$make_nojs = 1;
}


$lazy_css_byclass = 0;
if(isset($GLOBALS['config']['lazy_css_byclass']) && $GLOBALS['config']['lazy_css_byclass'])
{
	$lazy_css_byclass = 1;
}


$lazy_js_by_async = 0;
if(isset($GLOBALS['config']['lazy_js_by_async']) && $GLOBALS['config']['lazy_js_by_async'])
{
	$lazy_js_by_async = 1;
}


// ----------------------------------------------------------

// Read setting js-File

$arr_lang = [];
$arr_theme = [];

$lang_default_by_set = ""; 
$theme_default_by_set = ""; 


$fn_setting_js = $dir_total."js/setting.js";
if(file_exists($fn_setting_js))
{
	$js = file_get_contents($fn_setting_js);
	$js = MINIFY_JS($js, 1);

	$js = str_replace('],]',']]', $js);
	$js = str_replace('],}',']}', $js);

	$pos1 = strpos($js, ' _SET_UX_OPT=');
	if($pos1 > 0)
	{
		$pos2 = $pos1 + 13; 
		$pos3 = strpos($js, ']};', $pos2);
		if($pos3 > 0)
		{
			$json_str = substr($js, $pos2, $pos3 - $pos2 + 2);
			$json = json_decode($json_str, true);

			if(isset($json['com-lang']) && is_array($json['com-lang']) && count($json['com-lang']) > 0)			
			{
				$arr_lang = [];
				foreach($json['com-lang'] as $arr_sl)
				{
					$arr_lang[] = $arr_sl[0];
				}
			}

			if(isset($json['com-theme']) && is_array($json['com-theme']) && count($json['com-theme']) > 0)			
			{
				$arr_theme = [];
				foreach($json['com-theme'] as $arr_st)
				{
					$arr_theme[] = $arr_st[0];  // in $arr_st[1] is $is_dark 0/1 
				}
			}
		}
	}

	// now read also _SET_UX for default values of com-lang and com-theme

	$pos1 = strpos($js, ' _SET_UX=');
	if($pos1 > 0)
	{
		$pos2 = $pos1 + 9; 
		$pos3 = strpos($js, ']];', $pos2);
		if($pos3 > 0)
		{
			$json_str = substr($js, $pos2, $pos3 - $pos2 + 2);
			$json = json_decode($json_str, true);

			foreach($json as $arr_inp)
			{
				switch($arr_inp[0])
				{
				case 'com-lang':
					$lang_default_by_set = $arr_inp[3]; 
					break;
				case 'com-theme':
					$theme_default_by_set = $arr_inp[3]; 
					break;
				}
			}
		}
	}
} 

if(count($arr_lang) < 1)
{
	warn("FALLBACK: Reading languages from js/setting.js failed somehow, using the <lang> elements instead!");  

	$arr_dom_lang = $doc["lang"];
	foreach ($arr_dom_lang as $dom_lang)
	{
		$d_lang = pq($dom_lang);
		$lang = trim("".$d_lang->attr("lang"));
		if(!empty($lang))
		{
			if(array_search($lang, $arr_lang) === false) $arr_lang[] = $lang;
		}
	}
}

// get lang attribute of html 

$d_html = $doc["html"];

$lang_default = ''.$d_html->attr("lang");
if(empty($lang_default))
{
	$lang_default = $lang_default_by_set;
	if(empty($lang_default))
	{
		if(count($arr_lang) > 0)
		{
			$lang_default = $arr_lang[0];
		}
		else
		{
			warn("Did not find any lang-information, and not even a default-language. Fallback to 'en'.");
			$lang_default = "en";
		}
	}
}

if(count($arr_lang) < 1)
{
	$arr_lang[] = $lang_default;
}

// -----------------------------------------------------------

if(count($arr_theme) < 1)
{
	warn("FALLBACK: Reading themes from js/setting.js failed somehow, using the css/theme-CSS-files instead!");

	foreach (glob($dir_total."css/theme_*.css") as $fn) 
	{
		$pos = strpos($fn, 'theme_');
		$theme_name = substr($fn, $pos + 6, strlen($fn) - $pos - 10);

		$theme_name = preg_replace('/[^a-zA-Z0-9_.]/','-', $theme_name);

		if(!empty($theme_name))
		{
			$arr_theme[] = $theme_name;
		}
	}	
}

$theme_default = ""; // is overwritten below with the first theme-file used, otherwise from _SET_UX



// -----------------------------------------------------------
// Now, parse lang.js just to be able to fill some language-textes during MAKE
// e.g. readmore-textes 


$_LLS = [];
$_LLD = [];

$fn_lang_js = $dir_total."js/lang.js";
if(!file_exists($fn_lang_js))
{
	warn("Language-JS-File not found: ".$fn_lang_js);
}
else
{
	$js = file_get_contents($fn_lang_js);
	$js = MINIFY_JS($js, 1);

	$js = str_replace('],]',']]', $js);
	$js = str_replace('],}',']}', $js);
	$js = str_replace('",}','"}', $js);

	$pos1 = strpos($js, ' _LLS=');
	if($pos1 > 0)
	{
		$pos2 = $pos1 + 6; 
		$pos3 = strpos($js, '"};', $pos2);
		if($pos3 > 0)
		{
			$json_str = substr($js, $pos2, $pos3 - $pos2 + 2);
			$_LLS = json_decode($json_str, true);
		}
	}

	// -----------------------

	foreach($arr_lang as $lang)
	{
		if ($lang == $lang_default) continue;

		// Read languages.
		$filter = '_LLD.'.$lang.'=';
		$pos1 = strpos($js, $filter);
		if($pos1 == false)
		{
			$filter = '_LLD["'.$lang.'"]=';
			$pos1 = strpos($js, $filter);
		}
		if($pos1 == false)
		{
			warn("Could not find the JSON for a language in lang.js: ".$lang);
			continue;
		}

		$pos2 = $pos1 + strlen($filter); 
		$pos3 = strpos($js, '"};', $pos2);
		if($pos3 > 0)
		{
			$json_str = substr($js, $pos2, $pos3 - $pos2 + 2);
			$_LLD[$lang] = json_decode($json_str, true);
		}
	}
}
