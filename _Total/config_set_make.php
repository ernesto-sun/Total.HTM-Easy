<?php if(!isset($ok_come_from_config_script)) die();

// ---------------------------------------------------------------------------
// This is the config-file for the make-script. /_make/index.php  
// ---------------------------------------------------------------------------

// Note: relative paths refer to '/_Total/_make/'  E.g. '../' refers to '/_Total/'
// Note: xr_release must be the URI to the real online website '/_Total/' if login shall work.
// TODO: Enable direct FTP

$set_config = array (

'path_to_release' => '../../../release/Total_HTM_Easy/',      // DEFAULT: ../../release/
'path_to_test' => '../../../test/Total_HTM_Easy/',            // DEFAULT: ../../test/

'xr_release' => 'https://exa.run/Total.HTM-Easy/_Total/',  // keep empty to use relative path 
'xr_testing' => '',                 // keep empty to use relative path '../' 

'minify_any' => 1, 

'minify_htm' => 1,                  // minify htm does control minify inline-CSS and inline-JS as well
'minify_css' => 1,
'minify_js' => 1,
'minify_js_console' => 1,

'image_cache_use' => 0,             // internal image cache can make MAKE 'much' faster, depends on images

'image_resize_any' => 1,            // do any resizing on/off if image much bigger than e.g. 800 * 800

'image_resize_big' => 1,            // resize if image much bigger than e.g. 800 * 800
'image_resize_big_w' => 800,           
'image_resize_big_h' => 800,           

'image_resize_small' => 1,          // resize to e.g. 400 * 400 if <img> has class '.resize-small' 
'image_resize_small_w' => 400,           
'image_resize_small_h' => 400,           

'image_link_to_big' => 1,
'image_link_to_big_slider' => 1,

'image_lazy_slider' => 1,           // Lazy images inside sliders is realized with <template> to ensure first image loaded afap.

'section_lazy_any' => 1,            // you can control single sections by class '.no-lazy'
'section_lazy_readmore_mode' => 1,  // if lazy, and readmore exists, the html until first readmore is kept (teaser)
'section_lazy_min_char' => 256,     // no lazy, if the html-text for the default-language has less than this number of chars, set 0 to disable  

'lazy_main' => 1,                   // make most content HTML code load dynamically after init       
'lazy_footer' => 1,                 // make the footer load dynamically after init

'make_nojs_htm_file' => 1,          // Create No-JS-Version (File s.htm), if 0 also remove the noscript-warning in Total.HTM  

'login_at_start' => 0,              // makes login form appear immediatelly, sometimes useful for apps 

//  --------------------------------------------------------------------------
//  ---------- More like EXPERT-ONLY settings from here on: ------------------

'enable_https_redirect' => 0,

'minify_css_add_timestamp' => 1,
'minify_js_add_timestamp' => 1,

'lazy_menu_main' => 0,              // (template-fake-lazy) make the main-menu load dynamically after init (useful if hidden main menu is complex with images a.s.o.)
'lazy_menu_hamburger' => 0,         // (template-fake-lazy) make the menu-hamburger load dynamically after init
'lazy_lang_list_0' => 0,            // (template-fake-lazy) load language-list within topline lazy, so that other flags are not needed for first impression

'lazy_main_by_image_only' => 1,     //  only if (lazy_main == 1) keep HTML-code, only make images load lazy (Q: for SEO-reasons ?!)       
'lazy_footer_by_image_only' => 1,   //  only if (lazy_footer == 1) keep HTML-code, only make images load lazy       

'lazy_css_byclass' => 0,            // This loads all CSS-files that come within head, after .lazy-css-begin; Can be useless if not much lazy CSS   
'lazy_js_by_async' => 1,            // This puts the lazy JS into static HTML with attribute async, instead of dynamic loading   

'minify_js_lazy' => 1,              // only if  minify_any and minify_js are 1; this refers to the 'very lazy' inside /js/_lazy
'minify_css_lazy' => 1,             // only if  minify_any and minify_css are 1; this refers to the 'very lazy' inside /css_lazy

'cc_version_prev_keep' => 0,        // The number of previous release-MAKE's to keep    

'use_service_worker' => 0,          // Better only activate this if you really know what it means!

'empty_release_before_copy' => 1,   // If activated, the complete release-dir is deleted recursively before copy of new release.

'api_key_make' => 'kjdsa8798798d8d8d888d2',  // ?ak=...   API-Key for the MAKE-script
'api_key_make_allow' => 0,                // Only activate this if you know what you do!

);


