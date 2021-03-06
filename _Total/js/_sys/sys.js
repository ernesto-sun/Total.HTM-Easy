
/* Here are all things that are not needed for init, but that are basic 
 The most essential stuff for the first impression is in file 'init.js' */


var _f_close_popup = 0, 
  _tim_popup = 0,
  _msg_cc = 0,
  _f_close_lightbox = 0;


// --------------------------------------------------------
function I02()
{
  FRAME(I02F1);
}


// -------------------------------
async function I02F1_l()   // lazy html loading
{
  if(_m.Ch("unloaded"))
  {
    _m.Cr("unloaded");  
    var res = await fetch("lazy_main.htm");
    if(!res.ok)
    {
      console.err("Was not able to lazy load main!");
    }
    _m.APs(await res.text());    
  }

  var df = fQ("body > footer");
  if(df.Ch("unloaded"))
  {
    df.Cr("unloaded");  
    var res = await fetch("lazy_footer.htm");
    if(!res.ok)
    {
      console.err("Was not able to lazy load footer!");
    }
    df.APs(await res.text());    
  }
}



// -------------------------------
function I02F1()
{
  I02F1_l().then(() =>
  { 
    FRAME(I02F2);  
  });
}

// ------------------------------
function I02F2()
{
  if(!SETh("com-tz"))   // first time find out timezone
  {
    _tz = tz(); 
    SET("com-tz", _tz);  
  }

  if(!_OBS)   // not to colide with total.js, TODO: improve
  {
    _OBS = new IntersectionObserver(OBS_f, 
      {rootMargin: "0px 0px 0px 0px",
       threshold: 0.0001 });  
  }

  DYN(_b);

  // --------------------

  IDLE(I02F3);
}

// ------------------------------
function I02F3()
{
  var d;

  _tim = setInterval(SEC, 1000);

  _win.addEventListener("scroll", () => { _scr0 = _scr = 1; }, { passive: true });  
  _win.addEventListener("resize", () => { _res = 1; }, { passive: true }); 
  _win.addEventListener("keydown", KEY, { passive: true });

  IDLE(X_I02);

  _href = "" + _d.location.hash.substr(1);
  var ihi = _href.length,  // ihi: is hash init
      sc = 0;  // do js-scrolling
  if(ihi)
  {
    ds = fE(_href);
    if(ds)
    {
      sc = (!_scr0 && _win.scrollY < 10);   // Never scrolled so far and/or being at the top (still) 
      // This strategy works together with: history.scrollRestoration = "manual";  
      setTimeout(() => 
      {
        GOTO(_href, 1, !sc);
      }, 800);  // TODO: ?!
    }

    if(_href == "login-failed") 
    {
       err(LLS("login-err"), 3); 
       history.pushState(null, null, '#');
    } 
  }

  _win.addEventListener("hashchange", (e) =>
  {
    e.preventDefault();
    _hrefp = _href; 
    _href = "" + _d.location.hash.substr(1);
     if(_href.length)
     {
      if(fE(_href)) GOTO(_href, 1); 
     }
  });


  // SW_init(); // No need to register/install it before. (at release another serviceworker will be at the same location, generated by MAKE)


  // start Animation-Ticker for Parallax 
  if(_PARALLAX_on) 
  {
    // TODO: Disable if not used in fact, or at this scroll-pos, etc.
    FRAME(_FRAME);
  }
}


// --------------------------------------------------            
async function LAZY_section(ids)
{
  var ds = fE(ids), cc = 0; 
  if(!ds.Ch("unloaded")) return ds;
  if(ds.Ch("loading"))
  {
    while(!ds.Ch("loaded") && cc < 50)  // we wait 5 seconds! 
    {      
      console.warn("Waiting 100ms for section to get loaded.");
      await SEEP(100);
      cc++;
    }
    return ds;
  }

  var res = await fetch("section_" + ids + ".htm");
  if(typeof res.ok == UN || !res.ok)
  {
    throw new Error("Invalid result object in lazy section loading: " + ids);
  }

  ds.Ca("loading");
  var dsb = ds.Q(":scope > .section-body");
  if(!dsb)
  {
    dsb = ds.CE("div").Ca("section-body");
    console.warn("Fallback: Had to create section-body at lazy Loading of section: " + ids);
  }
  var ht = await res.text();

  if(ds.Ch("unloaded-rm"))
  {
    dsb.EMP(); // This is the readmore-mode (until readmore was preloaded)
  }

  DYN(dsb.APs(ht)); 

  await SLEEP(100);
  ds.Cr("loading").Ca("loaded").Cr("unloaded");

  return ds;
}

// --------------------------------------------------            
function OBS_in(d, first, e) 
{
  var id = d.id;
  switch(d.TAG())
  {
  case "footer":
  case "section":
  {
    if(first && d.Ch("unloaded") && !d.Ch("unloaded-rm") && !d.Ch("toggle-out")) 
    {
      LAZY_section(id);
    }
    else
    {
      // section in, in case of LAZY_section above current section is set otherwise
      if(!_ssk) _ss = id;
    }
    break;
  }
  default:
  {
    if(id == "header")
    {
      if(!_ssk) _ss = "";
      _b.Ca("header-in").Cr("header-out");
    }
    break;
  }
  }

  X_inout(d, 1, first);
}

// --------------------------------------------------            
function OBS_out(d) 
{ 
  if(d.id == "header")
  {
    _b.Cr("header-in").Ca("header-out");
  }

  X_inout(d, 0, 0);
}


// --------------------------------------------------            
function OBS_f(ae, obs) 
{ 
  for (e of ae)
  {
    var d = e.target,
       cl = d.classList; 
    if(e.intersectionRatio > 0)
    {
      if(!cl.contains("in"))
      {
        // element just moved into visibility

        cl.add("in");
        cl.remove("out");

        if(cl.contains("parallax"))
        {
          if(typeof d._dy == UN) d._dy = 0;
          
          //if(typeof d._mtop == UN) d._mtop = fIS(getComputedStyle(d).marginTop);

          d._pxx = 0;
          if(cl.contains("parallax-x")) d._pxx = 1;  // this flag calls the X-parallax-function

          d._h = d.H(); // TODO: here we maybe have to wait for image.loaded to get effective height

          if(typeof d._h == UN)
          {
            console.warn("Seems height not defined yet. Fallback to 200.");
            d._h = 200;
            // TODO: Delay the height-read ?!
          }

          d._top = d.TOP(); 

          // you can overwrite the parallax-factor, for all or just mobile devices, using data-f and data-fmob

          d._pf = _PARALLAX_f;
          if(d.Ah("data-fmob") && _b.Ch("mobile"))
          {
            d._pf = d.A("data-fmob");
          }
          else if(d.Ah("data-f"))
          {
            d._pf = d.A("data-f");
          }

          if(d._pf == "by-outer")
          {
            var dp = d.parentNode,
                hp = dp.H(); 

            d._pf = (hp / d._h) * (hp / _hs);  // ratio(outer, inner) * ratio(outer, screen) 
            d._top = dp.TOP(); 
            d._h = 0; 
          }
          else d._pf = fF(d._pf);

          d._sy0 = d._top - _hs - d._h;  // offset, to be subracted from sy. Is 0 if element is at top-page  
          if(d._sy0 < 0) d._sy0 = 0;
          d._in = 1;
        }

        if(!cl.contains("in0")) 
        {
          // the first time

          cl.add("in0");
          OBS_in(d, 1, e);
        }
        else OBS_in(d, 0, e);
      }
    }
    else 
    {
      if(!cl.contains("out"))
      {
        // element just moved out of visibility 

        cl.add("out");
        cl.remove("in");

        if(_prlx) // parallax enabled
        {
          if(cl.contains("parallax")) 
          {
            var sk = 0;
            d._dy = 0;
            d._in = 0;
            if(d._pxx)
            {
              sk = X_parallax(d);
            }

            if(!sk)
            {
             d.CSS("transform", "translateY(0px)"); // reset it
            }
          }
        }

        OBS_out(d);
      }
    }
  }
}



// -----------------------------------------------------
function _FRAME()
{
  if(_prlx)  // parallax
  {
    var d, 
        sy = _win.scrollY, 
        a,
        dy, sk, ddy, ddya;

    for (d of _prla)
    {
      if(d._in)
      {
        a = sy - d._sy0;   // the effective scroll to use, same as sy in case of top-viewport (d._sy0 == 0)    

        if(a > 0)
        {
          dy = Math.round(a * d._pf);

          ddy = dy - d._dy;
          ddya = Math.abs(ddy);

          if(ddya > 0)
          {          
            d._dy += ddy;   

            sk = 0;   // skip 

            if(d._pxx)
            {
              sk = X_parallax(d);
            }  

            if(!sk)
            {
              d.style.transform = "translateY(" + d._dy + "px)"; 
            }
          }   
        }
      }
    }
  }

  FRAME(_FRAME);
}

// ------------------------------------------------------
function DYN(dp)   // Overwrites empty DYN() from init.js so that it works always
{
    // ------------------------- All Link Click Catcher
    for(d of _b.QA('a:not(.done-a)'))
    {
      if(!d.Ah("onclick")) d.Ea("click", aclick);
      d.Ca("done-a");
    } 

  if(typeof SLIDER_dyn != UN) SLIDER_dyn(dp);

  if(_PARALLAX_on)
  {
    if(dp.Q(".parallax"))
    {
      // reload the whole parallax dom array
      _prla = _b.QA(".parallax");
      _prlx = _prla.length ? 1 : 0;
    }
  }

  if(_OBS)
  {
    for(var d of dp.QA(".obs"))   // at MAKE all those get class obs:  _b.QA(".box-grid, .observe, .parallax, .fly-in") 
    {
      d.Cr("obs");
      _OBS.observe(d);
    }
  }

  if(typeof X_DYN != UN) FRAME(() => { X_DYN(dp); });
}

// --------------------------------------------------------------------
function KEY(e)
{
  var da = _doc.activeElement,
      code = e.which,
      char = String.fromCharCode(code);

  if(code == 27)  
  {
    if(fE("menu-hamburger").Ch("active"))
    {
      MH_close();
      e.preventDefault();
      return;  
    }
    X_KEY(e, da, code, char);  // ESC is given X-priority
  }

  if(da && da.TAG() == "input" && !da.Ch("readonly")) return;  // ignore within an input

  //console.log("KEY: '" + char + "' Code: " + code);

  if(code == 27) // ESC
  {
    var ret = 0;
    if(!(fE("popup").Ch("hide")))
    {
      popup_clean();
      ret=1;
    }
    
    if(!(fE("lightbox").Ch("hide")))
    {
      lightbox_clean();
      ret=1;
    }

    if(ret)
    {
      e.preventDefault()      
      return;
    }
  }

  X_KEY(e, da, code, char);
}


var SEC = _SEC;  // allows extending of SEC() in file 'total.js'
// -------------------------------------------------
function _SEC(e)
{
  if(_res)
  {
    RES();  
  }

  if(_scr)
  {
    _scr = 0;
    _scr_on = 1;
    _scrtl = _scrt;
    _scrt = _win.scrollY; 
    _scr_up = (_scrtl > _scrt) ? 1 : 0;  

    X_SCR_ongoing();
  }
  else
  {
    if(_scr_on)
    {
      _scrtl = _scrt;
      _scrt = _win.scrollY;
    }
  }

  if(_scr_on)
  {
    if(_scrt == _scrtl)
    {
      _scr_on = 0;
      X_SCR_after();
    }
  }

  // check for current section
  if(!_ssk)
  {
    if(_ss != _href)
    {
      _SSC();
    }
  }

  if(typeof SLIDER_SEC != UN) SLIDER_SEC();

  if(_INP_inp_cc > 0)
  {
    _INP_inp_cc --;
    if(_INP_inp_cc < 1)
    {
       _INP_change(0, _INP_inp_d);  
    }
  } 
}

// -----------------------------------------------------
function _SSC() // set section current
{
  // This function is the possible switch of the current section. if ss is empty we are at top.
  var hn = "";  // href new 

  if(_ss != "" && _win.scrollY > (_hs * 2))  // if scroll isn't two pages down it's top 
  {
    switch(_ss)
    {
    case "top":
    case "home":
    case "header":
    case "main":
    case "intro":
      break;
    default:
      hn = _ss;
      break;
    }
  }

  if(hn != _href) 
  {
    if(hn != "")
    {
      var sk=0, d, sy = _win.scrollY;

      // check if new section is really visible
      d = fE(hn);
      if(sy + _hs < d.TOP()) sk=1;
      else if(sy > d.TOP() + d.H()) sk = 1;

      if(!sk && _href != "")
      {
        // Now check if current section is still visible, and keep it in case
        d = fE(_href);
        if(sy + _hs > d.TOP() && sy < d.TOP() + d.H()) sk=1;
      }

      if(sk) return;
    }

    _hrefp =  _href;
    _href = hn; 
    history.pushState(null, null, "#" + hn);

    _b.Cr("section-" + _hrefp);
    _b.Ca("section-" + _href);
  }
}

// ----------------------------------------------------
function msg(text, sec, is_err, is_warn)
{
  // first check if same message is visible already...
  var doup = fE("msg-outer"),       
      hsh = hashi(text),
      dex = doup.Q(".msg-any.hash-" + hsh);
  if(dex) return dex.Cr("hide").id; 

  if(typeof(sec) == UN || isNaN(sec)) sec=0;
  if(typeof(is_err) == UN || isNaN(is_err)) is_err=0;
  if(typeof(is_warn) == UN || isNaN(is_warn)) is_warn=0;
  if(is_warn && is_err) is_warn = 0;  // either or

  if(!sec) sec = is_err ? 10 : (is_warn ? 6 : 4);

  _msg_cc++;

  var dou = is_err ? fE("msg-err") : (is_warn ? fE("msg-warn") : fE("msg-msg")),
      cl = is_err ? "err" : (is_warn ? "warn" : "msg"),
      id = "msg-" + _msg_cc,
      htl = fE("topline").H() - (0.19 * _1em), //  - (1.5 * _1em),
      dmsg = fCE("div").A({id: id,
                         class: "msg-any " + cl + " hash-" + hsh,
                         onclick: "msg_hide(this.id)"}).APs("<span>" + text + "</span>");

  doup.CSS({top: htl + "px"});

  dou.AP(dmsg);

  sec = fI(sec);
  if(sec)
  {
    setTimeout(() =>
    {
      msg_hide(id);
    }, sec * 1000);
  }

 // TODO:  dmsg.fadeIn(800).delay(sec*500).animate({top:"-=0.2em"},200).animate({top:"+=0.2em"},200).delay(sec*500).fadeOut(800);
  return id;
}

// -----------------------------------------------------
function msg_is_vis(id)
{
  var dmsg = fE(id);
  if(!dmsg) return 0;
  if(dmsg.Ch("hide")) return 0;
  return 1;
}

// ----------------------------------------------------
function err_exist()
{
  return !!(fE("msg-err").Q(".err:not(.hide)"));
}

// ----------------------------------------------------
function err_clean()  
{
  fE("msg-err").EMP();
}

// ----------------------------------------------------
function msg_clean()
{
  for(var d of fQA("#msg-outer > div")) d.EMP();
}


// ----------------------------------------------------
function err(text, sec)
{
  return msg(text, sec, 1);
}

// ----------------------------------------------------
function warn(text, sec)
{
  return msg(text, sec, 0, 1);
}

// ----------------------------------------------------
function msg_hide(msg_id)   
{
  var d = fE(msg_id);
  if(d) d.REM(); 
}

// ----------------------------------------------------
// ----------------------------------------------------

// ----------------------------------------------------
function lightbox(html, sec, is_slim, is_noclose, f_close)
{
    var d = fE("lightbox");
    if(!d)
    {
      d = fCE("div").A({id: "lightbox", role:"dialog", draggable: "true"});
      d.CE("div").A({id: "lightbox-sym-close"}).Ea("click", lightbox_clean)
        .CE("img").A({src:"../img/_but/but_close.png", alt: "close"});
      d.CE("div").A({id: "lightbox-content"});
      _b.AP(d);
    }  

    var dc = fE("lightbox-content");

    if(typeof sec == UN || isNaN(sec)) sec = 0;
    if(typeof is_slim == UN || !is_slim) is_slim = 0;
    if(typeof is_noclose == UN || !is_noclose) is_noclose = 0;

    d.Cr("hide");
    dc.EMP().APs(html);
    d.CSS("top", (_win.scrollY + 80) + "px");
    if(!is_noclose) BGF(0, lightbox_clean);
    d.Ct("slim", !!is_slim);
    d.Ct("lightbox-noclose", !!is_noclose);

    _f_close_lightbox = (typeof f_close == UN ? 0 : f_close);

    sec = fI(sec);
    if(sec)
    {
      setTimeout(() =>
      {
         lightbox_clean(1);
      }, sec * 1000);
    }
}

// ----------------------------------------------------
function lightbox_clean(is_enforced)
{
  var d = fE("lightbox");
  if(!d) return;

  if(typeof is_enforced == UN) is_enforced = 0;
  if(!is_enforced && d.Ch("lightbox-noclose")) return;

  if(typeof _f_close_lightbox != UN && _f_close_lightbox)
  {
    try
    {
      _f_close_lightbox();
    } 
    catch (ex)
    {
      console.error("f_close() for lightbox failed!", ex);
    }
  }
  _f_close_lightbox = 0;

  BGF_close();

  d.Ca("hide");
  fE("lightbox-content").EMP();
}


// ----------------------------------------------------
function loading_area_start(d)
{
  if(d.Ch("loading-area")) return;
  d.Ca("loading-area");
  d.APs('<div class="div-loading-area"><svg class="sym-loading sym-loading-area sym-rotate" viewbox="0 0 512 512"><use href="#svg-loading"></use></svg></div>');
}



// ----------------------------------------------------
function loading_area_end(d)
{
  if(!d.Ch("loading-area")) return;
  d.Cr("loading-area");
  d.Q(".div-loading-area").REM();
}

// ----------------------------------------------------
function loading_area_endall()
{
  for(d of fQA(".loading-area")) loading_area_end(d);
}

// ----------------------------------------------------
function loading_start()
{
  fE("div-loading").Cr("hide");
}

// ----------------------------------------------------
function loading_end()
{
  fE("div-loading").Ca("hide");
}

// ------------------------------------------------------
function GOTO(ids, fin, skipScroll, f_done)   // works with sections AND any anchors
{
  if(typeof f_done == UN) f_done = 0;
  var dd = fE(ids), 
      ds = 0,
      r;

  r = X_click("#" + ids);
  if(r === false || r === 0)
  {
    if(f_done) f_done();
    return;
  }    

  if(!dd)
  {
    ds = _m.Q("section.ca--" + ids);   // set by MAKE to unloaded sections, 'ca' means: 'contains-anchor'
    if(!ds)
    {
      console.error("Invalid Anchor call at GOTO: " + ids);
    }
    else
    {
      // a link within a section
      if(ds.Ch("unloaded"))
      {
        GOTO(ds.id, 0, 1, () =>
        {
          // if we don't check for sucessful loaded we might end up in a endless loop, well, not really ....
          if(ds.Ch("loaded")) GOTO(ids, fin, skipScroll, f_done);
        });
      }
      else
      {
        console.error("Confusing case, seems MAKE failed, thus invalid anchor call at GOTO: " + ids);
      }
    }
    return;
  }
  else
  {
    ds = dd.closest("section");  // if itself is section closest() returns itself
  }

  if(ds)
  {
    if(ds.Ch("unloaded"))
    {
      LAZY_section(ds.id).then(() => 
      {
        GOTO(ids, fin, skipScroll, f_done);
      });  
      return;
    }
  }

  SHOW(dd);  // unhides all parents, and cares for .readmore   

  if(typeof fin != UN && fin)
  {
    var isfin = true;

/*    if(ds.Ch("sub-show-one"))  // TODO: Handle other classes
    {
      if(!ds.Q(".section-sub > section.show"))
      {
        var ds0 = ds.Q(".section-sub section");
        if(ds0)
        {
          isfin = false;
          GOTO(ds0.id, 1, 1);
        }
      }
    } */

    if(isfin)
    {
      console.log("GO FINAL: " + ids);

      if(dd.TAG() == "section") 
      {
        _ss = ids;
        _SSC();  // set section current
      }

      if(typeof skipScroll == UN || !skipScroll)
      {
          SCROLL(dd, () =>
          {
            X_GOTO_after(ids);
            if(f_done) f_done();          
          });
          return; // !
      }
      else
      {
        console.log("SKIP Scroll to: " + ids);
      }

    }
  }

  X_GOTO_after(ids);
  if(f_done) f_done();
}

// ----------------------------------------------------
function popup(html, sec, is_slim, is_noclose, f_close)
{
    if(_tim_popup)
    {
      clearTimeout(_tim_popup);
      _tim_popup = 0;
    } 

    var d = fE("popup");

    if(!d)
    {
      d = fCE("div").A({id: "popup", role:"dialog", draggable: "true"});
      d.CE("div").A({id: "popup-sym-close"}).Ea("click", popup_clean)
        .CE("img").A({src:"../img/_but/but_close.png", alt: "close"});
      d.CE("div").A({id: "popup-content"});  
      _b.AP(d);
    }  

    if(typeof(sec) == UN || isNaN(sec)) sec=0;
    if(typeof(is_slim) == UN || isNaN(is_slim)) is_slim=0;
    if(typeof(is_noclose) == UN || isNaN(is_noclose)) is_noclose=0;

    d.Cr("hide");
    fE("popup-content").APs(html);
    d.CSS("top", (_win.scrollY + 80) + "px");
        
    if(!is_noclose) BGF(0, popup_clean);
    
    d.Ct("slim", !!is_slim);
    d.Ct("popup-noclose", !!is_noclose);

    if(typeof(f_close) == UN)_f_close_popup=0;
    else _f_close_popup = f_close;
    
    sec = parseInt(sec);
    if(sec)
    {      
      _tim_popup = setTimeout(function()
      {
        _tim_popup = 0;
        popup_clean(1);

      },sec*1000);
    }
}

// ----------------------------------------------------
function popup_clean(is_enforced)
{
  var d = fE("popup");

  if(typeof(is_enforced) == UN)is_enforced=0;
  if(!is_enforced && d.Ch("popup-noclose")) return;

  if(_tim_popup)
  {
    clearTimeout(_tim_popup);
    _tim_popup=0;
  } 

  if(typeof(_f_close_popup) != UN && _f_close_popup)
  {
    try
    {
      _f_close_popup();
    } 
    catch (ex)
    {
      console.log("ERROR, Outside given Function f_close() for popup failed!", ex);
    }
  }
  _f_close_popup = 0;

  d.Ca("hide");
  fE("popup-content").EMP();

  BGF_close();
}

// -----------------------------------------------------------
async function SHOW(d)  // Cares for class "hide". Unhides all parents, and cares for .readmore   
{
  var dp = d.offsetParent;
  if(dp && dp != _m && dp != _b)
  {
    await SHOW(dp);
  } 

  d.Cr("hide");

  if(d.TAG() == "section")
  {
    if(d.Ch("unloaded"))
    {
      await LAZY_section(d.id);
    }

    if(d.Ch("toggle")) 
    {
      d.EE("click");
    }
    else
    {
      var dsb = d.Q(".section-body"),
          drm = d.Q(".readmore");
 
      dsb.Cr("hide");
        
      if(drm) 
      {
        if(!drm.Ch("readless")) drm.EE("click");
      }            
    }
  }
  else
  {
    // check if this is some element after readmore
    if(d.Ch("hide-readmore"))
    {
      var dx = d;
      while(dx = dx.previousElementSibling)
      {
        if(dx.Ch("readmore") && !dx.Ch("readless")) dx.EE("click");
      }
    }
  }
}



// -----------------------------------------------------------
async function SW_init()
{
  await navigator.serviceWorker.register('../_sw.js');

  navigator.serviceWorker.onmessage = (e) => 
  {
    console.log("Got SW-Message: " + e.data);
  };
    
  if(navigator.serviceWorker.controller) _SW_mi();
  else 
  {
    console.warn("Service-Worker isn't ready for getting messages, using event controllerchange");
    SW_init.s0 = 0;
    navigator.serviceWorker.addEventListener("controllerchange", () => 
    {  
      console.log("OK, Controller-Change-event.");
      SW_init.s0 = 1;
      setTimeout(_SW_mi, 100);
    });  

    setTimeout(() =>
    {
      if(!SW_init.s0)
      {
        console.warn("Service-Worker- controllerchange went into timeout. Going intro try-loop.");
        _SW_mi();
      }      
    }, 2000);

  }  
}

// -----------------------------------------------------------
function _SW_mi()  // message init
{
  if(!navigator.serviceWorker.controller)
  {
    if (typeof _SW_mi.cc == UN ) _SW_mi.cc = 0;
    _SW_mi.cc++;
    if(_SW_mi.cc > 10)
    {
      _SW_mi.cc = 0;
      console.error("Service-Worker isn't ready for getting messages, tried 10 times, giving up!");
      return;
    }
    console.warn("Service-Worker isn't ready for getting messages, try Nr. " + _SW_mi.cc );
    setTimeout(_SW_mi, 100 * _SW_mi.cc);
  } 
  else
  {
    _SW_mi.cc = 0;
    navigator.serviceWorker.controller.postMessage("INIT"); 
    console.log("OK, Service-Worker INIT Message sent!");
  }
}

// -----------------------------------------------------------
function SW_msg(msg)
{
  navigator.serviceWorker.controller.postMessage(msg)
}


// -----------------------------------------------------------
function dropdown_click(e)
{
  var da = this,
      dli = da.closest("li"),
      dsub = dli.Q(":scope > .li-sub"),
      dmenu = dsub.closest("nav"),
      dmh = fE("menu-hamburger"),
      hh = dsub.Ch("hide"); // had hide       
  
  dsub.Ct("hide");

  if(_b.Ch("menu-top"))
  {
    if(hh) 
    {
      BGF(dsub, () => { da.EE("click"); }).Ca("trans");
    }
    else 
    {
      BGF_close();
    }
  }

  if(dmenu.id == "menu-main" && dmenu.Ch("active") && dmh.Ch("active"))
  {
    // Here we are in main-menu in Hamburger, we need to move the menu-sys
    fE("menu-sys").CSS({top: (dmenu.offsetTop + dmenu.H())+"px"});
  }

  e.preventDefault();
}   

// -----------------------------------------------------------
function flip_click(e)
{
  var d = this;
  e.preventDefault(); 
  d.Ct("is-flip");
  if(!d.Ch("is-flip") && d.Ch("flip-v-toggle")) d.Ct("flip-v");
}

