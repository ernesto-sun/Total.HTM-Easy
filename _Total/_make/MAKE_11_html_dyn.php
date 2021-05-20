<?php

if (!isset($GLOBALS["indexOk"]) || $GLOBALS["indexOk"] != 815) die();


if(0) 
{
    // Note: That link-catcher is realized dynamically at DYN(). it would make to much HTML-text for little
    // The global link catcher
    $arr_a = $doc["a"];
    foreach($arr_a as $dom_a)
    {
        $d_a = pq($dom_a);
        $onclick = $d_a->attr("onclick");
        if(strlen($onclick) < 1)
        {
            $d_a->attr("onclick", "aclick.call(this,event)");
        }
    } 
}



// -----------------------------------------------------------------------------------

$arr_img_lang = $doc['.lang-list img[data-lang]'];
foreach($arr_img_lang as $dom_il) 
{
    $d_il = pq($dom_il);
    $d_il->attr("onclick", "lang_fclick.call(this,event)");
}

// -----------------------------------------------------------------------------------

$arr_li = $doc["ul.dropdown > li, li.dropdown"];

//  --- menu dropdown per JS
foreach($arr_li as $dom_li)
{
    $d_li = pq($dom_li);

    if($d_li->hasClass("dropdown-css")) continue;

    $d_sub = $d_li->children(".li-sub:first");
    if($d_sub->length())
    {
        $d_li->addClass("dropdown-js");

        $css = trim($d_sub->attr("style")).';display:block;';
        $d_sub->attr("style",$css);
        $d_sub->addClass("hide");

        $d_a = $d_li->children(".li-line:first")->children("a:first");
        if($d_a->length())
        {
            $d_a->attr("ondblclick", "aclick.call(this,event)");
            $d_a->attr("onclick", "dropdown_click.call(this,event)");  // ovrwriting aclick from above
        }
    }    
}



// ---------------------------------------------
// toggle structures (toggles class hide on the second & third child! Assuming first child is the clickable header)

$arr_toggle = $doc['.toggle'];

foreach($arr_toggle as $dom_toggle)
{
    $d_toggle = pq($dom_toggle);
    $ac = $d_toggle->children();

    if($ac->length() > 1)
    {
        $d_head = pq($ac->get(0));
        $d_body = pq($ac->get(1));

        $d_head->attr("onclick", "toggle.call(this,event)");

        if($d_body->hasClass("hide"))
        {
            $d_toggle->addClass("toggle-out");
        }
        else
        {
            $d_toggle->addClass("toggle-in");
        }
    }
    else
    {
        warn("Found toggle-element with less than 2 children.");
    }
}


// -----------------------------------------
  // flip-effect

$arr_flip = $doc[".flip"];

foreach($arr_flip as $dom_flip)
{
    $d_flip = pq($dom_flip);
    $d_flip->parent()->addClass("flip-outer");
    $d_flip->attr("onclick", "flip_click.call(this,event)");
}



// -----------------------------------------
// Add class obs to all elements that need the JS-Observer
$arr_obs = $doc["#header, #footer, section, .box-grid, .observe, .parallax, .fly-in"];
foreach($arr_obs as $dom_obs) 
{
    $d_obs = pq($dom_obs);
    $d_obs->addClass("obs");
} 


// -------------------------------------
$arr_s = $doc["section"];
foreach($arr_s as $dom_s) 
{
    $d_s = pq($dom_s);

    if(!$d_s->hasClass("toggle"))
    {
        $d_t = pq($d_s->children()->get(0));

        $onc = $d_t->attr("onclick");
        if(!strlen($onc))
        {
            $d_a = $d_t->find("a:first");
            if(!$d_a->length())
            {
                $d_a = $d_t->find("*[onclick]");
                if(!$d_a->length())
                {
                    $d_h = $d_t->find("h2, h3, h4, h5");
                    if($d_h->length())
                    {
                        $drm = $d_s->find(".readmore");
                        if($drm->length())
                        {
                            $d_h->addClass("cursor-pointer");
                            $d_h->attr("onclick", "titclick.call(this,event)");
                        }
                    }
                }
            }
        }
    }
}