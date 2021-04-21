<?php

$doc_nojs = phpQuery::newDocumentHTML($doc->html());

// By now images are set, but no lazy images and no lazy sections may be there, care for non-dynamic

$doc_nojs['.readmore']->remove();

$doc_nojs['.toggle > .hide:nth-child(2)']->removeClass('hide');
$doc_nojs['.toggle > .hide:nth-child(3)']->removeClass('hide');
$doc_nojs['.toggle']->removeClass('toggle');

$doc_nojs['section.hide']->removeClass('hide');

$doc_nojs['script']->remove();
$doc_nojs['noscript']->remove();

$doc_nojs['*[onclick]']->removeAttr('onclick');

// Make language-flags in footer clickable

$arr_img_lang = $doc_nojs['#footer .lang-list img'];
foreach($arr_img_lang as $dom_img_lang)
{
    $d_img_lang = pq($dom_img_lang);
    $lang_i = $d_img_lang->attr('data-lang'); 

    $d_img_lang->wrap('<a href="../'.$lang_i.'/s.htm">');
} 


$doc_nojs['#div-script-main']->remove();

$arr_prel = $doc_nojs['link[as="script"]']->remove();



