<?php

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();


$SL = array();
$SO = array();
$SP = array();

$loc=".";
$d_loc = $doc["link[rel='canonical']:first"];
if($d_loc->length())
{
	$d_loc = pq($d_loc->get(0));
	$loc = $d_loc->attr("href");
}
else err("Total.HTM needs this in the head: <link rel='canonical' href='your-site.com' />");

$XML_SITEMAP = array(array("loc" => $loc,
					"changefreq" => "monthly",
					"priority" => 1,
					"lastmod" => date('c')));

$i_section = 0;

$arr_section = $doc["main section"];

if($arr_section->length())
foreach($arr_section as $dom_section)
{
	$d_section = pq($dom_section);
	$id = $d_section->attr("id");

	$SL[] = $id;
	$i_section++;

	@msg("SECTION {$id} ... ");

	$id_parent = "main";

	$arr_parents = $d_section->parents("section");

	$cc = $arr_parents->length();

	if($cc > 0)
	{
		$id_parent = pq($arr_parents->get(0))->attr("id");

		$SP[ $id] = array();
		foreach($arr_parents as $dom_parent) $SP[$id][] = pq($dom_parent)->attr('id');
	}


	$freq="yearly";
	$data_freq=$d_section->attr("data-changefreq")."";
	if(!empty($data_freq))
	{
		$freq = trim(strtolower($data_freq));
		switch($freq)
		{
			case "always":
			case "hourly":
			case "daily":
			case "weekly":
			case "monthly":
			case "yearly":
			case "never":
				// ok
			break;
			default:
				warn("Wrong change-freq´in section: ".$id);
				$freq="yearly";
		}
	}

	$priority=0.79;
	$data_priority=floatval($d_section->attr("data-priority"));
	if($data_priority != 0)
	{
		$val = floatval($data_priority);
		if($val > 1.0) $val = 0.8;
		if($val < 0.0) $val = 0.3;
		$priority = $val;
	}

	$skipInSitemap = 0;


	if($id=="main")
	{
		$skipInSitemap = 1;
	}


	if(!$skipInSitemap)
	{
		$XML_SITEMAP[] = array("loc" => $loc,
					"section" => $id,
					"changefreq" => $freq,
					"priority" => $priority,
					"lastmod" => date('c'));    // TODO: calculate real lastmod

	}

	$SO[$id]=array("s"=>array(),
							"p"=>$id_parent,
							"i"=>$i_section);



	$SO[$id_parent]['s'][] = $id;

}
