
_iT = 1;  // is Total, from global.js

var _test_lazy_js = 0;

// --------------------------------------------------------
function I01()   // IMPORTANT: This OVERWRITES the same function from the file 'init.js'
{
  if (!_test_lazy_js) 
  {
    I01_T();
    return I02();
  }

  var dp = fE("div-script-lazy"),
    as = dp.QA("script"),
    ap = [];

  dp.remove();  // done by MAKE otherwise

  for (ds of as) 
  {
    var src = ds.A("src"),
      p0 = src.lastIndexOf('/'),
      s = src.substr(p0 + 1, src.length - p0 - 4),
      sp = src.substr(0, p0);
    if (!src.endsWith(".js")) 
    {
      console.error("Lazy loading invoked with non-JS-file");
    }
    if (sp.endsWith("/_sys")) s = "_sys/" + s;
    ap.push(LAZY(s));
  }

  Promise.all(ap).then(() => 
  {
    I01_T();
    I02();
  });
}


// -------------------------------------------------
function I01_T()   // The Special Total INIT (after LAZY-load) and before I02()
{
  LANG00();  

  // All the work here is done by MAKE otherwise
  // ----------------------------
  var d, ad;

  if (!_b.Ch("menu-left") && !_b.Ch("menu-top")) _b.Ca("menu-onclick");

    // ---------------------------------------------

  for (d of _b.QA(".lang-list img[data-lang]")) 
  {
    d.Ea("click", lang_fclick);
  }

  // ---------------------------------------------

  //  Give sections some metadata, thats otherwise done by MAKE
  for (d of _m.QA("section")) 
  {
    var dp = d.parentNode.closest("section");
    if (dp) 
    {
      d.Ca("ps-" + dp.id);
    }
    else d.Ca("ps-main");
  }

  // ---------------------------------------------

  // Prepare some helpful metadata for box-grids...
  for (d of _b.QA(".box-grid"))
  {
    var dcb = d.Q(":scope > .box-grid-body"),
        as = dcb.QA(":scope > .box"),
        cc = 0, db;
    d.A("data-n", as.length);
    for (db of as) 
    {
      db.Ca("i-" + cc).A("data-i", cc);
      cc++;
    }
  }

  // ---------------------------------------------
    

    //  --- menu dropdown per JS
    for (d of _b.QA("ul.dropdown li, li.dropdown"))
    {
      if(d.Ch("dropdown-css") || d.Ch("dropdown-js")) continue;
      d.Ca("dropdown-js");
      var da = d.Q("a");
      if(da)
      {
        var dli = da.closest("li"),
            dsub = dli.Q(":scope > .li-sub");
        if(dsub)
        {
          dsub.CSS({display: "block"}).Ca("hide");
  
          da.Ea("dblclick", aclick);
          da.Er("click", aclick);
          da.Ea("click", dropdown_click);
        }
      }
    }
  
    // ---------------------------------------------
  
    // toggle structures (toggles class hide on the second & third child! Assuming first child is the clickable header)
    for (d of _b.QA(".toggle:not(.done-toggle)"))
    {
      var ac = d.children,
          h;
      if(ac.length > 1)
      {
        ac[0].Ea("click", toggle);
        d.Ca("done-toggle");
        h = ac[1].Ch("hide");
        d.Ct("toggle-in", !h);
        d.Ct("toggle-out", h);
        }
      else d.Cr("toggle");   // remove class 'toggle' if not at least 2 children
    }
  

  // ---------------------------------------------

  // readmore-divs in content, otherwise done at MAKE
  ad = _m.QA(".readmore:not(.done-readmore)");
  if(ad.length)
  {
    v = LLS("readmore");
    for (d of ad)
    {
      var dx = d;
      while(dx = dx.nextElementSibling)
      {
        dx.Ca("hide").Ca("hide-readmore");
        if(dx.Ch("readmore")) break;
      }

      d.Ea("click", readmore).Ca("done-readmore");  
      d.EMP().CE("span").APs(v);
    }
  }


  // ------------------------------------------------

  // SET default visibility to sections (regarding sub-show-classes)
  for(ds of _m.QA("section"))
  {
    var id = ds.id,
        filter = "";

    if(!ds.Ch("sub-show"))
    {
      for(var d of fQA("section.ps-" + id)) d.Ca("hide");
      if(ds.Ch("sub-show-one")) filter=":lt(1)";
      if(ds.Ch("sub-show-two")) filter=":lt(2)";
      if(ds.Ch("sub-show-three")) filter=":lt(3)";

      if(filter.length) 
      {
        for(var d of ds.QA("section.ps-" + id + filter)) d.Cr("hide");
      }
    }


    // If section has no toggle and the title has no other link, make it clickable...
    
    if(!ds.Ch("toggle"))
    {
      var dt = ds.firstElementChild,
          onc = dt.Ah("onclick"),
          dh, drm;
      if(!onc)
      {
        da = dt.Q("a");
        if(!da)
        {
          da = dt.Q("*[onclick]");
          if(!da)
          {
            dh = dt.Q("h2, h3, h4, h5");
            if(dh)
            {
              drm = ds.Q(".readmore");
              if(drm)
              {
                // only if no other links and readmore exists a.s.o.
                dh.Ca("inline").Ca("cursor-pointer").Ea("click", titclick);
              }
            }
          }
        }
      }
    }
  }

  // ------------------------------------------------

  for(d of _m.QA(".but-more")) d.Ea("click", function(e)
  {
      var si = fI(fQ("section:visible:last").A("data-si")) + 1,
          ids = fQ("section.si-" + si).Cr("hide").id;
      if(ids) SECTION_load(ids, 0);
      else
      {
        for(var d of _m.QA("section"))
        {
          SECTION_load(d.Cr("hide").id, 0);
        }
        _win.scrollTo(0,0);
      }
  });


  // ------------------------------------------------

  if(!_OBS)
  {
    // this is a hack because at release we want to create OBS later, just before first DYN-call
    _OBS = new IntersectionObserver(OBS_f, 
      {rootMargin: "33% 0% -6% 0%"});    /* TODO: Understand options and set them well { tresholds:0, rootMargin: "33% 0 33% 0" } */
  }

  // Observe default alements here, over MAKE done at release by DYN()
  for(d of fQA("#header, #footer, section, .box-grid, .observe, .parallax, .fly-in"))
  {
    _OBS.observe(d);
  } 




  CLICK("#login");


  // Note: AJAX does not work with browsers on local 'file://'. CORS-restriction. A bit sad. 
  // for(d of fQA("section.file")) LOAD("./section_" + d.id.replace(/\W/,"_") + ".htm").then((ht) => { d.APs(ht); });
  // This means: If you are tech, and you want sections in their own htm-files, make it happen easy with the line above.

}


// -------------------------------------------------
function LANG00()  
{
  var l = SET("com-lang");
  if (typeof l != UN && typeof l == "string") 
  {
    LANG(l);
  }
  else 
  {
    if (_doc.Ah("lang")) 
    {
      LANG(_doc.lang);
    }
    else 
    {
      LANG(_lang);  // fallback
    }
  }
}


// ----------------------------------------------------
function LANG(l)    // Overwrites the LANG() from file 'init.js'
{
  var lp = _doc.A("lang");

  if (!l.length) 
  {
    console.error("LANG called with no language");
  }

  if (l == _lang && l == lp) return;

  console.log("LANG() of 'total.js' called with: " + l);

  _b.Cr("lang-" + lp).Ca("lang-" + l);
  _doc.A("lang", l);

  _lang = l;
  _LS.setItem("com-lang", l);

  // Some more special work to ensure dynamic language switch. Note: This is anyway not in the release
  for (var d of _b.QA(".readmore")) d.EMP().CE("span").APs(LLS("readmore"));
}



// -------------------------------------------------
function SEC() 
{
  _SEC();

  if (_lgerr) 
  {
    err(LLS("login-error"));
    _lgerr = 0;
  }

  if (_uok0 == 1) 
  {
    _uok0 = 0;
    msg("<div><span class='align-center'>" + LLS("welcome") + " " + un + "!</span></div>", 1);

    /*
    (function da77() {
      POSTA(_xr_ut + "_HTM/index.php", { m: "Acting against human dignity means bad to me and my beloved as well!" }).then((d) => {
        // console.log("DATA0: ", d);

        loading_end();
        if (!_iT) DYN(_m.EMP().APs(d));  // TODO: Improve this replace, ment for running wyswyg not on total
      });
        })();

      */

      POSTA(_xr_ut + "_make/get_admin_js.php").then((d) => 
      {
        // TODO: add script
        _b.CE("script").APs(d);

        loading_end();

      });

  }

  if (typeof TOOL_SEC != UN) TOOL_SEC();
}

