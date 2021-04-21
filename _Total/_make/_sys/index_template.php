<?php

// -------------------------------------
// Note: If only one language, the whole file is overwritten with e.g.: header("location: ./de/"); exit();

$LANG_POSSIBLE = __LANG_POSSIBLE__; // Overwritten by something like // e.g. array("de","es","en");
$LANG_DEFAULT = __LANG_DEFAULT__; // Overwritten by Default-Language // e.g."de";

$is_lang_explicit = 0;
// -------------------------------------

$lang = $LANG_DEFAULT;
if(isset($_REQUEST['lang']))
{
	$is_lang_explicit = 1;
	$lang = trim(strtolower($_REQUEST['lang']));
}
else
{
	$languageList = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	$languages = array();
	$languageRanges = explode(',', trim($languageList));
	foreach ($languageRanges as $languageRange)
	{
		if (preg_match('/(\*|[a-zA-Z0-9]{1,8}(?:-[a-zA-Z0-9]{1,8})*)(?:\s*;\s*q\s*=\s*(0(?:\.\d{0,3})|1(?:\.0{0,3})))?/', trim($languageRange), $match))
		{
			if (!isset($match[2])) $match[2] = '1.0';
			else $match[2] = (string) floatval($match[2]);
			if (!isset($languages[$match[2]])) $languages[$match[2]] = array();
			$languages[$match[2]][] = strtolower($match[1]);
		}
	}
	krsort($languages);

	foreach($languages as $lang_priority => $lang_arr)
	{
		foreach($lang_arr as $lang_val)
		{
			$lang_val = strtolower(substr($lang_val,0,2));

			if(array_search($lang_val, $LANG_POSSIBLE) !== false)
			{
				$lang = $lang_val;
				break 2;
			}
		}
	}
}

if(array_search($lang, $LANG_POSSIBLE) === false)
{
	$lang = $LANG_DEFAULT;
}

// -------------------------------------

header("location: ./".$lang."/");
exit();
