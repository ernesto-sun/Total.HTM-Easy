// /*
// * ------------------------------------------------------------
// * ------------  SLIDER MODULE for Total.HTM Easy -------------
// * ------------------------------------------------------------
// *
// *  @website     http://exa.run/Total.HTM-Easy
// *
// *  @author      Ernesto Sun <contact@ernesto-sun.com>
// *  @version     2020-12-09
// *
// *  @copyright   Ing. Ernst Johann Peterec http://Ernesto-Sun.com
// *  @license     JSON Software License http://open.exa.run/Total.HTM-Easy
// *
// */

// A few functions that need to be called from outside ...

// SLIDER_dyn(d);   // this is like DYN() and works for all sliders within
// SLIDER_SEC();    // so that no extra timer is needed

// Further possible to call:

// SLIDER_left(d);  
// SLIDER_right(d);  
// SLIDER_play(d);  
// SLIDER_stop(d);  

// SLIDER_goto(d, i);  

// Use 'SLd(obj)' to ensure the right input d (slider-DOM-obj) 

// Note: we rely here on DYN() .box-grid i-class and i-data-attr


var SLP = {}, // Sliders with play-js are in this data-structure
    _slpa = {};   

// -----------------------------------------
function SLd(d)  // SLIDER ensure DOM element
{
    return d.closest(".box-grid-slider");
}


// --------------------------------------------
function SLIDER_dyn(dp)
{      
    var ae = dp.QA(".box-grid-slider:not(.done)");

    for(de of ae)
    {
        id = de.id;
        if(typeof id==UN || !id)
        {
            id = "sl-" + rint();
            de.id = id;
        }

        _slpa[id] = [de, getComputedStyle(de)]; 
    }

    setTimeout(() => 
    {
        // This is basically a seperation of reading (getComputedStyle) and other DOM-work later
        // TODO: This does not check if SLIDER_dyn is called multiple times within the 100 ms
        FRAME(_SLF1);
    }, 100);
}

// ---------------------------------
function _SLF1()
{
    for(var id in _slpa)
    {
        var de = _slpa[id][0], 
            deb = de.Q(":scope > .box-grid-body"),
            as = deb.QA(":scope > .box"),
            n = as.length,
            cl = de.classList,
            dbi = deb.Q(":scope > .box.init"),
            i=0,
            sty = _slpa[id][1],
            dir = sty.getPropertyValue("--slider-direction").toLowerCase(),
            st = (dir.includes("reverse") ? -1 : 1);

        if(!dbi)
        {
            dbi = st > 0 ? as.item(0) : as.item(as.length - 1);    
            dbi.Ca("init");
        }
        
        i = fI(dbi.A("data-i"));


        if(n > 1 && cl.contains("play") && !cl.contains("play-css"))
        {
            var sec = fI(sty.getPropertyValue("--slider-sec")),
                fsec = fI(sty.getPropertyValue("--fade-sec")),
                run = (sty.getPropertyValue("--play-state").toLowerCase().includes("paused") ? 0 : 1),
                isec = (run ? fI(sty.getPropertyValue("--slider-init-sec")) : 0),
                alt = (dir.includes("alternate") ? 1 : 0),
                itr = sty.getPropertyValue("--slider-iteration"),
                ccl; 

            if(isec < 4) isec= 0;   // up to 3 sec init-time run immediatly here after JS was loaded a.s.o.

            if(isNaN(itr)) itr = 0;
            else itr = fI(itr);
            if(itr < 1) itr = 1000000;

            if(sec > 0 || fsec > 0)
            {
                cl.add("play-js");

                if(isec < 1 && run) 
                {                    
                    // run straight away
                    dbi.Cr("init").Ca("active");
                    cl.add("running");
                }

                // count of lazy images to load
                ccl = de.QA("template.unloaded").length;

                SLP[id] = [run, sec, fsec, isec, sec, 0, i, n, st, alt, itr, ccl];  
            }

            if(!cl.contains("no-nav"))
            {
                if(!cl.contains("no-lr"))
                {
                    de.APs(["<div class='slider-nav slider-prev look-menu'>",
                    "<img class='sym' src='../img/_but/but_left.png' alt='left' onclick='SLIDER_left(SLd(this))'/></div>",
                    "<div class='slider-nav slider-next look-menu'>",
                    "<img class='sym' src='../img/_but/but_right.png' alt='right' onclick='SLIDER_right(SLd(this))'/></div>"].join(""));
                }

                if(!cl.contains("no-ps"))
                {
                    de.APs(["<div class='slider-nav slider-play look-menu'>",
                    "<img class='sym' src='../img/_but/but_play.png' alt='play' onclick='SLIDER_play(SLd(this))'/></div>",
                    "<div class='slider-nav slider-stop look-menu'>",
                    "<img class='sym' src='../img/_but/but_pause.png' alt='stop' onclick='SLIDER_stop(SLd(this))'/></div>"].join(""));
                }
            }

            _SLlr(de,id,i);
        }

        cl.add("done");
        de.style.setProperty("--slider-count", n);
    }

    _slpa = {};
}

// -----------------------------------------
function SLIDER_SEC()
{
    for(id in SLP)
    {
        if(SLP[id][0])
        {
            var d = fE(id);
            if(d.Ch("out")) continue;
            // running...

            if(SLP[id][11] > 0)
            {
                // still lazy images to load
                var ix = _SL_inext(id, 0),  // next index without side-effects
                    dix = d.Q(":scope .box.i-" + ix + " template.unloaded");
                if(dix)
                {                    
                    dix.BEFs(dix.innerHTML);
                    dix.REM();
                    SLP[id][11] --;
                }
            }

            if(SLP[id][3] > 0)
            {
                // init-phase
                SLP[id][3]--;
                if(SLP[id][3] < 1)
                {
                    // first time start after init-phase
                    var db = d.Q(":scope > .box-grid-body"),  
                        dbi = db.Q(":scope > .box.init");
                    
                    dbi.Cr("init");
                    dbi.Ca("active");
                    d.Ca("running");
                }
            }
            else
            {
                if(SLP[id][5] > 0)
                {
                    // we are in fading
                    SLP[id][5]--;
                    if(SLP[id][5] < 1)
                    {
                        // end fading
                        var db = d.Q(":scope > .box-grid-body"),  
                            dbfi = db.Q(":scope > .box.fade-in"),
                            dbfo = db.Q(":scope > .box.fade-out");
                            
                        if(dbfi) dbfi.Cr("fade-in");
                        if(dbfo) dbfo.Cr("fade-out").Cr("active");                        
                    }
                }
                else
                {
                    // sliding ...
                    SLP[id][4]--;
                    if(SLP[id][4] < 1)
                    {
                        // next slide...
                        var i = SLP[id][6],
                            inext = _SL_inext(id, 1),
                            db = d.Q(":scope > .box-grid-body"),  
                            dprev = db.Q(":scope > .box.i-" + i),
                            dnext = db.Q(":scope > .box.i-" + inext);

                        _SLsw(d, id, dprev, dnext, inext, 0);
                    }
                }
            }
        } 
    }
}


// -----------------------------------------
function _SL_inext(id, se)  // returns the index of the next slide with or without side-effects
{
    var i = SLP[id][6],
        n = SLP[id][7],
        st = SLP[id][8],
        alt = SLP[id][9],
        inext = i + st,
        a = 1;

    if(typeof se == UN) se = 0;

    if(inext >= n)
    {
        if(alt)
        {
            if(se) SLP[id][8] = -1;
            inext = n - 2;  
        }
        else
        {
            inext = 0;  
        }
    }
    else if(inext < 0)
    {
        if(alt)
        {
            if(se) SLP[id][8] = 1;
            inext = 1;  
        }
        else
        {
            inext = n - 1;  
        }
    }
    else a = 0;

    if(a && se) // if new iteration and side-effects ...  
    {
        SLP[id][10]--;
        if(SLP[id][10] < 1)
        {
            SLIDER_stop(d);
        }
    }
    return inext;
}




// -----------------------------------------
function _SLsw(d, id, dprev, dnext, inext, skf)  // SLIDER switch between two slides (Without further checks!!!)
{
    // skf means: skip fading

    SLP[id][4] = SLP[id][1]; // reset sec-counter

    dnext.Ca(SLP[id][0] ? "active" : "init");
    SLP[id][6] = inext;

    if(!skf && SLP[id][2] > 0 && SLP[id][0])
    {
        // start fading
        SLP[id][5] = SLP[id][2]; // reset fade-counter

        dprev.Ca("fade-out"),
        dnext.Ca("fade-in");
    }
    else
    {
        // next slide direct switch
        dprev.Cr("active").Cr("init"); 
    }

    _SLlr(d,id,inext);
}


// -----------------------------------------
function _SLlr(d, id, i)  // checking leftmost and rightmost
{
    d.Cr("leftmost").Cr("rightmost");
    if(i >= (fI(d.A("data-n")) - 1)) d.Ca("rightmost");
    if(i <= 0) d.Ca("leftmost");
}


// -----------------------------------------
function SLIDER_left(de)
{
    var id = de.id,
        slp = SLP[id],
        iprev = slp[6],
        inext = iprev - 1;

    if(iprev > 0)
    {
        console.log("LEFT: ", inext);
        _SLgo(de, id, iprev, inext);
    }
}


// -----------------------------------------
function SLIDER_right(de)
{
    var id = de.id,
        slp = SLP[id],
        iprev = slp[6],
        n = slp[7],
        inext = iprev + 1;  

    if(inext < n)
    {
        console.log("RIGHT: ", inext);
        _SLgo(de, id, iprev, inext);
    }
}


// -----------------------------------------
function _SLgo(de, id, iprev, inext)
{    
    var deb = de.Q(":scope > .box-grid-body"),
        dprev = deb.Q(":scope > .box.i-"+iprev),
        dnext = deb.Q(":scope > .box.i-"+inext);

    _SLsw(de, id, dprev, dnext, inext, 1);
}


// -----------------------------------------
function SLIDER_goto(de, i)
{
    var id = de.id,
        iprev=SLP[id][6];
    _SLgo(de, id, iprev, i);
}


// -----------------------------------------
function SLIDER_play(de)
{
    var id = de.id,
        deb = de.Q(":scope > .box-grid-body"),
        dbi = deb.Q(":scope > .box.active"),
        i=SLP[id][6],
        d;

    if(!dbi) dbi = deb.Q(":scope > .box.init");
    if(dbi) i = fI(dbi.A("data-i"));

    for (d of deb.QA(":scope > .box")) d.Cr("active").Cr("init");

    var dnext = deb.Q(":scope > .box.i-" + i);
    dnext.Ca("active");
    de.Ca("running");

    //CSSVar(de,"play-state","running");
    SLP[id][6] = i;
    SLP[id][0] = 1;           
}


// -----------------------------------------
function SLIDER_stop(de)
{
    var id = de.id,
        deb = de.Q(":scope > .box-grid-body"),
        dbi = deb.Q(":scope > .box.active"),
        dbn = deb.Q(":scope > .box.active.fade-in"),
        d, ad;

    SLP[id][0] = 0;
    
    de.Cr("running");

    ad = deb.QA(".active");
    for(d of ad) d.Cr("active");

    ad = deb.QA(".fade-in");
    for(d of ad) d.Cr("fade-in");

    ad = deb.QA(".fade-out");
    for(d of ad) d.Cr("fade-out");

    if(!dbn) dbn = dbi;
    if(!dbn) dbn = deb.Q(":scope > .box"); 
    //CSSVar(dbn,"play-state","paused");
    dbn.Ca("init");
    SLP[id][6] = fI(dbn.A("data-i"));
}


