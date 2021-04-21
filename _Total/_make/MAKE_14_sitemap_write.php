<?php

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();

// ------------------------------------------------------------
@msg("Creation of SITEMAP XML ... ");


$xml_file = "<?xml version='1.0' encoding='UTF-8'?>
<urlset xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'
xmlns:image='http://www.google.com/schemas/sitemap-image/1.1'
xsi:schemaLocation='http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd'
xmlns='http://www.sitemaps.org/schemas/sitemap/0.9' xmlns:xhtml='http://www.w3.org/1999/xhtml'>";

foreach($XML_SITEMAP as $sitemap_entry)
{
	$xml_file .= "
	<url>
	<loc>".$sitemap_entry['loc']."/".$lang_default."/".(isset($sitemap_entry['section'])?$sitemap_entry['section'].".htm":"")."</loc>
	<lastmod>".$sitemap_entry['lastmod']."</lastmod>
	<changefreq>".$sitemap_entry['changefreq']."</changefreq>
	<priority>".$sitemap_entry['priority']."</priority>";


	if($multilang) foreach($arr_lang as $lang)
	{
			$xml_file .= "
			<xhtml:link rel='alternate' hreflang='".($lang == $lang_default ? "x-default" : $lang)."' href='".$sitemap_entry['loc']."/".$lang."/".(isset($sitemap_entry['section'])?$sitemap_entry['section'].".htm":"")."'/>";
	}

	$xml_file .= "
	</url>
	";
}

$xml_file .= "
</urlset>
";

$filenameSitemap = $dir_release."sitemap.xml";

file_put_contents($filenameSitemap,$xml_file);
