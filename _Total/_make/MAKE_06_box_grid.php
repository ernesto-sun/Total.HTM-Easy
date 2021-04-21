<?php 

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();

// --------------------------------------------------------------------
// Image loading from container directory directives "data-dir"

$arr_cont = $doc[".box-grid"];

foreach($arr_cont as $dom_cont)
{
	$d_cont = pq($dom_cont);

	$dir_cont = trim($d_cont->attr('data-dir'));

	if(strlen($dir_cont))
	{
		msg("----BOX-GRID with data-dir, dir-reading starting: {$cc_cont} containers existing ");

		if (strpos($dir_cont, '../image/') !== 0)
		{
			warn("Invalid Directory given to box-grid: ",$dir_cont, " Only images allowed!");
			continue;
		}

		$dir_cont = $dir_total.substr($dir_cont,3);

		if(!file_exists($dir_cont) || !is_dir($dir_cont))
		{
			warn("Invalid Directory given to box-grid: ",$dir_cont, " Directory does not exist!");
			continue;
		}

		@msg("box-grid: id='", $d_cont->attr('id') ,"'  Loading by directory: ", $dir_cont);

		// get list of files that are already used hardcoded in HTML
		$arr_file_used = array();

		$arr_img = $d_cont->find(".box img");  // TODO: Support <picture> here!!!
		foreach($arr_img as $dom_img)
		{
			$di = pq($dom_img);
			$src_img = trim($di->attr('src'));

			if(strpos($src_img, $dir_cont) === 0)
			{
				$src_file = substr($src_img, strlen($dir_cont));
				if(strpos($src_file,"/") || strpos($src_file,"\\"))
				{
					// ignore because file is in a sub_dir
				}
				else
				{
					$arr_file_used[] = $src_file;
				}
			}
			else
			{
				// ignore because file is in another dir
			}
		}

		$dc = opendir($dir_cont);
		while(false !== ( $file = readdir($dc)) )
		{
			if (( $file != '.' ) && ( $file != '..' ))
			{
				if (is_dir($dir_cont . '/' . $file) )
				{
					// ignore sub dir for now
				}
				else
				{
					$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
					switch($ext)
					{
						case "svg":
						case "jpg":
						case "jpeg":
						case "gif":
						case "png":
							// ok;
							if(in_array($file, $arr_file_used))
							{
								warn("Not using image-file from directory because used manually already in container: ".$file);
							}
							else
							{
								$d_cont->append("<div class='box'><img src='".$dir_cont.$file."'/></div>");
								@msg("Using Image-File: ", $file);
							}
							break;
						default:
							// ignore
					}
				}
			}
		}
		closedir($dc);		
	}

	// ---------------- end of file-reading data-dir

	$d_cont_body = $d_cont->children(".box-grid-body");
	if($d_cont_body->length() != 1)
	{
		warn("Invalid box-grid-found. They need a single .box-grid-body. id: ".$d_cont->attr('id'));
	}

	$arr_box = $d_cont_body->children(".box");
	$cc_arr_box = $arr_box->length();
	$d_cont->attr("data-n", $cc_arr_box);

	$cc = 0;
	foreach($arr_box as $dom_box)
	{
		$d_box = pq($dom_box);
		$d_box->addClass('i-'.$cc);
		$d_box->attr("data-i", $cc);
		$cc++;
	}

}


// TODO: Gallery-Image-Reading here!!! or above

