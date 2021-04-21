<?php

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();


// --------------------------------------------------------------------
// ----      LAZY LOAD for Boxes

// Only images within sliders, box-grids make sense to be loaded lazy,
// because otherwise whole sections or areas of images are loaded lazy. 


if(isset($GLOBALS['config']['image_lazy_slider']) && $GLOBALS['config']['image_lazy_slider'])
{

msg('LAZY IMAGE TRANSFORMING (only slider images!) ... ');

$arr_cont = $doc['.box-grid.lazy-image'];
foreach ($arr_cont as $dom_cont)
{
	$d_cont = pq($dom_cont);

	$c_nonlazy = 1;
	if($d_cont->hasClass('box-grid-quarter')) $c_nonlazy = 4;
	else if($d_cont->hasClass('box-grid-third')) $c_nonlazy = 3;
	else if($d_cont->hasClass('box-grid-half')) $c_nonlazy = 2;

	$cc_nonlazy = $c_nonlazy;
	$cc_lazy = 0;

	$d_body = $d_cont->children('.box-grid-body:first'); 
	$arr_box = $d_body->children('.box');

	$ii = 0;
	foreach ($arr_box as $dom_box)
	{
		$d_box = pq($dom_box);

		$ii++;
		if($c_nonlazy > 0) 
		{ 
			$c_nonlazy--; 
			continue; 
		}

		$d_pic = $d_box->find('picture:first');
		if($d_pic->length() < 1) $d_pic = $d_box->find('img:first');

		if($d_pic->length())
		{
			$d_pic->wrap('<template class="unloaded">');
			$cc_lazy++;
		}
	}

	if($cc_lazy > 0)
	{
		if($d_cont->hasClass("play"))
		{
			$d_cont->addClass("play-js-only"); // lazy loading without js would not do the job
		}
	}
}

}

// --------------------------------------------------------------------
