// ---------------------------------------------------------------------------
// --------------------          UX Settings       ------------------------ */
// ---------------------------------------------------------------------------
//
// UX means: User Experience. Another term for 'User Interface'.
//
// IMPORTANT: The default-language set at <html lang="xx"> shall be 
// the same language as defined a few lines below as 
// DEFAULT_LANGUAGE. 
// Changing the default langauge at <html lang="xx"> for testing is ok, 
// but your website needs a clear language-concept, and translations
// must be correctly set at the file 'lang.js'.
//
// Note: UX Settings are stored 'per Browser'. Locally. Easy Reset.
// 

// ---------------------------------------------------------------------------
var _SET_UX=
[

["chapter-look-feel", "chapter",    "Look & Feel",              1],             // Chapter have 0/1 for collapsed/expanded
["com-fontsize",    "com-radio",    "Font Size",                "1", 0],      // com-zero == 0 
["com-theme",       "com-radio",    "Theme",                   "light", 0],      // Default theme is overwritten by the first Theme-CSS_-File linked, following ones are removed by MAKE   
["com-effect",      "com",          "Effect",                  "", "None"],    // the text at the end is/enables the com-zero-option  

["chapter-general", "chapter",      "General Settings",         1],             // TODO: Chapter have 0/1 for collapsed/expanded
["chk-fullscreen",  "chk",          "Fullscreen Mode",          0],
["chk-menu-out",    "chk",          "Hide Main Menu",           0],
["com-lang",        "com-radio",    "Language",                "en", 0],          // <-- DEFAULT_LANGUAGE. Overwritten by <html lang='xx'> 
["com-tz",          "com",          "Timezone",                 0, 0],            // com: can-zero
["but-reset",       "but",          "Reset Client-Settings*",  "gray"],       // button has no default value, but classes instead
["lab-reset",       "lab",          "* Non-critical browser-settings like table-sorting or visual preferences."],      

/*
["chapter-test",    "chapter",      "Some Test-Values",         1],       
["num-test",        "num-range",    "Range Number",             1, 0.3, 3, 0.1],  // num: def, min, max, step 
["txt-test",        "txt",          "Test String",              "pla", 25],       // txt: def, max-char
["num-test",        "num",          "Test Number",              0, -2000000000, 2000000000, 0.01],   
["txt-test2",       "txt-area",     "Test Textarea",            "ploplo", 400],       // txt: max-char
["time-test",       "time",         "Test Time",                "20:13:15"],      
["date-test",       "date",         "Test Date",                "2017-05-14"],      
["datetime-test",   "datetime",     "Test Datetime",            "2080-05-14 14:30:01"],      
["col-test",        "col",          "Test Color",               "#ff00ff"], 
["dyn-test",        "dyn",          "Dynamic Element",          0],                 // dyn: 0/1 if label is appended 
*/

];



// ---------------------------------------------------------------------------
var _SET_UX_OPT =
{
"com-lang": // Language Register  
[ 
["en",   "English"],  // Image: 'img/_lang/lang_en.png'
["de",   "German"],
["es",   "Spanish"],
],
"com-theme": // Theme Register 
[ 
["light",   "Light",            0],     // the last 0/1 means 'is-dark'
["dark",    "Dark",             1],
["alu",     "Aluminium",        1],
],
"com-fontsize": 
[ 
["0.82",    "Small"],
["1",       "Medium"],
["1.33",    "Big"],
],
"com-effect": // Some nice effects (== class on <html>)
[ 
["invert",   "Invert Screen"],     // the last 0/1 means 'is-dark'
["sepia",    "Sepia Effect"],
],
"com-tz":   // Timezones
[
["720",   "(-12:00) Marshall Islands"],
["660",   "(-11:00) Samoa"],
["600",   "(-10:00) Hawaii"],
["540",   "(-09:00) Alaska"],
["480",   "(-08:00) Pacific"],
["420",   "(-07:00) Mountain"],
["360",   "(-06:00) Central"],
["300",   "(-05:00) Eastern"],
["240",   "(-04:00) Atlantic"],
["210",   "(-03:30) Newfoundland"],
["180",   "(-03:00) Brazil"],
["120",   "(-02:00) Mid-Atlantic"],
["60",    "(-01:00) Azores"],
["0",     "( 00:00) London (GMT)"],
["-60",   "(+01:00) Paris"],
["-120",  "(+02:00) Central Africa"],
["-180",  "(+03:00) Moscow"],
["-210",  "(+03:30) Tehran"],
["-240",  "(+04:00) Abu Dhabi"],
["-270",  "(+04:30) Kabul"],
["-300",  "(+05:00) Islamabad"],
["-330",  "(+05:30) Bombay"],
["-345",  "(+05:45) Kathmandu"],
["-360",  "(+06:00) Bangladesch"],
["-420",  "(+07:00) Bangkok"],
["-480",  "(+08:00) Singapore"],
["-540",  "(+09:00) Tokyo"],
["-570",  "(+09:30) Darwin"],
["-600",  "(+10:00) Eastern Australia"],
["-660",  "(+11:00) Solomon Islands"],
["-720",  "(+12:00) New Zealand"],
]};

// -----------------------------------------------------------------------------
function SET_def(id)   // return the default value of a setting or null
{
    for(s of _SET_UX)
    {
        if(s[0] == id)
        {
            return s[3]; 
        }
    } 
    return null;
}

// -----------------------------------------------------------------------------
function SET(id, v)   // reads or writes, depending if second param is given
{
    if(typeof v == UN)
    {
        v = _LS.getItem(id);
        return v == null ? SET_def(id) : v;
    }
    else
    {
        _LS.setItem(id, v);
    }
}

// -----------------------------------------------------------------------------
function SETh(id)  
{
    return  (_LS.getItem(id) != null);
}


// -----------------------------------------------------------------------------
function SET_or(id, or)  // reads or returns or-value
{
    var v = _LS.getItem(id);
    return v == null ? or : v;
}


// -----------------------------------------------------------------------------
function SET_make()
{
    var ds = _m.Q("#setting-ux"),
        df, dsb, v, id, d;
    if(!ds) 
    {
        var dsh=fCE("header").AP(fCE("h3").AP(fCE("span").APs(LLS("ux-setting")))),
            dsb = fCE("div").A({"class":"section-body"});
        _m.CE("section").A({"id":"setting-ux"}).AP(dsh).AP(dsb);
    }
    else 
    {
        dsb = ds.Q(":scope >.section-body");
        if(dsb.childNodes.length > 0)
        {
            return; // done already
        }
    }

    df = FORM_make("form-setting", _SET_UX, _SET_UX_OPT),
    dsb.AP(df);

    // check if lang-choice is visible anyway
    d = fQ("#header .lang-choice");
    if(d && d.offsetWidth > 0)
    {
        d = fE("form-setting-com-lang");
        if(d) d.REM();          
    }

    if(!_b.Ch("menu-left") && !_b.Ch("menu-top"))
    {
        d = fE("form-setting-chk-menu-out"); // makes only sense if menu-left or menu-top (at landscape at least)
        if(d) d.REM();          
    }

    if(d = fE("form-setting-com-fontsize"))
    {
       for(d of d.QA(".div-option"))
       {
           var di = d.Q("input"),
               vi = fF(di.value),
               ds = d.Q("label > span");
            ds.CSS("font-size", vi + "em");          
       }  
    }    
    
    for(set of _SET_UX)
    {
        id = set[0];
        if(SETh(id) && fE(id))
        {
            INP_val_DB(id, SET(id));
        }
    }
}



