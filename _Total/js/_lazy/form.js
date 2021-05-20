
// ----------------------------- Total.HTM Easy Form JS ---------------
// 
// This file supports a number of input types, but NOT:
//
// * Multi-Select
// * File-Upload
// * Combi (Combinations of multiple inputs)
// * Filters (for search forms)
// * Table / List / Grid
//
// This file is used for UX-settings, contact forms, website editing, ...
// Find out about 'Total.HTM App', if you want to create something more complex.
//
// Note: See the file 'settings.js' to see an example for form-definitions 

let _FORMa = {},
    _FORM_INP_cc = 1;    // a counter to give every input its unique id  

// Note: The functions SYM_make() and BUT_make() are defined in sys_util.js

// ------------------------------------------------------
function FORM_make(idf, S, O)  // S is the form-definition, O are the options for combo-boxes
{    
    var dio, idc, diol, dioi,
        dfo = fCE("div").A({id: idf, class: "form"}),
        df = fCE("form"), // .A({action: "#", method: "post"}),
        dp = df;

    if(typeof O == UN) O = {};

    for(s of S)
    {
        switch(s[1])
        {
        case "chapter":
        {
            // Note: we do NOT use <fieldset> for form-chapters, because those are nicer for grouping radios, combis
            dp = fCE("div").A({class: "chapter-body"});
            df.CE("div").A({id: idf + "-" + s[0], class: "chapter"}).CE("h5").APs(LL(s[2]));
            df.AP(dp);
            continue;            
        }
        case "but":
        {
            if(s[0] == "but-send")
            {
                var url = ""+s[4];
                url = url.trim();
                if(url.startsWith("http"))
                {
                    // default send button with url given, make it onsubmit
                    df.A({onsubmit: "event.preventDefault(); return false;", "data-url": url}); 

                    // and add captcha
                    dp.APs(["<div class='div-captcha'><div>",
                    "<img class='captcha-image' src='", url, "/_3p/securimage/securimage_show.php?namespace=", idf ,"' alt='Captcha Image' ",
                    "onload='_FORM_cap_chk.call(this, event)' onerror='_FORM_cap_err.call(this, event)'/>",
                    "<img src='../img/_but/but_reload.png' class='sym captcha-reload cursor-pointer' onclick='_FORM_cap.call(this, event)' alt='Different image' /></div>",
                    "<input type='number' name='captcha-code' size='1' minlength='1' maxlength='1' min='2' max='9' step='1' required='required' placeholder='?' ",
                    "autocomplete='off' autocapitalize='off' autocorrect='off' autospellcheck='off' value=''/></div>"].join(""));
                }
            }    
        }
        }

        dio = INP_make(s, typeof O[s[0]] != UN ? O[s[0]] : undefined);    
        dp.AP(dio);

        idc = idf + "-" + s[0];
        dio.A({id: idc});
    }
    dfo.AP(df);

    _FORMa[idf] = {d: dfo, S: S, O: O}; 

    return dfo; 
}


// ----------------------------------------------------------------
function COM_txt(d)
{
    d = INP_d(d);
    var di = d.Q("select"), 
        n = "", 
        i;
    if(di)
    {
        i = di.selectedIndex;
        if(i >= 0)
        {
            n = di.options[i].text;
        }
    } 
    return n;
}

// ----------------------------------------------------------------
function BUT_click(e)
{
    e.preventDefault();
    var n = this.A('data-n'), r;

    if(n == "but-send")
    {
        var df = this.closest("form");
        if(df)
        {
            _FORM_submit.call(df, new Event("submit", {cancelable: true})); 
            return false;
        }
    }
    r = CLICK(n);
    if(r)
    {
        console.error("Invalid / unhandled button clicked: ", this.id," Tipp: Look for function X_click() in X.js ");
    }
}


// ----------------------------------------------------------------
function INP_d(d)   // return closest .control, by d or id 
{
    return d.closest(".control");
}


// ----------------------------------------------------------------
function INP_ro(d, b)   // Set readonly 1 / 0 
{
    if(typeof b == UN) b = true;
    else b = !!b; 
    d = INP_d(d);
    d.Ct("readonly", b);

    var adi = d.QA("input, select, textarea"), 
        y = d.A("data-y"), 
        di, yi;
    switch(y)
    {
    case "com":
    case "datetime":
    {
        for (di of adi) di.Ct("hide", b);            
        di = d.Q(".inp-ro");
        if(!di) di = d.CE("input").A({class: "inp-ro", readonly: "readonly", type:"text"});        
        di.value = (y == "com" ? COM_txt(d) : INP_val(d));
        di.Ct("hide", !b);
        break;
    }        
    default:
    {
        for (di of adi)
        {
            if(b) di.A("readonly", "readonly");
            else di.Ar("readonly");
            di.readonly = b;  // Note: Additionally, but mostly ingnored seemeingly
            yi = di.A("type"); 
            switch(yi)
            {
            case "checkbox":
            case "radio":
            case "range":
            case "color":
                di.disabled = b;
                break;
            }
        }         
    }
    }
}

// ----------------------------------------------------------------
function INP_val_DB(d, v)  
{   
    d = INP_d(d);
    if(!d) 
    {
        console.error("INP_val_DB() called with invalid d: ", d);
    }

    if(typeof v == UN)
    {
        d.Ar("data-val-db");
        v = INP_val(d);
        d.Ct("uxc", !!v);
    }
    else
    {
        v = INP_val(d, v);
        d.A("data-val-db", v);
        d.Cr("uxc");
    }
}


// ----------------------------------------------------------------
function INP_val(d, v)  
{
    d = INP_d(d);
    if(!d) 
    {
        console.error("INP_val() called with invalid d: ", d);
    }

    var y = d.A("data-y"),
        g = (typeof v == UN),  // if is get
        st = 0, // standard, like text-value
        di, v2;  

    if(!y) return v;

    switch (y)
    {
    case "dyn":
    {
        // Nothing to do
        return v;
    }
    case "lab":
    {
        di = d.Q("span");
        if(di)
        {
            if(g) return di.innerHTML;
            di.innerHTML = v;
        } 
        return v;
    }
    case "but":
    {
        v2 = d.A("data-cl")
        if(g) return v2;
        if(v2) d.Cr(v2).Ca(v).A("data-cl", v);
        return v;
    }
    case "chk":
    {
        di = d.Q("input");
        if(g) return di.checked ? 1 : 0;         
        di.checked = !!v; 
        return di.checked ? 1 : 0;
    }
    case "num-range":
    {
        st = 1;
        break;
    }
    case "num":
    {
        st = 1; 
        break;
    }
    case "txt-area":
    {
        st = 1; 
        break;
    }
    case "email":
    {
        st = 1; 
        break;
    }
    case "txt":
    {
        st = 1; 
        break;
    }
    case "com-radio":
    {
        if(g) return d.Q("input:checked").value;

        for(di of d.QA("fieldset > div > input"))
        {
            di.checked =  (v == di.value);  
        }
        return v;
    }
    case "com":
    {
        st = 1; 
        break;
    }
    case "datetime":
    {
        var d1 = d.Q(".inp-date"),
            d2 = d.Q(".inp-time"),
            v1, v2;

        if(g)
        {
            v1 = d1.value;
            v2 = d2.value;
            if(!v1) v1 = "2000-01-01";
            if(!v2) v2 = "00:00:00";
            v = v1 + " " + v2;
            if(v == "2000-01-01 00:00:00") v="";
        }
        else
        {
            [v1, v2] = DT_split(v); 
            v = v1 + " " + v2;
            d1.value = v1;
            d2.value = v2;
            di = d.Q(".inp-ro");
            if(di) di.value = v;
        }
        if(v && v.length != 19) 
        {
            console.error("Invalid datetime-value INP_val(): " + v);
        }            
        return v;
    }
    case "time":
    {
        st = 1; 
        break;
    }    
    case "date":
    {
        st = 1; 
        break;
    }    
    case "col":
    {
        st = 1; 
        break;
    }    
    default:
    {
        console.error("IP_val called with invalid y: '"+ y +"', inp: ", d);
    }
    }

    if(st) // standard
    {
        di = d.Q(".inp");
        if(!di)
        {
            console.error("Could not find the input for control in standard get/set INP_val(): ", d);
        }
        else
        {
            if(g) // get
            {        
                return di.value;            
            }
            else // set
            {
                di.value = v;
            }
            return v;
        }
    }
}


// ----------------------------------------------------------------
function INP_make(s, opt)   // for all inputs that are not chapter
{    
    var n, txt, di, dio, y, o, id_inp,
        v = (typeof s[3] == UN || !s[3]) ? "" : s[3],   // v here is the default-value (or class for button)
        req = 0, 
        ise = 0;    // is extra (for special inputs)


    if(v == "required") // Note: Instead of giving a default value you can give "required"
    {
        req=1;
        v = "";
    }            

    txt = LL(s[2]);

    switch(s[1])
    {
    case "dyn":
    {
        dio = fCE("div").A({'data-n': s[0], class:"control dyn", "data-y": "dyn"});
        dio.CE("label").Ct("hide", !v).CE("span").APs(txt);
        dio.CE("div").A({class: "dyn-body"});
        return dio;
    }
    case "lab":
    {
        dio = fCE("div").A({'data-n': s[0], class:"control lab", "data-y": "lab"});
        dio.CE("span").APs(txt);
        return dio;
    }
    case "but":
    {
        var sym = "";
        if(s[0].startsWith("but-")) sym = s[0].substr(4);  
        dio = BUT_make(s[0], txt, sym);
        if(v) dio.Ca(v).A("data-cl", v);        
        return dio; 
    }
    }

    id_inp = "inp-" + (_FORM_INP_cc++); 
    n = s[0];
    dio = fCE("div").A({'data-n': n, class: "control " + s[1] + " " + n, "data-y": s[1], "data-default": v});
    dio.CE("label").A({for: id_inp}).CE("span").APs(txt);

    o = {class: "inp", name: n, value: v, id: id_inp};

    if(req)
    {
        o.required = "required";
    }

    di = 0;
    ise = 0;

    switch(s[1])
    {
    case "chk":
    {
        y = "checkbox";
        break;
    }
    case "num-range":
        ise = 1;
        dio.Ca("num");
    case "num":
    {
        y = ise ? "range" : "number";

        if(typeof(s[4]) != UN) o.min = s[4];
        if(typeof(s[5]) != UN) o.max = s[5];
        if(typeof(s[6]) != UN) o.step = s[6];

        if(!ise) dio.Ca("num-regular");
        break;
    }
    case "email":
    case "txt-area":
    case "txt":
    {
        if(typeof(s[4]) != UN) o.maxlength = s[4];

        if(s[1] == "txt-area")
        {
            ise = 1;
            di = fCE("textarea").A(o);
        }
        else
        {
            dio.Ca("txt-single");
            if(s[1] == "txt")
            {
                y = "text";
            }
            else            
            {
                dio.Ca("email");
                y = "email";
            }
        }
        break;
    }
    case "com-radio":
        ise=1;
        dio.Ca("com");
    case "com":
    {
        var dop, cc_op = 1;
        if(ise)
        {            
            di = fCE("fieldset").A("data-n", o.name); 
            delete o.name;
            di.CE("legend").APs(LLS("choose-opt"));
        }
        else
        {
            di = fCE("select").A(o);
            if(s[4])
            {
                // can-zero 
                var nz = "";
                if(typeof s[4] == "string" && s[4].length) nz = LL(s[4]);
                di.CE("option").A({class: "option-zero", value: ""}).APs(nz);
            }
        }
        if(typeof opt != UN)
        {
            for(op of opt)
            {
                id_op = id_inp + "-op-" + (cc_op++);
                        
                if(ise)
                {   
                    var dchk = fCE("input").A({type: "radio", 
                                            value: op[0],
                                            class: "opt-" + op[0], 
                                            name: n,
                                            id: id_op});
                    dop = fCE("div").A({class: "div-option"}).AP(dchk);
                    dop.CE("label").A({for: id_op}).CE("span").APs(LL(op[1]));
                }
                else
                {
                    dop = fCE("option").A({id: id_op, value: op[0]}).APs(LL(op[1]));
                }         
                di.AP(dop);                    
            }
        } 
        break;
    }
    case "datetime":
    {
        di = fCE("fieldset");  
        var di1 = di.CE("input").A({"data-n": n, class: "inp inp-date", type: "date", min: "1900-01-01", max:"2100-12-31"}),
            di2 = di.CE("input").A({class: "inp inp-time", type: "time", step: "1", format: "HH:mm:ss"});
        di1.Ea("input", _INP_inp).Ea("change", INP_change);     
        di2.Ea("input", _INP_inp).Ea("change", INP_change);     
        break;

    }
    case "time":
    {
        y = "time";
        o.class += " inp-time";
        o.format = "HH:mm:ss"; 
        o.step = 1;
        break;
    }    
    case "date":
    {
        y = "date";
        o.class += " inp-date";
        o.min = "1900-01-01"; 
        o.max = "2100-12-31";
        break;
    }    
    case "col":
    {
        y = "color"; 
        break;
    }    
    default:
    {
        console.error("Invalid input-type: '"+ s[1] +"' given for inp: "+ s[0])
    }
    }

    if(!di)
    {
        o.type = y;
        di = fCE("input").A(o);

        if(y == "text" && typeof o.maxlength != UN && o.maxlength > 1 && o.maxlength < 16)
        {
            di.CSS("max-width", o.maxlength + "em");
        }
    }    
    dio.append(di);
    if(v) INP_val_DB(dio, v);
    di.Ea("input", _INP_inp).Ea("change", INP_change); 
    return dio;
}


// -------------------------------------------
function _INP_inp(e)   // UX Input, relevant only in some cases, like typing single characters
{
    _INP_inp_d = INP_d(this);
    if(_INP_inp_d)
    {
        _INP_inp_d.Ca("uxc");
        _INP_inp_cc = 2;  // count down at SEC()
    }  
}


// -------------------------------------------
function INP_change(e)   // UX Change
{
    var d = INP_d(this);
    if(_INP_inp_cc > 0)
    {
        if(d.isSameNode(_INP_inp_d)) _INP_inp_cc = 0; 
    }
    _INP_change(e, d);
}

// -------------------------------------------
function _INP_change(e, d)
{
    var d = INP_d(d),
        vdb = d.A("data-val-db"),
        uxc = d.Ch("uxc"),
        v = INP_val(d);

    d.Ct("uxc", (v != vdb));

    X_input(d, v);
}

// -------------------------------------------
function INP_tit(d, tit)
{    
    var d = INP_d(d),
        dl = d.Q("label");
    if(dl)
    {
        if(typeof tit == UN)
        {
            return dl.textContent;
        }
        else dl.EMP().CE("span").APs(tit);          
    }
    return tit;
}


// ----------------------------------------------------------------
function INP_validate(d)  
{
    // returns null if invalid, check it like this:  if(v === null)
    var di = d.Q("input,select,textarea");
    if(di)
    {
        if(!di.checkValidity()) 
        {
            err(LLS("err-inp") + ": " + INP_tit(d));
            return null;
        }    
    }
    return INP_val(d);
}


// -------------------------------------------
function FORM_validate(idf)
{
    // returns null if invalid, else JSON-object 

    var obj = [],
        dfo = _FORMa[idf].d, 
        df = dfo.Q("form"), 
        S = _FORMa[idf].S,
        id, dio;

    for(s of S)
    {
        switch(s[1])
        {
        case "chapter": 
        case "dyn":
        case "lab":
        case "but":
            // nothing to do here
            continue;
        }
        id = s[0];
        dio = df.Q("#" + idf + "-" + id);
        if(dio)
        {
            v = INP_validate(dio);
            if(v === null)
            {
                return null;        
            }
            obj.push([id, s[1], INP_tit(dio), v]);
        }
        else
        {
            console.error("Input not found at FORM_validate, given by id: " + id);
        }
    }
    return obj;
}


// ------------------------------------
function FORM_preview(obj)  // used to display form-data-preview, data is the result of FORM_validate()
{
  var ht = "", v, inp;
  for(inp of obj)
  {
    v = "" + inp[3];
    v = v.trim();
    if(v.length)
    {
      ht += "<div class='inp " + inp[0] + " " + inp[1] + "' style='margin-top:0.3em;'><label style='display:inline-block;min-width:10em;'><b>" + inp[2] + ": </b></label><span class='v'>" + v + "</span></div>";
    }
  }
  return ht;
}


// ------------------------------------
function FORM_clean(idf)
{
    var dform = _FORMa[idf].d.Q("form"),
        dinp, tag, def, dic;
    
    for(dinp of dform)
    {
        def = "";
        if(dinp.Ah("data-default")) def = dinp.A("data-default");
        tag = dinp.TAG();
        switch(tag)
        {
        case "input":
        case "select":
        case "textarea":
            dic = dinp.closest(".control");
            if(dic) INP_val(dic, def);
            else dinp.value = def;   // This is a fallback if input is not one inside div.control
            break;
        }
    }
}

// ---------------------------------------------------------------------------
function _FORM_cap(e)  // Reload near captcha 
{ 
    var df = this.closest("form"),
        url = df.A("data-url"),
        ns =  df.parentNode.id;
    df.Q(".captcha-image").src = url + "/_3p/securimage/securimage_show.php?namespace=" + ns + "&id=" + Math.random();
    if(e)e.preventDefault();
}


// ---------------------------------------------------------------------------
function _FORM_cap_err(e)   // Captcha Image could not be loaded
{
    console.error("Captcha not loaded!");

    err(LLS("err-cap-off"));

    var dp = this.parentNode,
        d = dp.Q(".cap-off"),
        ad = dp.children, dsym;
    if(!d)
    { 
        for(d of ad) d.Ca("hide");
        d = fCE("div").A({class: "cap-off"});
        this.BEF(d);
        d.CE("span").A({class: "red"}).APs(LLS("err-cap-off"));
        dsym = SYM_make("reload", 1).Ca("cursor-pointer");
        d.AP(dsym);
        dsym.Ea("click", _FORM_cap)
        dp.closest("form")["captcha-code"].Ca("hide"); 
    }
}


// ---------------------------------------------------------------------------
function _FORM_cap_chk(e)  // at onload-event check further
{
    if(!this.complete || this.naturalWidth < 64) 
    {
        _FORM_cap_err.call(this, e);
    }
    else
    {
        var dp = this.parentNode,
            d = dp.Q(".cap-off");
        if(d)
        {
            d.REM();
            for(d of dp.children) d.Cr("hide");
            dp.closest("form")["captcha-code"].Cr("hide");   // TOOO: Seems Safari at Ihone des not like this statement ?!
        } 
    }
}


// ---------------------------------------------------------------------------
function _FORM_submit(e)
{
    e.preventDefault();
    var df = this,
        url = df.A("data-url"),
        dfo = this.closest(".form"),
        idf = dfo.id; 
    FORM_send(idf, url); 
    e.stopPropagation();
    return false;
}

// ---------------------------------------------------------------------------
function FORM_send(idf, url) 
{
    var fd = FORM_validate(idf);
    if(fd && fd.length)
    {
        if(typeof url == UN || !url.startsWith("http")) url = "https://exa.run/ConfiMailPHP";

        var dform = _FORMa[idf].d.Q("form"), 
            dcap = dform['captcha-code'],
            cap = dcap.value;

        if(!dcap.checkValidity() || cap.length != 1) return err(LLS("err-captcha"));

        ConfiMailPHP(url, cap, JSON.stringify(fd), idf).then((ok) => 
        {
            if(ok == 1)
            {
                console.log("MAIL sending done.");

                FORM_clean(idf);
                msg(LLS("msg-send-ok"));
                setTimeout(() => { msg(LLS("thanks")); }, 2000);
                _FORM_cap(0); // reload captcha
            }
            else
            {
                console.log("Wrong captcha input!");  
                err(LLS("err-captcha"));
            }

        }, (ex) =>
        {
            console.error("Mail sending failed. Check the system! Info: ", ex);
            err(LLS("err-mail-sys"));
        });
    }
}


// ---------------------------------------------------------------------------
function ConfiMailPHP(url, cap, msg, ns) 
{ 
    return new Promise(function(ok, no)
    {
        var r = rint(),
            ids = "cs98",
            ds = fE(ids);    
        if(ds) ds.REM();
        ds = _b.CE("script");
        ds.id = ids;
        ds.onload = () => 
        { 
            if(typeof _MAIL_err_cap == UN) no("_MX1");
            if(_MAIL_err_cap == 0)
            {
                _MAIL_send(msg, r).then((b) =>
                {
                    if(b == 1) ok(1);
                    else no("_MX2");
                }); 
            }
            else
            {
                if(_MAIL_err_cap == 1) ok(0);
                else no("_MX3");
            }
        }
        ds.A("src", url + "/mail_script.php?r=" + r + "&c=" + cap + "&namespace=" + ns);
    });
}


// ---------------------------------------------------------------------------
// INIT-code, CSS-linking, and language-extension

_h.CE("link").A({rel: "stylesheet", type: "text/css", href: "../css_lazy/form.css"});

// -----------------------

Object.assign(_LLS, {
"msg-send-ok":          "Ok, your message was sent.",
"err-captcha":          "Wrong code. Please try again!",
"err-mail-sys":         "Sorry! Our mail system failed. Please use regular email.",
"err-inp":              "Input Error",
"err-cap-off":          "Sending not possible! Please go online."
});

Object.assign(_LLD.de, {
"Ok, your message was sent.":           "Ok, Nachricht wurde versandt.",
"Wrong code. Please try again!":        "Falscher Code. Bitte nochmal probieren.",
"Sorry! Our mail system failed. Please use regular email.":"Zur Zeit leider System-Probleme. Bitte ein Email schreiben.",
"Your Contact Info":                    "Wie k&ouml;nnen wir Sie kontaktieren",
"Input Error":                          "Eingabefehler",
"Sending not possible! Please go online.": "Senden nicht möglich! Bitte online gehen.",
});
    
Object.assign(_LLD.es, {
"Ok, your message was sent.":           "Ok, su mensaje fue enviado.",
"Wrong code. Please try again!":        "Código incorrecto. Inténtelo de nuevo.",
"Sorry! Our mail system failed. Please use regular email.":"Nuestro sistema falló. Utilice el correo electrónico.",
"Your Contact Info":                    "Su información de contacto",
"Input Error":                          "Error",
"Sending not possible! Please go online.": "No es posible el envío. Por favor, vaya en línea.",
});
    
    




