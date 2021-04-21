
// ---------------------------------------------------------------------------
// This code is part of Total.HTM Easy. See: http://exa.run/Total.HTM 
// ---------------------------------------------------------------------------

// -------------------------------------------------
async function HASH(s)
{
  const s8 = new TextEncoder().encode(s);                           // encode as (utf-8) Uint8Array
  const hbuf = await crypto.subtle.digest('SHA-256', s8);           // hash the message
  const harr = Array.from(new Uint8Array(hbuf));                    // convert buffer to byte array
  return harr.map(b => b.toString(16).padStart(2, '0')).join('');   // convert bytes to hex string
}


// -------------------------------------------------
function dl8()
{
  let sec0 = rint(), sec1, sec2;

  var cal = fI(_LS.getItem("chk-auto-login"));
  if(typeof cal != UN && cal)
  {
    var al = _LS.getItem("al"),
        alp = _LS.getItem("al-p"),
        als = _LS.getItem("al-s");

    if(typeof al != "string" || al.length != 64) al = "";
    if(typeof alp != "string" || alp.length != 64) alp = "";
    if(typeof als != "string" || als.length != 64) als = "";

    _LS.removeItem("al");
    _LS.removeItem("al-p");
    _LS.removeItem("al-s");

    if(al.length && alp.length && als.length)
    {
      // try direct login
      _LAZY(_xr_ut + "_login/get_login_js.php?al=" + als, () =>
      {     
        if(typeof HMAC != UN)
        {
          HMAC(alp, al).then((hm) =>
          {
            POST(_xr_ut + "_login/checkin_al.php", 
            {
              m: (_iT ? "total" : "app"),
              x: hm
            }, 1).then((denc) =>
            {
              DECRYPT(alp, JSON.parse(denc.x)).then((arr) =>
              {             
                try
                {
                  var d = JSON.parse(arr), ok = 0;

                  if(typeof(d["n"]) != "undefined" &&
                    typeof(d["p"]) != "undefined" &&
                    typeof(d["cc"]) != "undefined" && 
                    typeof(d["uid"]) != "undefined")
                  {
                    uid = d["uid"];
                    login_ok(d);
                    _auto_login = 1;
                    ok = 1;
                  }
                }
                catch(ex)
                {
                  console.error("Seemingly ok Auto-login failed with result data!", ex);
                }

                if(!ok)
                {
                  console.error("Seemingly ok auto-login failed!");
                  _LS.setItem("chk-auto-login", 0); 
                  dl8();
                }
              });
            },
            (ex) =>
            {
              console.error("Seemingly ok auto-login failed of network failure!", ex);
              _LS.setItem("chk-auto-login", 0); 
              dl8();
            });
          });
        }
        else
        {
          console.error("Auto-login failed from Server-Side!");
          _LS.setItem("chk-auto-login", 0); 
          dl8();
        }
      });
      return;
    }
  }   // try auto-login end


  POST(_xr_ut + "_login/checkin.php", {a: sec0, m: (_iT ? "total" : "app")}).then((d) =>
  {
    sec1 = d;
    HASH(sec1).then((h) => 
    {
      sec2 = h;
      _LAZY(_xr_ut + "_login/get_login_js.php?a=" + sec0 + "&b=" + sec2, () =>
      {
        var dlogin = fQ("#login-dialog-here:not(.hide)"),
            ht="<div id='div-login'><h2>" + LLS("login-title") + "</h2><div id='login-form'></div></div>";
        
        if(dlogin)
        {
          dlogin.APs(ht);
        }
        else
        {              
          dlogin = fE("login-dialog-here");
          if(dlogin) dlogin.EMP();
          popup(ht, 0, 1, 1);
        }

        setTimeout(() => { IDLE(() => { doLogin(sec0, sec1, sec2);})}, 400);  // time do load inline js         
      });
    });
  });
}
