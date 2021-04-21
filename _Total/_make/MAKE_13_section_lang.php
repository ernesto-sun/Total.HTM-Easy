<?php

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();



// --------------------------------------------------------------------
function PREPARE_DOC_LANG($doc_lang, $lang, $multilang, $arr_dhl, $arr_lang, $loc, $lang_default)
{
	if($multilang)
	{
		$doc_lang['head']->prepend( $arr_dhl[$lang] );
		$doc_lang['html']->attr( "lang", $lang );
	}

	$doc_lang['body']->attr("lang", $lang)->addClass("lang-".$lang);

	$doc_lang['lang[lang!="'.$lang.'"]']->remove();

	$arr_lang_elem = $doc_lang['lang'];

	foreach($arr_lang_elem as $lang_elem)
	{
		$d_lang = pq($lang_elem);

		$d_lang->after($d_lang->html()); // ?!
		$d_lang->remove();
	}

	foreach($arr_lang as $langSub)
	{
		// TODO: ?? Clever?
		$doc_lang["head"]->prepend("<link rel='alternate' hreflang='".($langSub==$lang_default?"x-default":$langSub)."' href='".$loc."/".$langSub."/'/>");
	}

}



// --------------------------------------------------------------------

// Note: In MAKE_01_setting.js the following vars are set. At least one language must be set.
// $arr_lang = ["en"];
// $lang_default = "en";

$arr_dhl = array();   // DOM of lang-head  

$multilang = 0;

if(count($arr_lang) > 1)
{
	@msg("MULTI-LANGUAGE-MODE ...");
	$multilang = 1;

	foreach($arr_lang as $lang)
	{
		$arr_dhl[$lang] = $doc['head lang[lang="'.$lang.'"]'];
		if(!$arr_dhl[$lang]->length())
		{
			err("Missing language header (head > lang): {$lang}!");
		}
	}
	$doc['head lang']->remove();
}
else
{
	@msg("SINGLE-LANGUAGE-MODE: ",$arr_lang[0]," ...");
}

$html_temp = $doc->html();

foreach($arr_lang as $lang)
{
	@msg("LANGUAGE {$lang} ...");

	$doc_lang = phpQuery::newDocumentHTML($html_temp);

	PREPARE_DOC_LANG($doc_lang, $lang, $multilang, $arr_dhl, $arr_lang, $loc, $lang_default);

	$script_top0 = $doc_lang["#script-top0"];
	if($script_top0->length())
	{
		$script_top0->append('_lang="'.$lang.'";');
	}
	else
	{
		$doc_lang["head"]->append('<script>_lang="'.$lang.'";</script>');
	}
	
	// ----------------------------------

	$dir_lang = $dir_release.$lang."/";
	if(!is_dir($dir_lang)) mkdir($dir_lang);
	else delete_dir_content($dir_lang);

	// favicon to language

	copy($dir_total."_HTM/favicon.ico", $dir_lang."favicon.ico");

	// Write each section in each language

	foreach ($SL as $id_section)
	{
		// @msg("SEC-LANG {$lang} {$id_section}");

		$secLang = phpQuery::newDocumentHTML($SO[$id_section]['html']['all']);

		$secLang['lang[lang!="'.$lang.'"]']->remove();
		$arr_lang_elem = $secLang['lang'];
		foreach($arr_lang_elem as $lang_elem)
		{
			$d_lang = pq($lang_elem);

			$d_lang->after($d_lang->html());
			$d_lang->remove();
		}

		$html_section = $secLang->html();

		if($do_minify_html)
		{
			$html_section = mini_htm($html_section);
		} 

		$SO[$id_section]['html'][$lang] = $html_section;  // section in each language stored

		$filenameSecLang = $dir_lang."section_".$id_section.".htm";

		file_put_contents($filenameSecLang, $html_section);

		// --------------------------------------------------------------------

		/*
		$doc_lang_section = phpQuery::newDocumentHTML($doc_lang->html());  // just for adding the one section

		$doc_lang_section['#'.$id_section." .section-body:first"]->prepend($html_section);

		$doc_lang_section['#'.$id_section]->removeClass("hide")->parents("section")->removeClass("hide");

		foreach($arr_lang as $langSub)
		{
			// TODO: ??? Is this clever:
			$doc_lang_section["head"]->prepend("<link rel='alternate' hreflang='".($langSub==$lang_default?"x-default":$langSub)."' href='".$loc."/".$langSub."/".$id_section.".htm'/>");
		}


		$html_lang_section = $doc_lang_section->html();
		if($do_minify_html) 
		{
			$html_lang_section = mini_htm($html_lang_section);
		}

		$filenameSecLangIndividual = $dir_lang.$id_section.".htm";

		file_put_contents($filenameSecLangIndividual, $html_lang_section);
		*/

	}

	// ------------------------------------------------------------------------------

	if($doc_nojs)
	{
		// ----------------------- write NO-JS File s.htm 
		$doc_nojs_lang = phpQuery::newDocumentHTML($doc_nojs);
		PREPARE_DOC_LANG($doc_nojs_lang, $lang, $multilang, $arr_dhl, $arr_lang, $loc, $lang_default);
		$html_nojs = $doc_nojs_lang->html();
		$fn_html_nojs = $dir_lang."s.htm";
		if($do_minify_html) 
		{
			$html_nojs = mini_htm($html_nojs);
		}
		file_put_contents($fn_html_nojs, $html_nojs);
	}

	// ------------------------------------------------------------------------------
	// create index...

	$ds_main = $doc_lang["main"];

	$ids0 = $SL[0];

	$ds0 = $doc_lang['#'.$ids0];
	$dsb0 = $ds0[".section-body:first"]; 
	
	$dsb0->html($SO[$ids0]['html'][$lang]);
	$ds0->removeClass("hide unloaded")->parents("section")->removeClass("hide");


	if($do_lazy_main)
	{
		if(isset($GLOBALS['config']['lazy_main_by_image_only']) && $GLOBALS['config']['lazy_main_by_image_only'])
		{
			$ds_main["img"]->attr("loading", "lazy");
			$ds0["img"]->removeAttr("loading");  // First section is 'above the fold', no lazy loading here		
		}
		else
		{
			$html_s0 = $ds0->html(); 
			$ds0->remove();
			$html_main = $ds_main->html();
			$fn_html_main = $dir_lang."lazy_main.htm";
			if($do_minify_html) 
			{
				$html_main = mini_htm($html_main);
			}
			file_put_contents($fn_html_main, $html_main);
			$ds_main->html($html_s0);
			$ds_main->addClass("unloaded");
		}
	}

	if($do_lazy_footer)
	{
		$ds_footer = $doc_lang["footer"];

		if(isset($GLOBALS['config']['lazy_footer_by_image_only']) && $GLOBALS['config']['lazy_footer_by_image_only'])
		{
			$ds_footer["img"]->attr("loading", "lazy");		
		}
		else
		{
			$fn_html_footer = $dir_lang."lazy_footer.htm";
			$html_footer = $ds_footer->html();
			if($do_minify_html) 
			{
				$html_footer = mini_htm($html_footer);
			}
			file_put_contents($fn_html_footer, $html_footer);
			$ds_footer->html("");
			$ds_footer->addClass("unloaded");
		}
	}

	if($do_lazy_mm)   // main menu
	{
		// only possible if menu is on-click 
		if($doc_lang["body"]->hasClass("menu-onclick"))
		{
			$dmm = $doc_lang["#menu-main"];
			$ht_mm = $dmm->html();
			$dmm->empty();	
			$dmm->html('<template>'.$ht_mm.'</template>');
			$dmm->addClass("unloaded");
		}
	}
	else
	{
		// TODO
		$dmm = $doc_lang["#menu-main"];
		$dmm["img"]->attr("loading","lazy");		
	}


	if($do_lazy_mh)   // main hamburger
	{
		$dmh = $doc_lang["#menu-hamburger"];
		$ht_mh = $dmh->html();
		$dmh->empty();	
		$dmh->html('<template>'.$ht_mh.'</template>');
		$dmh->addClass("unloaded");
	}
	else
	{
		// TODO
		$dmh = $doc_lang["#menu-hamburger"];
		$dmh["img"]->attr("loading","lazy");		
	}

	if($do_lazy_ll0)   // language-list at topline
	{
		$dl0 = $doc_lang['#topline .lang-list:first'];
		if(count($dl0))
		{
			$dl0_cur = $dl0->find('.lang-'.$lang);
			if(count($dl0_cur))
			{
				$ht_cur = $dl0_cur->htmlOuter();
				$dl0_cur->remove();
				$ht_templ = $dl0->html();
				$dl0->empty();
				$dl0->html($ht_cur.'<template>'.$ht_templ.'</template>');
				$dl0->addClass("unloaded");
	
				$doc_lang["head"]->append('<link rel="preload" href="'.$dl0_cur->attr('src').'" as="image" />');
			} 
			else
			{
				warn("lazy_lang_list_0 found but current language-flag not found within: ".$lang);
			}
		}
		else
		{
			warn("lazy_lang_list_0 is active but no language list found in topline, lang: ".$lang);
		}
	}
	else
	{
		// TODO
		$dl0 = $doc_lang['#topline .lang-list:first'];
		$dl0["img"]->attr("loading","lazy");		
	}

	$f_html_index = $dir_lang."index.htm";
	$html_index = clean_htm($doc_lang->html());

	if($do_minify_html) 
	{
		$html_index = mini_htm($html_index);
	}

	file_put_contents($f_html_index, $html_index);
}
