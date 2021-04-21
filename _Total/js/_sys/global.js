
// In fact most of the JavaScript of Total.HTM Easy plays in the global space
//
// I like short simple fast global variables. 
//
// You might be right, mentioning, that global vars are dangerous together
// with other libraries and frameworks. This is an interesting discussion.    
//
// 
// -------------------------------------------------------------

// Some globals that are ment to be overwritten at the top of Total.HTM
var 
MIN_EM =            30,
MAX_EM =            60,

_PARALLAX_on =      1,
_PARALLAX_f =       0.4;


// -------------------------------------------------------------
// Internal globals, used for all kind of operations 
let 
UN = "undefined",     // helper to write less at each typeof
_lang = "en",         // The default language set at <html lang="xx">, the value set here is the last fallback 
_win = window,              
_d = document,
_doc = _d.documentElement,
_b,                   // set to document.body in function I00() in file 'init.js'
_h = _d.head,
_m,                   // fQ("main")
_print = 0,           // the window object for print output, or 0 if none 
_wm = 0,              // width of main (of <body> in fact) 
_ws = 0,              // width-screen
_hs = 0,              // height-screen
_rs = 0,              // ratio-screem: height/width: If > 1 is portrait, else landscape
_wsp = 0,             // width-screen-previous
_hsp = 0,             // height-screen-previous
_1em = 16,            // the current pixel per em
_1emo = 16,           // the original value of 1em (before first JS-resizing)
_1emd,                // the hidden sys dom element of 1em width and 1em height
_100v,                // the hidden #div-100v to meassure CSS-screen-width, height
_res = 0,             // 'dirty_resize'    
_rese = 0,            // ensure resize
_href = "",           // current href url 
_hrefp = "",          // previous href url
_scr = 0,             // 'dirty_scroll'
_scr0 = 0,            // 1 if dirty_scroll was ever set
_scrt = 0,            // current scroll-top
_scrtl = 0,           // last scroll_top (previous _scrt)
_scr_on = 0,          // 1 if scrolling is ongoing
_scr_up = 0,          // 1 if ongoing (or previous) scrolling was upwards
_prlx = 0,            // will be 1 if _PARALLAX_on && _b.QA(".parallax").length
_prla,                // array of .parallax dom
_zoom = 1,            // Zoom factor for whole page
_tz = 0,              // Default Time Zone offset in minutes (-720 to 720)
_tim = 0,             // The main timer calling SEC() (each second)
_OBS,                 // The ref to the nice IntersectionObserver
_th = "light",        // name of current theme, Fallback: 'light'. See setting.js
_TH,                  // reference to the current theme info (see setting.js)
_LS = window.localStorage,  // Ref to Localstorage for UX-settings
_xr_ut = "../",        // Path to /_Totfal/
_auto_login = 1,       // Enable/disable auto-login-function here 
_uok0 = 0,
_uok  = 0,
_lgiu = atob("X2xvZ2luL2xvZ2luX2luaXQuanM="),          // "_login/login_init.js",
_lgerr = 0,                 
_INP_inp_d = 0,
_INP_inp_cc = 0,
_iT = 0,              // is Total (if Total.HTM is run directly, not release)
_skeyb = 0;           // 1 if soft-keyboard is popped up to avoid resize-event


// --------------------------
// Global function shortcuts to produce less code

let
fI =    parseInt,
fF =    parseFloat,
fR =    Math.round,
fCE = _d.createElement.bind(_d),
fQ =  _d.querySelector.bind(_d),
fQA = _d.querySelectorAll.bind(_d),
fE =  _d.getElementById.bind(_d),
fAP = _d.appendChild.bind(_d),
FRAME = requestAnimationFrame,
IDLE = typeof requestIdleCallback != UN ? requestIdleCallback : FRAME;  // We anyway dont use timeout, safari 2021 fallback to FRAME

var ep = Element.prototype;  // cant just use HTMLElement, because e.g. <svg> isn't covered then.

ep.Q = ep.querySelector;
ep.QA = ep.querySelectorAll;

ep.AP = function(d) { this.appendChild(d); return this; }
ep.AP0 = function(d) { this.prepend(d); return this; }

ep.APs = function(s) { this.insertAdjacentHTML('beforeend', s); return this; }   // this is for appending html as string

ep.CE = function(n) { var d=fCE(n); this.AP(d); return d; }
ep.CE0 = function(n) { var d=fCE(n); this.AP0(d); return d; }

ep.Ch = function(n) { return this.classList.contains(n); }
ep.Ca = function(n) { this.classList.add(n); return this; }
ep.Cr = function(n) { this.classList.remove(n); return this; }
ep.Ct = function(n, b) { this.classList.toggle(n, b); return this; }

ep.EMP = function() { var d = this, c; while(c = d.lastElementChild) d.removeChild(c); d.textContent = ""; return d; }  // empty
ep.REM = function() { return this.parentNode.removeChild(this); }    // REM() remove returns the DOM-ref, like detach 

ep.BEF = function(d) { this.parentNode.insertBefore(d, this); return d; }    // BEFORE 
ep.AFT = function(d) { var dp=this.parentNode; if(dn = this.nextSibling) dp.insertBefore(d, dn); else dp.appendChild(d); return d; }    // AFTER 

ep.BEFs = function(s) { this.insertAdjacentHTML('beforebegin', s); return this; }   // this is for inserting html before an element

// Note:  With ES6 its possible to just use .after() and .before()

ep.TAG = function(d) { return this.nodeName.toLowerCase(); } 

// ------------------------------
ep.CSS = function(n, v)
{
    var x = this.style,
        y = typeof n; 
    if(typeof v == UN)
    {
        if(y == "string") return x[n];               // get
        if(y == "object") for(k in n) x[k] = n[k];   // set by object
    }
    else x[n] = v;  // set by name and value
    return this;
}

ep.W = function(v) { if(typeof v == UN) return this.offsetWidth; this.CSS({width: fI(v) + "px"}); return this; }
ep.H = function(v) { if(typeof v == UN) return this.offsetHeight; this.CSS({height: fI(v) + "px"}); return this; }

// Note: TOP() and LEFT() give absolute page-values. for relative ones use .offsetTop, for viewport use .clientTop
// Important: The biggest performance issue with JS is when you mix DOM-relevant GET and SET within a function. This can make multiple repaints/reflows and the browser gets really slow. getBoundingClientRect() is such a DOM-relevant getter, but there are many more.
ep.TOP = function() { return this.getBoundingClientRect().top + _win.scrollY; }   
ep.LEFT = function() { return this.getBoundingClientRect().left + _win.scrollX; }

ep.Ah = function(n) { return this.hasAttribute(n); }
ep.Ar = function(n) { this.removeAttribute(n); return this; }

ep.A = function(n, v)  // just give one object with key:value pairs for multiple setting
{
    if(typeof v == UN)
    {
        if(typeof n == "object")
        {
            for(k in n) this.setAttribute(k, n[k]); // set by object
        }    
        else 
        {
            v = this.getAttribute(n);  // get
            return v ? v : "";
        }
    }
    else this.setAttribute(n, v);  // set
    return this;
}

ep.Ea = function(n, f) { this.addEventListener(n, f); return this; }
ep.Er = function(n, f) { this.removeEventListener(n, f); return this; }
ep.EE = function(n) { this.dispatchEvent(new Event(n)); return this; }   // trigger

//
//
// Here, after all this 'mess' with globals and extending, a note:
//
// I know I 'shall' not extend JS-standard-prototypes, but it's so nice
// and the naming convention never conflicts if first char is capital. (...) 
// If performance/memory is affected because each DOM Element is bigger, then
// I agree to better avoid it, otherwise the advantages outweigh in my present view ...
// 
// TODO: Discuss and clarify ...

