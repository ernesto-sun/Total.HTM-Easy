<?php

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();


copy($dir_htm."favicon.ico", $dir_release."favicon.ico");
copy($dir_htm."manifest.json", $dir_release."manifest.json");
copy($dir_htm."robots.txt", $dir_release."robots.txt");
copy($dir_htm."humans.txt", $dir_release."humans.txt");

if(isset($GLOBALS['config']['use_service_worker']) && $GLOBALS['config']['use_service_worker'])
{
	// TODO:  Adapt template a.s.o.
	copy($dir_make."js/_sw.js",$dir_release."_sw.js");
}


$dir_release_img = $dir_release."img/";
if(!is_dir($dir_release_img)) mkdir( $dir_release_img );

$dir_release_image = $dir_release."image/";
if(!is_dir($dir_release_image)) mkdir( $dir_release_image );

copy_recurse($dir_total."font/", $dir_release."font/");

// Copy the static assets recursively
copy_recurse($dir_total."s/", $dir_release."s/");

dir_ensure($dir_release.'js/');

if(is_dir($dir_total."js/_lazy/"))
{
	// just copy all lazy-JS-files because it would be hard to find out what JS is loaded asynch lazy somewhere in runtime
	copy_files($dir_total."js/_lazy/", $dir_release."js/_lazy/");
}

if(is_dir($dir_total."js/_3p/"))
{
	// just copy all 3p-JS-files as well
	copy_recurse($dir_total."js/_3p/", $dir_release."js/_3p/");
}



if(is_dir($dir_total."css_lazy/"))
{
	// just copy all lazy-CSS-files. Note: No sub-directories!!!
	copy_files($dir_total."css_lazy/", $dir_release."css_lazy/");
}


// ----------------------------

if(is_dir($dir_total."img/_but/")) copy_files($dir_total."img/_but/", $dir_release."img/_but/");
if(is_dir($dir_total."img/_icon/")) copy_files($dir_total."img/_icon/", $dir_release."img/_icon/");
if(is_dir($dir_total."img/_lang/")) copy_files($dir_total."img/_lang/", $dir_release."img/_lang/");
if(is_dir($dir_total."img/_svg/")) copy_files($dir_total."img/_svg/", $dir_release."img/_svg/");
if(is_dir($dir_total."img/_sym/")) copy_files($dir_total."img/_sym/", $dir_release."img/_sym/");
if(is_dir($dir_total."img/_sys/")) copy_files($dir_total."img/_sys/", $dir_release."img/_sys/");
