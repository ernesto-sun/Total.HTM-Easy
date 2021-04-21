<?php

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();


// -----------------------------------------

$fp_htaccess = $dir_make."_sys/htaccess_template.txt";
$htaccess = "";
if(file_exists($fp_htaccess))
{
	$htaccess .= file_get_contents($fp_htaccess);
}

if(isset($GLOBALS['config']['enable_https_redirect']) && $GLOBALS['config']['enable_https_redirect'])
{
	$htaccess .= <<<END
# The following redirects from Not-HTTPS to HTTPS ( = SSL, that appears safe in browsers)

RewriteEngine On
RewriteCond %{HTTPS} !on
RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
END;
}

file_put_contents($dir_release.".htaccess", $htaccess);

// -----------------------------------------


$index_htm = file_get_contents($dir_make."_sys/index_template.htm");
$index_htm = str_replace("__LANG_DEFAULT__", $lang_default, $index_htm); 
file_put_contents($dir_release."index.htm", $index_htm);

if(count($arr_lang) <= 1)
{
	// single-Language
	file_put_contents($dir_release."index.php", '<?php header("location: ./'.$lang_default.'/"); exit();');
}
else
{
	// multi-Language
	$index_php = file_get_contents($dir_make."_sys/index_template.php");
	$index_php = str_replace("__LANG_DEFAULT__", var_export($lang_default, true), $index_php); 
	$index_php = str_replace("__LANG_POSSIBLE__", var_export($arr_lang, true), $index_php); 
	file_put_contents($dir_release."index.php", $index_php);
}

