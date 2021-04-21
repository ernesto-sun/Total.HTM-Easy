
// -------------------------------------------------
function X_I01()  // init only after most essential work done in init.js (kepp it small and fast) 
{

}

// -------------------------------------------------
function X_I02()  // init after lazy scripts are loaded, maybe half a second after X_I01()
{
}

// -------------------------------------------------
function X_DYN(d)   // Ater each DYN-call. That is dynamisation of DOM
{

}


// ------------------------------------
function X_MH(b)  // 1/0 if Hamburger Menu opened/closed
{

  if(b)
  {
    // Check our extra slider-buttons in menu
    var dms = fE("menu-sys"), 
        dsl = fE("slider-main"),
        ru = dsl.Ch("running");    
    dms.Q(".slider-play").Ct("hide", ru);
    dms.Q(".slider-pause").Ct("hide", !ru);
  }
}


// ------------------------------------
function X_KEY(e, d, code, char)
{
  if(code == 27)  // ESC
  {

  }
}


// ------------------------------------ 
function X_SCR_ongoing()  // ongoing scrolling
{
}

// ------------------------------------ 
function X_SCR_after() // after scrolling
{

}

// ------------------------------------
function X_RES()   // after resize 
{

}

// ------------------------------------------------------
function X_click(id)   // all href-catcher, and button-id's
{
  switch(id)
  {
  case "#donate":
      lightbox(fE("lightbox-donate").innerHTML);
      break;
  case "#test-lightbox":
      lightbox(["<lang lang='en'>This is just a test message.</lang>",
        "<lang lang='de'>Das ist eine Test-Nachricht.</lang>",
        "<lang lang='es'>Esto es sólo un mensaje de prueba.</lang>"].join(""));
      break;
    
  case "#test-popup":
      popup(["<lang lang='en'>This is just a test message.</lang>",
          "<lang lang='de'>Das ist eine Test-Nachricht.</lang>",
          "<lang lang='es'>Esto es sólo un mensaje de prueba.</lang>"].join(""));
      break;

  case "#test-msg":
      msg(["<lang lang='en'>This is just a test message.</lang>",
          "<lang lang='de'>Das ist eine Test-Nachricht.</lang>",
          "<lang lang='es'>Esto es sólo un mensaje de prueba.</lang>"].join(""));
      break;
  
  case "#test-warn":
    warn(["<lang lang='en'>This is a test warning. Just testing.</lang>",
          "<lang lang='de'>Das ist eine Test-Warnung.</lang>",
          "<lang lang='es'>Esta es una advertencia de prueba. Sólo una prueba.</lang>"].join(""));
      break;
  
  case "#test-err":
    err(["<lang lang='en'>This is the test error message. It's all good!</lang>",
         "<lang lang='de'>Das ist die Test-Fehlermeldung. Ist aber alles ok.</lang>",
         "<lang lang='es'>Este es el mensaje de error de la prueba.</lang>"].join(""));
    break;

  case "#contact": 
    XX_make_form_contact();
    // no break here, keep going for email-making
  case "#impressum":
    {
      // just a funny way to write an email address, not to make it all too easy for home-made spiders, ...
      var e;
      for(e of fQA(".apl-476:not(.done)")) e.A("href",  atob("bWFpbHRvOg==") + e.A("href").substr(1) +  atob("QGV4YQ==") + "l" + atob("b3QuY29t")).Ca("done");
      for(e of fQA(".epl-476:not(.done)")) e.EMP().APs("exalot.").Ca("done");  
    }    
    return 1;
  case "#setting-ux":
    if(fE("setting-ux")) return 1; 
    LAZY("_lazy/form").then(() => 
    {
      SET_make();
      GOTO("setting-ux", 1);
    });
    return 0;
  default:
    return 1;
  }
  return 0;  // 0 means we have done the job
}


// ----------------------------------------------
function X_GOTO_after(id)
{
  // 'id' can be of any kind of anchor, including section
}


// -------------------------------------------
function X_input(d, v)
{
  console.log("UX-Input: " + v, d);

  var id = d.id,
      y = d.A("data-y");

  if(id.startsWith("form-setting-"))
  {
    // this is an UX-setting...
    _LS.setItem(id, v); // save in Local-Storage      
    d.Cr("uxc");  // remove dirty-flag after saving in Local-Storage
    
    switch(id.substr(13))
    {
    case "com-fontsize":        
    {
      _zoom = fF(v);  // parseFloat
      _res = _rese = 1;   // ensure
      RES();
      break;
    }
    case "com-theme":        
    {
      TH(v);
      break;
    }
    case "com-effect":        
    {
      EFFECT(v);
      break;
    }
    case "chk-fullscreen":        
    {
      if(v) fullscreen_on();
      else fullscreen_off();
      break;
    }
    case "chk-menu-out":        
    {
      _b.Ct("menu-out", v);
      break;
    }
    case "com-tz":        
    {
      _tz = v;
      break;
    }
    default:
      console.log("Unhandled UX-setting: " + id);
    }
  }
}




// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------

// From here on your custom functions. 
// Recommendation: User XX_ as prefix, that makes text-finding easy etc. 

// ---------------------------------------------------------------------------
function XX_SM(b)   // slider-main control over Hamburger Menu
{
  if(b) 
  {
    SLIDER_play(fE('slider-main'));
  }
  else
  {
    SLIDER_stop(fE('slider-main'));
  }
  fQ("#menu-hamburger .slider-play").Ct("hide", b);
  fQ("#menu-hamburger .slider-pause").Ct("hide", !b);
}



// ---------------------------------------------------------------------------
function XX_make_form_contact()
{
  LAZY("_lazy/form").then(() =>
  {
    var dcf = fE("contact-form");
    if(!dcf.Ch("done")) dcf.Ca("done").AP(FORM_make("form-cf",
    [
    ["txt-subject",     "txt",          "Subject",              "",  255],
    ["com-topic",       "com",          "Topic",                "",    "Other"],
    ["txt-contact",     "txt",          "Your Contact Info",    "required",  255],
    ["txt-message",     "txt-area",     "Your message",         ""],    
    ["but-send",        "but",          "Send",                 "green", "https://exa.run/ConfiMailPHP"]
    ],
    {
    "com-topic":  
    [ 
    ["request",    "Request"],
    ["question",   "Question"],
    ["feedback",   "Feedback"],
    ["bug",        "Bug-Report"],
    ["legal",      "Legal matter"],
    ]}));
  });
}
