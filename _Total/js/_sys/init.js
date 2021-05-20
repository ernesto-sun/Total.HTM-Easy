
// ----------------------------------------------------
function LANG(l)  // Overwritten at 'total.js'
{
  // this function is a redirect for releases (after MAKE).  
  if(_doc.lang != l) location.href = location.href.replace("/" + _doc.lang + "/", "/" + l + "/");
}

// ----------------------------------------------------
function _LAZY(src, f, d)
{
  if(typeof d == UN || !d)
  {
    d = _b.CE("script").A({type: "text/javascript"});
  }
  
  // d.async: true;
  if(typeof f != UN) d.Ea("load", f);

  d.A({src: src});  
  return d;
}

// ----------------------------------------------------
function _LAZYa(src, d)   // the async version, here with Promise
{
  return new Promise((ok, no) => 
  {
    _LAZY(src, () => 
    {
      setTimeout(() => 
      { 
        IDLE(ok); 
      }, 100);   
    }, d);
  });
}

// ----------------------------------------------------
function _SLEEP(ms) 
{
  return new Promise((ok, no) => 
  {
    setTimeout(() => ok(), ms);
  });
}

// ----------------------------------------------------
async function SLEEP(ms) 
{
  await _SLEEP(ms);
}

// ----------------------------------------------------
async function LAZY(src)
{
  var id = "script-" + src.replace(/\W/,"-"),
      d = fE(id),
      sk = 0, 
      cc = 0;
  if(d)
  {      
    if(d.Ch("loaded")) 
    {
      sk = 1;
    }

    while(!d.Ch("loaded") && cc < 50)  // we wait 5 seconds! 
    {      
      console.warn("Waiting 100ms for JS-file to get loaded.");
      await SLEEP(100);
      cc++;
    }
    sk = 1;
  }
  
  if(!sk) 
  {
    d = _b.CE("script").A({id: id, type: "text/javascript", class: "loading"});
    await _LAZYa("../js/" + src + ".js", d);
    d.Cr("loading").Ca("loaded");  
  }    
}

// -----------------------------------------
function MH_toggle()  // MH like Menu Hamburger
{
  if(fE("menu-hamburger").Ch("active")) MH_close();
  else MH_open();
}

// -----------------------------------------
function MH_open()
{
  BGF(fE("topline"), MH_close);

  var dmm = fE("menu-main"),
      dmh = fE("menu-hamburger");
    
  if(dmh.Ch("unloaded")) 
  {
    dmh.Cr("unloaded");
    dmh.APs(dmh.Q("template").REM().innerHTML);
  }

  if(dmm.Ch("unloaded")) 
  {
    dmm.Cr("unloaded");
    dmm.APs(dmm.Q("template").REM().innerHTML);
  }

  dmh.Ca("active");

  var dms = fE("menu-sys"),  // must be down here, becuase unknown if unloaded
      dmd = fE("menu-dyn");
  
  if(_b.Ch("menu-onclick") || _b.Ch("portrait") || _b.Ch("menu-out"))
  {
    dmm.Ca("active");
    if(dms) dms.CSS({top: (dmm.offsetTop + dmm.H()) + "px"});
    if(dmd) dmd.CSS({right: (_ws - dmm.offsetLeft) + "px"});
  }
  else
  {
    // main-menu isn't part of hamburger menu
    if(dmd) 
    {
      dmd.CSS({right: "0px"});
      if(dms) dms.CSS({top: (dmd.offsetTop + dmd.H()) + "px"});
    }
  }  
  
  X_MH(1);
}


// -----------------------------------------
function MH_close()
{
  var dmm = fE("menu-main"),
      dmh = fE("menu-hamburger");

  dmh.Cr("active");
  dmm.Cr("active");

  BGF_close();

  X_MH(0);
}


// ---------------------------------------------------------
function CSSVar(d,n,v)  // keep v == UN if you want a read
{
  if(typeof d != "object") 
  {
    d = _doc;
  }

  if(typeof n == UN) 
  {
    console.error("Use of function CSSVar without n");
    return "";
  }

  if(!n.startsWith("--")) n = "--" + n; 

  if(typeof v == UN)
  {
    return getComputedStyle(d).getPropertyValue(n); 
  }

  d.style.setProperty(n, v);
}

// --------------------------------------------------------------------
function RES() // The Asynchronous Resize Event Handler
{
/*  var bz =_win.devicePixelRatio;  
  _ws = screen.availWidth * bz;
  _hs = screen.availHeight * bz; */

  _ws = _100v.W();  
  _hs = _100v.H();  

  _rs = _hs / _ws;
  _wm = _m.W();

  var tw = Math.abs(_wsp - _ws) / _ws,   // difference-factor to previous w/h-values
      th  = Math.abs(_hsp - _hs) / _hs,
      skip = 0;

  // avoid Resize in minor resolution-changes (e.g. browser bar appears and disappers)
  if(!_rese)
  {
    if(th >= 0.2 || tw >= 0.2 || (th >= 0.1 && tw >= 0.1))
    {
      // we have mayor changes 
    }
    else
    {
      console.log("RESIZE: Avoiding because of minor changed!");
      skip=1;
      if(_skeyb)
      {
        // thats here because we skipped resize at ON, now we have no change
        _skeyb = 0;  
      }  
    }

    if(!skip)
    {
      // further check: maybe soft keyboard did pop up

      if(tw < 0.1 &&  // width must be nearly unchanged (maybe some scrollbar-change) 
        th > 0.24 &&  // height change must be significant 
        _b.Ch("screen-small"))  // looks like mobile-view (was set by a previous RES())
      {
        var dfoc = _doc.activeElement,
            isft = dfoc && (dfoc.TAG() == "input" || dfoc.TAG() == "textarea");

        if(isft && _hsp > _hs)
        {
          // very likely the soft keyboard did pop up
          _skeyb = 1;
          console.log("RESIZE: Avoiding because of soft keyboard did pop up (seems)!");
          skip = 1;  
        }
      }
    }  
  }  // ensure

  if(skip) 
  {
    _res = 0;
    return;
  }

  _hsp = _hs;
  _wsp = _ws;

  _rese = 0;
  _res = 0;

  _b.Ct("landscape", (_rs <= 1) );
  _b.Ct("portrait", (_rs > 1) );

  if(_1em != _1emo * _zoom) 
  {
    // NOTE: This coming two lines can be very performance-expensive because browser does repants. TODO: Improve!
    _b.CSS("font-size", (_1emo * _zoom) + "px");
  }

  _1em = fR(_1emd.W());  // rounded px-width of 1em
  
  var sma = (_win.matchMedia('(max-width: 690px)').matches),
      big = (_win.matchMedia('(min-width: 1300px)').matches),
      fw = _ws / _1em,
      min = MIN_EM / _zoom,   // correct min/max relative to UX-zoom
      max = MAX_EM / _zoom,
      mob = sma;

  _b.Ct("screen-small", sma);
  _b.Ct("screen-big", big);
  _b.Ct("screen-medium", (!big && !sma));

  // ------------------------------------------------------------------
  
  if(fw < min)
  {
    v = _1em;
    _1em = fR(_ws / min);
    _b.CSS("font-size", _1em + "px");
    console.log("RESIZE UP: 1em from " + v + "px to: " + _1em + "px. (fw < min) = (" + fR(fw) + " < " + fR(min) + ") FW-new: " + fR(_ws / _1em) + "px");
    fw = _ws / _1em;
  }

  if(fw > max)
  {
    v = _1em;
    _1em = fR(_ws / max);
    _b.CSS("font-size", _1em + "px");
    console.log("RESIZE DOWN: 1em from " + v + "px to: " + _1em + "px. (fw > max) = (" + fR(fw) + " > " + fR(max) + ") FW-new: " + fR(_ws / _1em) + "px");
    fw = _ws / _1em;
  }

  if(_b.Ch("screen-big"))
  {
    if(fw < 60) _b.Cr("screen-big");
    // the zoom level is just so high, CSS cant have screen-big
  }
  else
  {
    if(_b.Ch("screen-small"))
    {
      if(fw > 40) _b.Cr("screen-small");
    }
    else if(fw > 70) _b.Ca("screen-big");
  }

  if(fw < 29)
  {
    _b.Ca("screen-small");
    if(fw <= min)
    {
      _b.Cr("landscape").Ca("portrait");  // For extreme cases... (also very small landscape)
      mob = 1;
    }
  }

  _b.Ct("mobile", mob);  // mob shall be .small-screen || .portrait
  _b.Ct("desktop", !mob);
  
  FRAME(X_RES);
}


// -----------------------------------------------------------------------------
function TH(th)
{
  for(var t of _SET_UX_OPT["com-theme"]) 
  {
    if (t[0] == th) 
    {
      _TH = t;

      if(_th != th)
      {
        _b.Cr("theme-" + _th);
        var d = fE("link-theme-"+ _th);
        if(d) d.REM();
      
        _th = th;

        _b.Ca("theme-" + th);

        _h.CE("link").A({id: "link-theme-" + th,
                          rel: "stylesheet",
                          type: "text/css",
                          href: "../css/theme_" + th + ".css"});
      }
      break;
    }
  }

  if(!_TH.length)
  {
      console.error("Could not find theme in Theme Registry. See setting.js. Theme: " + th);
  }
}


// -----------------------------------------------------------------------------
function EFFECT(v)
{
  if(_doc.Ah("data-effect")) _doc.Cr(_doc.A("data-effect"));
  getComputedStyle(_doc);  // recalculate CSS (very costly!!!)
  if(v) setTimeout( () => { _doc.Ca(v).A("data-effect", v); }, 100);  // give some time to new effect  
}



// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------
function I00()
{
  _b =      _d.body;  
  _m =      fQ("main");   
  _1emd =   fE("div-1em");
  _1emo =   fR(_1emd.W()); // emo = em-orig
  _100v =   fE("div-100v");

  FRAME(I00F);

  RES();

  X_I01();
 
  if(typeof I01 != UN) IDLE(I01); 
}


// -----------------------------------------------------------------------------
function I00F()
{
  // --------------------

  var bc = _b.classList, v;
  bc.remove("no-js");
  bc.add("ok-js");

  TH(SET_or("com-theme", _th));  // now _TH is set for sure, used at several places 

  _zoom = fF(SET_or("com-fontsize", _zoom));

  v = SET_or("com-effect", "");
  if(v) EFFECT(v)

  v = SET_or("chk-fullscreen", 0);
  if(v) BGF(0, () => { BGF_close(); fullscreen_on(); }).Ca("trans");

  v = SET_or("chk-menu-out", 0);
  if(v) _b.Ca("menu-out");

  _tz = SET_or("com-tz", _tz);

}

// -------------------------------------------------------
async function toggle(e, sk)   // sk = skip most, just toggle on (used for title-click) 
{
  var dp = sk ? this : this.closest(".toggle");

  if(dp.TAG() == "section")
  {
    if(dp.Ch("unloaded"))
    {
      await LAZY_section(dp.id);
    }
  }

  var ac = dp.children,
      n = ac.length,
      h = 0;

  if(n > 1)
  {
    h = sk ? 1 : ac[1].Ch("hide");
    ac[1].Ct("hide", !h);
    if(n > 2) ac[2].Ct("hide", !h);

    if(!sk) dp.Ct("toggle-in", h).Ct("toggle-out", !h);
  }

  X_toggle_after(dp, h ? 1 : 0);
}


// -------------------------------------------------------
async function readmore(e)
{
  var d = this,
      ds = d.closest("section");

  if(ds.Ch("unloaded"))
  {
    await LAZY_section(ds.id);
    d = ds.Q(".readmore");  // was overwritten by lazy load. Thus the old d is invalid
  }

  if(d.Ch("readless"))
  {
    d.Cr("readless");
    d.EMP().CE("span").APs(LLS("readmore"));
    var dx = d;
    while(dx = dx.nextElementSibling)
    {
      dx.Ca("hide-readmore").Ca("hide");
      if(dx.Ch("readmore"))
      {
        if(dx.Ch("readless"))
        {
          dx.Cr("readless");
          dx.EMP().CE("span").APs(LLS("readmore"));
        }
        else
        {
          break;
        }
      } 
    }
  }
  else
  {
    var dx = d;
    while(dx = dx.nextElementSibling)
    {
      if(dx.Ch("hide-readmore"))
      {
        dx.Cr("hide").Cr("hide-readmore");
        if(dx.Ch("readmore")) break;
      }
    }
    d.Ca("readless");
    d.EMP().CE("span").APs(LLS("readless"));
  }
}

// ----------------------------------- take care to give z-index > 8900 to active element
function BGF(d, f)   //  uses / creates .bg-free as first child of this element, f is the close-function 
{
  var dp;
  if(typeof d == UN || !d) dp = _b;
  else dp = d.parentNode;
  var bgf = dp.Q(":scope > .bg-free");
  if(!bgf) bgf = dp.CE0("div").A({class: "bg-free"});
  bgf.Cr("hide").Ea("click", f);
  return bgf;
}

// -----------------------------------
function BGF_close()
{
  for (var d of fQA(".bg-free:not(.hide)")) CLONE(d.Ca("hide").Cr("trans"));  // CLONE is a way to remove all eventhandles  
}

// -----------------------------------
function CLONE(d)   // Note: Looses all dynamic eventhandlers!
{
  var dp = d.parentNode,
      dc = d.cloneNode(true);
  dp.replaceChild(dc, d);
  return dc;
}

// -----------------------------------
function lang_click(d)
{
  d.Ct("active");
  if(d.Ch("active")) 
  {
    BGF(d, lang_close);

    var dll = d.Q(".lang-list");
    if(dll.Ch("unloaded")) 
    {
      dll.Cr("unloaded");
      dll.APs(dll.Q("template").REM().innerHTML);
    }
  }
  else 
  {
    BGF_close();
  }    
}

// -----------------------------------
function lang_close()
{
  for (var d of fQA(".lang-choice.active")) d.Cr("active");
  BGF_close();
}

// ------------------------------------------------------
function lang_fclick(e)  // flag click
{ 
  var d = this,  
      l = d.A("data-lang"); 
  if(l.length == 2 && l != _lang) LANG(l);
}

// ------------------------------------------------------
function titclick(e)
{
  var ds = this.closest("section");
      drm = ds.Q(".readmore");
  if(drm)
  {
    drm.EE("click");
  } 
  else
  {
    toggle.call(ds, e, 1);  // sk, skip other toggle-work
  }
}

// ------------------------------------------------------
function aclick(e)
{
  e.preventDefault();

  var da = this,
      href = da.A("href"),
      dp = da.parentNode;
 
  if(da.Ch('no-js')) return;

  if(dp.Ch("li-line"))
  {
    dp = dp.parentNode;
    if(dp.Ch("dropdown-js") && dp.Q(":scope > .li-sub")) return; // must be done at dropdown_click()
  }

  if(da.Ch("a-img-bigger"))
  {
    console.log("Lightbox Img: " + href);

    var di=da.Q("img");
    if(di) 
    {
      lightbox("<img src='" + href + "' alt='" + (di.A("alt")) + " big' class='img-lightbox-fullsize'/>");
      return;
    }
  }

  lightbox_clean();

  CLICK(href);
}

// ------------------------------------------------------
function CLICK(href)
{
  var is_anchor = (href.charAt(0)=='#' && href.length > 1),
      r;

  if(fE("menu-hamburger").Ch("active")) MH_close();

  switch(href)  // href that exist as sections
  {
  case "#menu-hamburger":
      MH_toggle();
      return;
  case "but-reset":
    _LS.clear();
  case "#reload":
    msg(LLS("reload"));
    setTimeout(() => { location.reload(); }, 1000);
    return;
  case "#login":
    if(_uok)
    {
      msg(LLS("goodbye"));
      setTimeout(() => { location.reload(); }, 1000);
    }
    else (function $() 
    { 
      if(typeof $.cc != UN) {$.cc = 0; dl8(); }
      else _LAZY(_xr_ut + _lgiu, () => { dl8(); });     
      $.cc++;
    })();
    $ = 0;
    return; 
  }

  if(is_anchor)
  {
    GOTO(href.substr(1), 1);   // calls X_click in itself
  }
  else
  {
    r = X_click(href);
    if(r === false || r === 0)  return;

    console.log("OPEN External Link: " + href);
    window.open(href);
  }
}


// --------------------------------------------------------
// function I01()  // GENERATED AT MAKE (if lazy JS loaded dynamically, not static async)
// {
  // Note: At total.js I01 is 'overwritten' to test lazy loading
  // Will be hardcode-overwritten during MAKE-process
  
  // Leads to I02();  // defined in file 'system.js'
// }


// Here in fact JS starts
if (_d.readyState != "loading") I00(); else _d.addEventListener("DOMContentLoaded", I00);

