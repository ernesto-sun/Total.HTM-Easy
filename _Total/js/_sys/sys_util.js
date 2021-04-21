
/* All kind of independent functions used at sys.js and other files, 
but not yet needed at init.js */


// ----------------------------------------------------------------
function SYM_make(n, isb)  // isb : is-button
{
    if(n.startsWith("fa-"))
    {
        return fCE("span").A({class: "sym fa " + n});
    }
    else
    {
        if(typeof isb == UN) isb=0;
        var src = "../img/" + (isb ? "_but/but_" : "_sym/sym_")  + n.replace(/\-/, "_") + ".png";
        return fCE("img").A({class: "sym", src: src, alt: n});
    }
}

// ----------------------------------------------------------------
function BUT_make(n, txt, sym, f)
{
    if(typeof f == UN) f = BUT_click;
    var d = fCE("button").A({"data-n": n, class: "control but", "data-y": "but"}).Ea("click", f);

    if(typeof sym == "string" && sym.length > 0)
    {        
        d.AP(SYM_make(sym, 1));
    }

    d.CE("span").APs(txt);
    return d;
}


// ----------------------------------------------------
function LOAD(src)
{
  return new Promise(function(ok, no)
  {
    try
    {
      var xhr = new XMLHttpRequest();
      xhr.addEventListener("load", function()
      {
        ok(this.responseText);
      });

      xhr.onerror = no;

      xhr.open("POST", src);
      xhr.send();       
    }
    catch(ex)
    {
      console.error("Loading script: " +  src + " failed!", ex);
      no(ex);
    }
	});
}


// ---------------------------------
function rint()  // random int > 0
{
  return fR(Math.random() * 2000000000) + 1;
}


// ----------------------------------------------
function hashi(str)   // returns a primitive, quite unique (?!), int-hash for string-identification
{
  var hash = 0;
  if (str.length < 1) return 0;

  for (var i = 0; i < str.length; i++) 
  {
      var char = str.charCodeAt(i);
      hash = ((hash << 5) - hash) + char;
      hash = hash & hash; // Convert to 32bit integer
  }
  return hash;
}


// ---------------------------------------------------------
function DT_split(v)
{
  if(!v) return ["2000-01-01", "00:00:00"];
  var v1 = v.substr(0, 10),
      v2 = v.substr(11);
  if(v2.length < 1) v2 = "00:00:00";
  else if(v2.length < 6) v2 += ":00";
  return [v1, v2];
}


// ---------------------------------------------------------
function tz()  // Returns effective timezone-diff from UTC taking summertime in account
{
  var v = new Date().getTimezoneOffset();  //  If your time zone is GMT+2, -120 will be returned.
  if(isSummertime()) v += 60;
  return v;
}


// ---------------------------------------------------------
function hasSummertime()
{
  var y = new Date().getFullYear(),
      d1 = new Date(y, 0, 1),
      d2 = new Date(y, 6, 1);
  return ((d1.getTimezoneOffset() - d2.getTimezoneOffset()) == 60);
}


// ---------------------------------------------------------
function isSummertime(date)
{
  if(typeof date==UN) date = new Date();
  var y = date.getFullYear(),
      d1 = new Date(y, 0, 1);
  return ((d1.getTimezoneOffset() - date.getTimezoneOffset()) == 60);
}


// ----------------------------------------------------------------------
function monday(date)
{
  var day = date.getDay() || 7;
  if( day !== 1 ) date.setHours(-24 * (day - 1));
  return date;
}


// ----------------------------------------------------------------------
function DT_diff_txt(sec)  
{
  var min = fR(sec / 60), 
    hr = fR(min / 60),
    txt="";

  if(sec > 45)
  {
    if(hr > 1)
    {
      if(hr > 66) 
      {
        var days = fR(hr / 24); 
        if(days > 34)
        {
          var month = fR(days / 30);
          if(month > 36)
          {
            txt = fR(days / 365) + " " + LLS("yn");
          }
          else
          {
            if(month > 3)
            {
              txt = month + " " + LLS("monn");
            }
            else
            {
              txt = fR(days / 7) + " " + LLS("wn");
            }
          }
        }
        else
        {
          txt = days + " " + LLS("dayn");
        }
      }
      else txt = hr + " " + LLS("hn");
    }
    else
    {
     if(min > 111) txt = "~2 " + LLS("hn");
     else if(min > 79) txt = "~1.5 " + LLS("hn");
     else if(min == 60) txt = "1 " + LLS("h1");
     else if(min > 55) txt = "~1 " + LLS("h1");
     else if(min == 1) txt = "~1 " + LLS("min1");
     else txt = min + " " + LLS("minn");
    }
  }
  else if(sec > 0)
  {
    // in seconds
    if(sec == 1) txt = "1 " + LLS("sec1");
    else txt = sec + " " + LLS("secn");
  }
  return txt;
}


// ----------------------------------------------------------------------
function COL_hex(v)  // ensures the color to be in the format [r,g,b]
{
  var y=typeof v;
  if(y == UN) return [0,0,0];
  else if(y=="object")
  {
    // assume its a color, TODO: more checks, converts ?
    return v;
  }
  else if(y=="string")
  {
    var r=0,g=0,b=0;
    v=v.trim();
    if(v.startsWith("#"))
    {
      r=parseInt(v.substr(1,2),16);
      g=parseInt(v.substr(3,2),16);
      b=parseInt(v.substr(5,2),16);
    }
    else if(v.startsWith("rgb"))
    {
      var p = v.indexOf("(");
          va = v.substr(p+1).split(",",3); 
      r=parseInt(va[0]);
      g=parseInt(va[1]);
      b=parseInt(va[2]);     
    }
    else
    {
      // convert using css, assume color name
      d_1em.CSS("background",v);
      v = d_1em.CSS("background-color");
      if(v.startsWith("rgb")) return COL_hex(v);
      else return [0,0,0];
    }
    return [r,g,b];
  }
  else
  {
    return [0,0,0];
  }
}


// ----------------------------------------------
function COL_str(v)
{
    var v = COL_hex(v),
        r = v[0].toString(16),
        g = v[1].toString(16),
        b = v[2].toString(16),
        vs =  "#" + (v[0] < 16 ? "0" : "")  + r +
                    (v[1] < 16 ? "0" : "")  + g +
                    (v[2] < 16 ? "0" : "")  + b;
    return vs;
}

// ---------------------------------------------------
function POST(url, data, dj)  // dj: data as json, by default false
{
  return new Promise(function(ok, no)
  {
    var req = new XMLHttpRequest();
    if(typeof dj != UN && dj) req.responseType = 'json';
    req.open("POST", url, true);  

    req.onload = function() 
    {
      if (this.status >= 200 && this.status < 400) 
      {
        ok(this.response);
      } 
      else 
      {  
        no("ERROR: POST failed: " + url)
      }
    };
          
    req.onerror = function() 
    {
      no("AJAX Network failed: " + url)
    }
    
    if(typeof data != UN)
    {
      if(typeof data == "object")
      {
        req.setRequestHeader("Content-type", "application/json");
        data = JSON.stringify(data);
      } 
    }

    req.send(data);    
  });
}



// ----------------------------------------------------
function putNextTo(d, dref, adaptWidth)
{
  var wd = d.W(),
      hd = d.H(),
      left_ref = dref.offsetLeft,
      top_ref = dref.offsetTop,
      bottom_ref = top_ref + dref.H(),
      right_ref = left_ref + dref.W(),
      adaptWidth = (typeof adaptWidth != UN ? !!adaptWidth : 0),
      put_left = 0,
      left_new = 0,
      top_new = 0,
      scrollTop = _win.scrollY,
      scrollLeft = _win.scrollX,
      space_top = bottom_ref - scrollTop,
      space_below = _hs - (top_ref - scrollTop);
      wdoc = _doc.offsetWidth;

  // ------------- check horizontal

  if((right_ref - scrollLeft) + wd <= wdoc)
  {
    // enough space to the right
  }
  else
  {
    if((left_ref - scrollLeft) >= wd)
    {
      // enough space to the left
      put_left = 1;
    }
    else
    {
      // NOT enough space to the left nor to the right

      var width_new = 0;
      if((left_ref - scrollLeft) > (wdoc - (right_ref - scrollLeft)))
      {
        // more space available to the left
        width_new = (left_ref - scrollLeft);
        put_left = 1;
      }
      else
      {
        // more space available to the right
        width_new = wdoc - (right_ref - scrollLeft);
      }

      if(adaptWidth && width_new)
      {
         d.CSS("width", fR(width_new) + "px");
         wd = width_new;
      }
    }
  }

  if(put_left) left_new = (left_ref - scrollLeft) - wd;
  else left_new = (right_ref - scrollLeft);

  // ------------- now check vertical

  if(space_below >=  hd)
  {
    // enough space below
    top_new = top_ref;
  }
  else
  {
    if (space_top >= hd)
    {
      // goes into upside
      top_new = top_ref - (hd - space_below);
    }
    else if(_hs >= hd)
    {
      // just goes into screen
      top_new = scrollTop + (_hs - hd);
    }
    else
    {
      // too much height anyway
      top_new = scrollTop;
    }
  }

  d.CSS({top: fR(top_new) + "px",
         left: fR(left_new) + "px"});
}


// ----------------------------------------------------
function fullscreen_on()
{
  _b.Ca("fullscreen");
  try 
  {
    if (_doc.requestFullscreen) _doc.requestFullscreen();
    else if (_doc.webkitRequestFullscreen) _doc.webkitRequestFullscreen();
    else if (_doc.mozRequestFullScreen) _doc.mozRequestFullScreen();
    else if (_doc.msRequestFullscreen) _doc.msRequestFullscreen();
  }
  catch (ex) 
  {
    console.warn("_doc.requestFullscreen() failed!", ex);  
  }
}


// ----------------------------------------------------
function fullscreen_off()
{
  _b.Cr("fullscreen");
  try 
  {
    if (_d.exitFullscreen) _d.exitFullscreen();
    else if (_d.mozCancelFullScreen) _d.mozCancelFullScreen();
    else if (_d.webkitExitFullscreen) _d.webkitExitFullscreen();
    else if (_d.msExitFullscreen) _d.msExitFullscreen();
  }
  catch (ex) 
  {
    console.warn("_d.exitFullscreen() failed!", ex);  
  }
}


// ------------------------------------------------------
function print(html)
{
  try
  {
    if(typeof _print != UN && _print != null && _print && !_print.closed) _print.close();
  }
  catch (ex)
  {
    console.error("Closing Print Window failed! ", ex);
  }

  try 
  {
    _print = _win.open();
    _print._doc.write(html);
    setTimeout(function() 
    { 
      _print.print(); 
    }, 500);  
      
  } 
  catch (ex)
  {
    console.error("Printing failed! ", ex);
  }
}

