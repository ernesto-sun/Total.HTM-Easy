<?php

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();

// --------------------------------------------------------------------
// Manage and Minify JS

$has_js_inline = 0;

if($doc["script:not([src])"]->length())
{
	$has_js_inline = 1;
}


$dir_release_js = $dir_release."js/";
if(!is_dir($dir_release_js)) mkdir($dir_release_js);

$doc["#script-total"]->remove();  // remove the total.js from Relase

$div = $doc["#div-script-main"];
if(!$div->length())
{
	$div = pq("<div>")->attr("id","div-script-main");
	$doc["body"]->append($div);
}	

$div->append("<script>".$xr_js."</script>");

$do_ts_js = 0;
if(isset($GLOBALS['config']['minify_js_add_timestamp']) && $GLOBALS['config']['minify_js_add_timestamp'])
{
	$do_ts_js = 1;
}

$d_script_sys = 0;

$arr_script = $doc["script"];

$js_combi = "";
$js_lazy = "";

foreach ($arr_script as $dom_script)
{
	$d_script = pq($dom_script);
	$src = "".$d_script->attr("src");

	$is_inline = 0;
	if(strlen($src) < 1)
	{
		$is_inline = 1;
		$js = $d_script->html();
	}
	else
	{
		// by file
		$js = file_get_contents($dir_total.substr($src,3));
	}

	if($d_script->parents("#div-script-main")->length())
	{		
		$js_combi .= $js;  
		$d_script->remove();
		continue;	
	}

	if($d_script->parents("#div-script-lazy")->length())
	{		
		$js_lazy .= $js;  
		$d_script->remove();
		continue;	
	}

	if($is_inline)
	{
		if($do_minify_js || $do_minify_html)
		{
			$js = MINIFY_JS($js, $do_rem_log || $do_minify_html); 
			$d_script->html($js);	
		}
		else
		{
			// nothing to do
		}
	}
	else
	{
		// not a inline, and not -main and not -lazy 

		if(substr($src, 0, 6) != "../js/")
		{
			@msg("Ignoring JS-File because it's not under ../js/: ".$src);
			return;
		}	

		$fn_js = substr($src, 6);
		$fn_js = preg_replace('/[^a-zA-Z0-9_.]/','_', $fn_js);


		if($do_minify_js)
		{
			$js = MINIFY_JS($js, $do_rem_log);
		}

		$is_sg = 0;

		if($fn_js == "_sys_global.js")
		{
			$is_sg = 1;
			$d_script_sys = $d_script; 
		}
		
		if($do_ts_js)
		{
			$fn_js = substr($fn_js, 0, strlen($fn_js) - 3)."_".date('ymdHis').'.js';
		}

		if($is_sg)
		{
			$d_script->before('<link rel="preload" href="../js/'.$fn_js.'" as="script">');

		}

		file_put_contents($dir_release_js.$fn_js, $js);
		$d_script->attr("src", "../js/".$fn_js);

	}
}

if(!empty($js_combi))
{
	if($do_minify_js)
	{
		$js_combi = MINIFY_JS($js_combi, $do_rem_log);
	}

	$fn_js = "i0";
	if($do_ts_js)
	{
		$fn_js .= "_".date('ymdHis');
	}
	$fn_js .= ".js";

	file_put_contents($dir_release_js.$fn_js, $js_combi);
	
	$div->append("<script src='../js/{$fn_js}'></script>");

	if(!$d_script_sys)
	{
		err("Could not find system-js-file: global.js");
	}

	$d_script_sys->before('<link rel="preload" href="../js/'.$fn_js.'" as="script">');	
}


if(!empty($js_lazy))
{
	if($do_minify_js)
	{
		$js_lazy = MINIFY_JS($js_lazy, $do_rem_log);
	}

	$fn_js = "i1";
	if($do_ts_js)
	{
		$fn_js .= "_".date('ymdHis');
	}


	$doc["#div-script-lazy"]->remove();

	if($lazy_js_by_async)
	{
		$doc['body']->append('<script async src="../js/'.$fn_js.'.js"></script>');
		$js_lazy .= ' if (_d.readyState != "loading") IDLE(I02); else _d.addEventListener("DOMContentLoaded", I02);';
	}
	else
	{
		$js_extra .= "LAZY('{$fn_js}').then(I02});";
	}

	file_put_contents($dir_release_js.$fn_js.'.js', $js_lazy);
}
else
{
	$js_extra .= "I02();";
} 

// $js_extra is written outside after CSS-work


if($do_minify_js_lazy)
{
	// this refers to the 'very lazy' JS-scripts inside ../js/_lazy/

	$fp_js_lazy = $dir_release."js/_lazy/";

	if(file_exists($fp_js_lazy) && is_dir($fp_js_lazy))
	{
		
		$dir = opendir($fp_js_lazy);
		while(false !== ($fn = readdir($dir))) 
		{
			if (($fn != '.') && ($fn != '..')) 
			{

				if(strtolower(substr($fn, -3)) == ".js")
				{
					$fp = $fp_js_lazy.'/'.$fn;
					$js = file_get_contents($fp);
					$js = MINIFY_JS($js, $do_rem_log);
					file_put_contents($fp, $js);

					msg("Did minify file in js/_lazy/: ".$fn);
				}
			}
		}
		closedir($dir);
	}
}
	


