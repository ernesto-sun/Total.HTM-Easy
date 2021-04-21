<?php 

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();


// ---------------------------------------------------------------------------
// Now, get all images used in default CSS and themes
	
$offset = 0;
while( $pos_img = strpos( $css_complete, "/image/", $offset) )
{
	$pos_end = strpos($css_complete, "\"", $pos_img);
	$pos_end_2 = strpos($css_complete, "'", $pos_img);
	if (!$pos_end || ($pos_end_2 && $pos_end_2 < $pos_end)) $pos_end = $pos_end_2;
	$pos_end_3 = strpos($css_complete, ")", $pos_img);
	if(!$pos_end || ($pos_end_3 && $pos_end_3 < $pos_end)) $pos_end = $pos_end_3;   

	if($pos_end <= $pos_img)
	{
		warn("Couldn't find valid end of image filename in CSS, POS: ".$pos_img." Around this: ".substr($css_complete, $pos_img - 10, 40));
		continue;
	}

	$img_filename = substr($css_complete, $pos_img + 7, $pos_end - $pos_img - 7);

	$offset = $pos_end + 1;

	@msg("Image used in CSS: ../image/", $img_filename);

	$arr_image[] = '../image/'.$img_filename;
}



// --------------------------------------------------------------------
// Reading regular Images in HTML ...

$arr_img = $doc["img"];
foreach($arr_img as $dom_img)
{
	$di = pq($dom_img);
	$src = trim($di->attr('src'));

	$alt = $di->attr('alt');
	$alt_unset = 0;
	if(!isset($alt))
	{
		$alt = "";
		$alt_unset = 1;
	}

	$is_in_pic = 0;
	if($di->parent()->is("picture")) $is_in_pic = 1;

	if(substr($src, 0, 9) == '../image/')
	{
		$arr_image[] = $src;

		$want_small = 0; 
		if(!$is_in_pic)
		{
			if($di->hasClass('resize-small'))
			{
				$want_small = 1;
			}
			else
			{
				if($di->parents('.box-grid')->hasClass('resize-small'))
				{
					$want_small = 1;
				}
			}
		}	

		if($want_small) 
		{
			$arr_image_small[] = $src; 
		}


		// ----------------
		// Care for alt-attribute:
		// since here wa are in /image/, noit in /img/ we assume the filenames are meaningful
		if($alt_unset)
		{
			$alt = str_replace('_', ' ', explode('.',basename($src))[0]);
			msg("Image of /image/ without alt-attribute. Detected from filename: ".$alt);	
		}	
	}

	if($alt_unset)
	{
		$di->attr('alt', $alt);
	}
}


$arr_pic = $doc["picture"];
foreach($arr_pic as $dom_pic)
{
	$dpic = pq($dom_pic);

	// IGNORE the <img> inside <picture> here, was done above

	$arr_source = $dpic->find("source");
	foreach($arr_source as $dom_source)
	{
		$dsource = pq($dom_source);
		$src_source = trim($dsource->attr('srcset'));

		if(substr($src_source, 0, 9) == '../image/')
		{
			$arr_image_copy[] = $src_source;
		}
	}

	if($dpic->hasClass("make-mob"))
	{
		$dimg = $dpic->find("img");  // only one img can be in picture!,
		$fn_img = trim($dimg->attr('src'));
		$arr_image_mob[] = $fn_img;

		$ext = strtolower(pathinfo($fn_img, PATHINFO_EXTENSION));
		$fn_mob = substr($fn_img, 0, strlen($fn_img) - strlen($ext))."mob.".$ext;
	
		$dpic->prepend('<source media="(orientation: portrait)" srcset="'.$fn_mob.'" />');
	}			
}


$arr_img_head = $doc["meta,link"];
foreach($arr_img_head as $dom_img_head)
{
	$di_head = pq($dom_img_head);
	$name = strtolower($dom_img_head->nodeName);
	$src = "";

	$attr="";
	if($name == "meta")$attr='content';
	elseif($name == "link")$attr='href';

	$src = $di_head->attr($attr);

	if(substr($src, 0, 9) == "../image/")
	{
		$arr_image[] = $src;
	}
}


$arr_img = $doc["image"];  // that appears within inline svg (images in external svg are not supported!)
foreach($arr_img as $dom_img)
{
	$di = pq($dom_img);
	$src = trim($di->attr('href'));
	$pos = strpos($src, '/image/');
	if($pos > 0)
	{
		$fn = "..".substr($src, $pos);
		if(file_exists($fn))
		{
			$arr_image[] = $fn;
		}
	}
}




// Now $arr_image shall be complete (all HTML and CSS image usages within ../image/) 


// --------------------------------------------------------------------
// Image Handling.

@msg("--------------------------------------------------------- IMAGE MANAGEMENT ... ");


// remove all images from arrays that are inside /image/_o/ 

foreach($arr_image as $key => $src)
{
	if(substr($src, 0, 12) == '../image/_o/')
	{
		unset($arr_image[$key]); // Deleting within loop is supported by PHP. cool!
	}
}


foreach($arr_image_small as $key => $src)
{
	if(substr($src, 0, 12) == '../image/_o/')
	{
		unset($arr_image_small[$key]); // Deleting within loop is supported by PHP. cool! 
	}
}

foreach($arr_image_copy as $key => $src)
{
	if(substr($src, 0, 12) == '../image/_o/')
	{
		unset($arr_image_copy[$key]); // Deleting within loop is supported by PHP. cool!
	}
}


// ----------------------------

$arr_image = array_unique($arr_image); // Such a cool PHP-function, again Thumps Up for PHP

$arr_image_small = array_unique($arr_image_small);

$arr_image_copy = array_unique($arr_image_copy);

$arr_image_mob = array_unique($arr_image_mob);

$do_create_cache_image = 0;
$done_image = 0;

$do_resize_big = 0;
$do_resize_small = 0;

$MAX_W = 800;
$MAX_H = 800;

$MAX_W_SMALL = 400;
$MAX_H_SMALL = 400;


if(isset($GLOBALS['config']['image_resize_any']))
{
	if($GLOBALS['config']['image_resize_any'])
	{
		if(isset($GLOBALS['config']['image_resize_big']) && $GLOBALS['config']['image_resize_big'])
		{
			$do_resize_big = 1;

			if(isset($GLOBALS['config']['image_resize_big_w']) && $GLOBALS['config']['image_resize_big_w'])
			{
				$MAX_W = (int)$GLOBALS['config']['image_resize_big_w'];
				if($MAX_W < 100 || $MAX_W > 5000)
				{	
					warn("Invalid, Max Width given for image resize 'image_resize_w'. Fallback to 800");
					$MAX_W = 800;
				} 	
			}

			if(isset($GLOBALS['config']['image_resize_big_h']) && $GLOBALS['config']['image_resize_big_h'])
			{
				$MAX_H = (int)$GLOBALS['config']['image_resize_big_h'];
				if($MAX_H < 100 || $MAX_H > 5000)
				{	
					warn("Invalid, Max Height given for image resize 'image_resize_h'. Fallback to 800");
					$MAX_H = 800;
				} 	
			}
		}
	
		if(isset($GLOBALS['config']['image_resize_small']) && $GLOBALS['config']['image_resize_small'])
		{
			$do_resize_small = 1;

			if(isset($GLOBALS['config']['image_resize_small_w']) && $GLOBALS['config']['image_resize_small_w'])
			{
				$MAX_W_SMALL = (int)$GLOBALS['config']['image_resize_small_w'];
				if($MAX_W_SMALL < 50 || $MAX_W_SMALL > 5000)
				{	
					warn("Invalid, Max Width given for image resize 'image_resize_w'. Fallback to 400");
					$MAX_W_SMALL = 400;
				} 	
			}

			if(isset($GLOBALS['config']['image_resize_small_h']) && $GLOBALS['config']['image_resize_small_h'])
			{
				$MAX_H_SMALL = (int)$GLOBALS['config']['image_resize_small_h'];
				if($MAX_H_SMALL < 50 || $MAX_H_SMALL > 5000)
				{	
					warn("Invalid, Max Height given for image resize 'image_resize_h'. Fallback to 400");
					$MAX_H_SMALL = 400;
				} 	
			}
		}
	} 
} 

// -----------------------------------------------------------------------------------------

$arr_image_resized = [];
$arr_image_resized_small = [];

if(isset($GLOBALS['config']['image_cache_use']) && $GLOBALS['config']['image_cache_use'])
{
	if(!file_exists($dir_cache."image/") || !is_dir($dir_cache."image/"))
	{
		mkdir($dir_cache."image/");
		if(!file_exists($dir_cache."image/") || !is_dir($dir_cache."image/")) err("Could not create cache-image-dir");
		$do_create_cache_image = 1;
	}
	else
	{
		// check if cache is ok

		$cache_ok = 0;		
		$crc = crc32(var_export($arr_image, true).$do_resize_big.$MAX_H.$MAX_W.
					 var_export($arr_image_small, true).$do_resize_small.$MAX_H_SMALL.$MAX_W_SMALL.
					var_export($arr_image_mob).var_export($arr_image_copy));  

		$fp_crc = $dir_cache."image_crc.y7";
		if(file_exists($fp_crc))
		{
			$crc_prev = file_get_contents($fp_crc);
			if($crc_prev == $crc)
			{

				// CRC-Level means that at HTML and CSS were no relevant changes,
				// TOOO: Also check changes on file-level! 

				$cache_ok = 1;
			} 
			else
			{
				msg("CACHE: CRC-mismatch, not using cache.");
			}
		}

		if($cache_ok)
		{
			// perfect, just copy
			important("USING IMAGE CACHE!");

			copy_recurse($dir_cache."image/", $dir_release_image);
			$done_image = 1;

			$fp_res = $dir_cache."image_arr_resized.y7";
			if(!file_exists($fp_res))
			{
				err("A cache file is missing: ".$fp_res);
			}
			include($fp_res);

			$fp_small = $dir_cache."image_arr_resized_small.y7";
			if(!file_exists($fp_small))
			{
				err("A cache file is missing: ".$fp_small);
			}
			include($fp_small);
		}
		else
		{
			$do_create_cache_image = 1;
			delete_dir_content($dir_cache."image/");
			file_put_contents($fp_crc, $crc);
		}
	}
}

// ---------------------------------


if(!$done_image)
{
	$max_w = floor($MAX_W * 1.1);
	$max_h = floor($MAX_H * 1.1);

	foreach($arr_image as $fn_img)
	{
		$fn_img = windows_filename($fn_img);

		$ext = strtolower(pathinfo($fn_img, PATHINFO_EXTENSION));
		
		$ok_for_resize = 0;
		switch($ext)
		{
		case "jpg":
		case "jpeg":
		case "png":
			$ok_for_resize = 1;
			break;
		default:
			// Note: GIF is also not resized, to avoid animation- and trans-problems
		}

		$did_resize_big = 0;

		if($do_resize_big && $ok_for_resize)
		{
			$image = 0;
			$w = 0;
			$h = 0;
			try
			{
				$image = new ImageResize($dir_total.substr($fn_img, 3));
				$w = $image->getSourceWidth();
				$h = $image->getSourceHeight();
			}
			catch (Exception $e) 
			{ 
				@msg("ERROR in Image-Reading: ".$fn_img."  ERR: ".$e->getMessage()); 
			}

			$w_target = 0;
			$h_target = 0;

			if($w > $h)
			{
				// landscape
				if($w > $max_w)
				{
					$w_target = $MAX_W;
				}				
			}
			else
			{
				if($h > $max_h)
				{
					$h_target = $MAX_H;
				}
			}

			if($w_target > 0 || $h_target > 0)
			{
				try
				{
					$image = new ImageResize($fn_img);
					if($w_target > 0) $image->resizeToWidth($w_target);
					if($h_target > 0) $image->resizeToHeight($h_target);

					$fp_target = $dir_release_image.substr($fn_img, 9);	
					dir_ensure($fp_target);
					$image->save($fp_target, null, 66);
					msg("Did Resize of Image: ".$fn_img); 

					$arr_image_resized[] = $fn_img; 
					$did_resize_big = 1;
				}
				catch (Exception $e) 
				{ 
					@msg("ERROR in ImageResize: ".$fn_img."  ERR: ".$e->getMessage()); 
				}
				// Note: We do NOT copy the bigger version over her, but maybe further down at 'image_link_to_big'
			}
		}

		if(!$did_resize_big)
		{
			copy_ensurepath($fn_img, $dir_release_image.substr($fn_img, 9));
		}

		// now work for small
		if(array_search($fn_img, $arr_image_small) !== false)
		{
			if($do_resize_small && $ok_for_resize)
			{
				$image = 0;
				$w = 0;
				$h = 0;
				try
				{
					$image = new ImageResize($dir_total.substr($fn_img, 3));
					$w = $image->getSourceWidth();
					$h = $image->getSourceHeight();
				}
				catch (Exception $e) 
				{ 
					@msg("ERROR in Image-Reading: ".$fn_img."  ERR: ".$e->getMessage()); 
				}

				$w_target = 0;
				$h_target = 0;

				if($w > $h)
				{
					// landscape
					if($w > ($MAX_W_SMALL * 1.1))
					{
						$w_target = $MAX_W_SMALL;
					}				
				}
				else
				{
					if($h > ($MAX_H_SMALL * 1.1))
					{
						$h_target = $MAX_H_SMALL;
					}
				}

				if($w_target > 0 || $h_target > 0)
				{
					try
					{
						$image = new ImageResize($fn_img);
						if($w_target > 0) $image->resizeToWidth($w_target);
						if($h_target > 0) $image->resizeToHeight($h_target);


						$ext = pathinfo($fn_img, PATHINFO_EXTENSION);	
						$fn_small = substr($fn_img, 0, strlen($fn_img) - strlen($ext))."sm.".$ext;

						$fp_small = $dir_release_image.substr($fn_small, 9);

						dir_ensure($fp_small);
						$image->save($fp_small, null, 66);

						msg("Did Resize of Image SMALL: ".$fn_small); 

						$arr_image_resized_small[] = $fn_img;  // not the small here
					}
					catch (Exception $e) 
					{ 
						@msg("ERROR in ImageResize: ".$fn_img."  ERR: ".$e->getMessage()); 
					}
					// Note: We do NOT copy the bigger version over her, but maybe further down at 'image_link_to_big'
				}
			}
		}
	} // end of for_arr_image (with big/small)

	// -------------------

	foreach($arr_image_copy as $fn_img)
	{
		$fn_img = windows_filename($fn_img);

		msg("Regular Image copy: ".$fn_img); 

		copy_ensurepath($dir_total.substr($fn_img, 3), $dir_release_image.substr($fn_img, 9));
	}

	// -------------------

	foreach($arr_image_mob as $fn_img)
	{
		$fn_img = windows_filename($fn_img);

		$ext = strtolower(pathinfo($fn_img, PATHINFO_EXTENSION));
		
		$ok_for_cut = 0;
		switch($ext)
		{
		case "jpg":
		case "jpeg":
		case "png":
			$ok_for_cut = 1;
			break;
		default:
			// Note: GIF is also not resized, to avoid animation- and trans-problems
		}

		// ------------------- now work for mobile images (those that were specified by <picture> and portrait)
		
		$done_mob = 0;

		$fn_mob = substr($fn_img, 0, strlen($fn_img) - strlen($ext))."mob.".$ext;
		$fp_mob_target = $dir_release_image.substr($fn_mob, 9);

		if(file_exists($dir_total.substr($fn_mob, 3)))
		{
			msg("Mobile file to create already exists: ".$fn_mob);
			$fn_img  = $fn_mob;  // so the mob is copied to release below
		}
		else if($ok_for_cut)
		{
			try
			{
				$image = new ImageResize($fn_img);
				$w_source = $image->getSourceWidth(); 
				$h_source = $image->getSourceHeight(); 
				
				if($w_source > $h_source)
				{
					$w_target = $h_source * 0.8;   // TODO: This facor is dangerous!!! Good strategy?!

					$image->crop($w_target, $h_source, false, ImageResize::CROPCENTER);
					 	
					dir_ensure($fp_mob_target);
					$image->save($fp_mob_target, null, 66);
	
					msg("Did Resize to MOBILE Image: ".$fn_mob); 
	
					$done_mob = 1;		
				}	
				else
				{
					// if h==w or even portrait already, skip resize mob
					msg("SKIP ImageResize for Mobile: ".$fn_mob); 
				}
			}
			catch (Exception $e) 
			{ 
				@msg("ERROR in ImageResize: ".$fn_img."  ERR: ".$e->getMessage()); 
			}
		}

		if(!$done_mob)
		{
			// Otherwise for whatever reason, just make a copy
			msg("Fallback: Just COPY Mobile File: ".$fn_mob); 
			copy_ensurepath($fn_img, $fp_mob_target);
		}

	}

	// -------------------

	if($do_create_cache_image)
	{
		file_put_contents($dir_cache."image_arr_resized.y7", 
						'<?php $arr_image_resized='.var_export($arr_image_resized, true).";\n");

		file_put_contents($dir_cache."image_arr_resized_small.y7", 
						'<?php $arr_image_resized_small='.var_export($arr_image_resized_small, true).";\n");						

		copy_recurse($dir_release_image, $dir_cache."image/");  // Here the Image-Cache is created
	}
}

// The folder /image/_o/ is just copied over 'as is'
if(file_exists('../image/_o'))
{
	copy_recurse('../image/_o', $dir_release_image.'_o');
}

// ---------------------------------------------------------------------------

// Now, images are done on file-level, go an adapting <img> on html-level
// Note: Only <img> elements are adapted. Using <picture> you get more
// control yourself about resolutions a.s.o.

$arr_img = $doc["img"];
foreach($arr_img as $dom_img)
{
	$di = pq($dom_img);

	if($di->parent()->is("picture"))
	{
		continue;		
	}

	$fn_img = trim($di->attr('src'));

	if(substr($fn_img, 0, 9) == "../image/")
	{
		if(array_search($fn_img, $arr_image) === false)
		{
			err("Found <img> image thats not correctly in arr_image! FN: ".$fn_img);
		}

		$use_small = 0;
		if(array_search($fn_img, $arr_image_resized_small) !== false)
		{
			if($di->hasClass('resize-small'))
			{
				$use_small = 1;
			}
			else
			{
				$dp_box = $di->parents(".box-grid");
				if($dp_box->length())
				{
					if($dp_box->hasClass('resize-small')) $use_small = 1; 
				}
			}
		}

		if(!$use_small)
		{	
			if(array_search($fn_img, $arr_image_resized) === false)
			{
				// image wasn't resized, so resizing and linking is no topic here any more 
				continue;
			}
		}

		// The images here have been resized

		$no_link = $di->hasClass('no-link-to-big');
		$no_resize = $di->hasClass('no-resize');

		if(!$no_link || !$no_resize)
		{
			$dp_box = $di->parents(".box-grid");
			if($dp_box->length())
			{
				if($dp_box->hasClass('no-link-to-big')) $no_link = 1; 
				if($dp_box->hasClass('no-resize')) $no_resize = 1; 
			}
		}	

		if($no_resize && $no_link)
		{
			if($use_small)
			{
				warn("Image has contradicting classes: '.resize-small' and also '.no-resize'. Ignoring.");
			}
			continue;
		}


		$ext = pathinfo($fn_img, PATHINFO_EXTENSION);	
		
		$fn_big = "";
		if($use_small)
		{
			$fn_small = substr($fn_img, 0, strlen($fn_img) - strlen($ext))."sm.".$ext;
			$fp_small = $dir_release_image.substr($fn_small, 0, 9);
			if(!file_exists($fp_small))
			{
				err("Small image not found, but has to exist: ".$fp_small);
			}
			$di->attr('src', $fn_small);
			$fn_big = $fn_img;
		}
		else
		{
			$fn_big = substr($fn_img, 0, strlen($fn_big) - strlen($ext))."big.".$ext;
			$fp_big = $dir_release_image.substr($fn_big, 0, 9);
			copy($fn_img, $fp_big);
			if($no_resize)
			{
				$di->attr('src', $fn_big);
			}	
		}

		if(!$no_link)
		{
			$di->wrap("<a href='".$fn_big."' class='a-img-bigger'>");
		}
	}
}



