<?php

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();


// --------------------------------------------------------------
// --------------------------------------------------------------
// --------------------------------------------------------------

$do_section_lazy = 0;
if(isset($GLOBALS['config']['section_lazy_any']) && $GLOBALS['config']['section_lazy_any'])
{
    $do_section_lazy = 1;
}

$lazy_readmore_mode = 0;
if($do_section_lazy && 
    isset($GLOBALS['config']['section_lazy_readmore_mode']) && 
    $GLOBALS['config']['section_lazy_readmore_mode'])
{
    $lazy_readmore_mode = 1;
}

$lazy_min_char = 0;
if($do_section_lazy && 
    isset($GLOBALS['config']['section_lazy_min_char']) && 
    $GLOBALS['config']['section_lazy_min_char'])
{
    $lazy_min_char = (int) ($GLOBALS['config']['section_lazy_min_char']);
}

// Prepare readmore-html
$lls_rm = isset($_LLS['readmore']) ? $_LLS['readmore'] : '...';
$ht_rm = '<span><lang lang="'.$lang_default.'">'.$lls_rm.'</lang>';

foreach($arr_lang as $lang)
{
    if($lang == $lang_default) continue; 
    $lld_rm = (isset($_LLD[$lang]) && isset($_LLD[$lang][$lls_rm])) ? $_LLD[$lang][$lls_rm] : $lls_rm;     
    $ht_rm .= '<lang lang="'.$lang.'">'.$lld_rm.'</lang>';
}
$ht_rm .= '</span>';

foreach($SL as $id_section)
{
	$SO[$id_section]['html'] = array();
	$SO[$id_section]['html']['all'] = "";

	$d_section = $doc["#".$id_section];

	$d_section->addClass("ps-".($SO[$id_section]['p']));

	$ok = 0;
	$d_section_body = $d_section->find("div.section-body:first");
	if($d_section_body->length())
	{
		$arr_parents = $d_section_body->parents("section");
		if($arr_parents->length())
		{
			$id_parent = pq($arr_parents->get(0))->attr("id");
			if($id_parent == $id_section) 
            {
                $ok=1;
            }
		}

		// $d_section_body->after("<div class='clear-both'></div>");
	}

	if($ok)
	{
		// $d_section_body->addClass("section-body-".$id_section);

        // TODO: What means not ok really here? 

        // -------------------------------------
        // Do some work with content that would have to be done at DYN otherwise 

        $arr_rm = $d_section_body->find(".readmore");
        foreach($arr_rm as $dom_rm)
        {
            $d_rm = pq($dom_rm);

            $dx = $d_rm->next();
            while($dx->length())
            {
              $dx->addClass("hide hide-readmore");
              if($dx->hasClass("readmore")) break;
              $dx = $dx->next();
            }
            // Readmore-language is done later
            $d_rm->html($ht_rm);
            $d_rm->attr("onclick","readmore.call(this,event)");
        }

        // -------------------------------------

		$filenameSection = $dir_total."htm/section_".$id_section.".htm";

		$SO[$id_section]['html']['all'] = $d_section_body->html();

		/*
		$html = clean_htm($SO[$id_section]['html']['all']);
		if($GLOBALS['config']['minify_any'] && $GLOBALS['config']['minify_htm']) $html = mini_htm($html);

		file_put_contents($filenameSection, $html);

		$d_section_body->html("<?php if(\$all||isset(\$SV['".$id_section."'])) include(\"htm/section_{\$prefix_lang}".$id_section.".htm\"); ?>");

		*/

        if($do_section_lazy)
        {
            $arr_class = explode(" ", $d_section->attr("class"));
            
            $skip_lazy = 0;

            if(in_array("no-lazy", $arr_class))
            {
                // keep content
                $skip_lazy = 1;
            }
            else
            {
                // make section lazy... 
 
                if($lazy_min_char > 0)
                {
                    $tx = '';
                    if(count($arr_lang) > 1)
                    {
                        // in multi-lang-mode test length of default-language
                        $tx = $d_section_body['lang[lang="'.$lang_default.'"]']->text();
                    }
                    else
                    {
                        $tx = $d_section_body->text();
                    }

                    if(strlen($tx) < $lazy_min_char)
                    {
                        // and ensure no image 
                        if(count($d_section_body["img"]) < 1)
                        {
                            msg("Skipping LAZY section, because of small text length and no image: ".$id_section);
                            $skip_lazy = 1;
                        }
                    }
                }
            }
 
            if(!$skip_lazy)
            {
                //  make hidden anchors explicit

                $arr_id = $d_section_body["*[id]"]; // any element with id is a possible anchor elsewhere
                foreach($arr_id as $dom_id)
                {
                    $d_id = pq($dom_id);
                    $idca = trim($d_id->attr("id"));
                    if(empty($idca)) continue;
                    $arr_a = $doc['a[href="#'.$idca.'"]'];
                    if(count($arr_a))
                    {
                        // see if all of this links are just in the same <section>
                        $arr_a2 = $d_section_body['a[href="#'.$idca.'"]'];
                        if(count($arr_a) - count($arr_a2) > 0)
                        {
                            // This might be a relevant href!!
                            @msg("Relevant link into section-body, for lazy loading, id: ".$idca);
                            $d_section->addClass("ca--".$idca);
                        }                
                    }
                }
 
                $d_section->addClass("unloaded");

                if($lazy_readmore_mode)
                {
                    $drm = $d_section_body[".readmore:first"];
                    if(count($drm))
                    {
                        $drem = $drm->next();
                        while(count($drem))
                        {
                          $dremd = $drem;   
                          $drem = $drem->next();
                          $dremd->remove();  
                        } 
                        $d_section->addClass("unloaded-rm");

                        msg("Kept section content until readmore: ".$id_section);
                    }
                    else
                    {
                        $d_section_body->empty();
                    }
                }
                else
                {
                    $d_section_body->empty();
                }
            }
        }
	}
}


