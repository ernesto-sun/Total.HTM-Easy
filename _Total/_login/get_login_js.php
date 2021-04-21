<?php

// ---------------------------------------------------------------------------
// This code is part of Total.HTM Easy. See: http://exa.run/Total.HTM 
// ---------------------------------------------------------------------------

ob_start();

header('Content-type: text/javascript');
error_reporting(E_ALL | E_STRICT); 
define('MODE_STRICT', 1);
set_time_limit(1);  // can only run 1 seconds. Thats much anyway.

$GLOBALS['debug'] = 0;

ini_set('display_errors', $GLOBALS['debug']);   

// ------------------------------------------------------
function MS()
{
	return intval(microtime(true) * 1000);	
}

$GLOBALS['sts'] = MS();

// ------------------------------------------------------
function TIMESTAMP()
{
	return date('Y-m-d H:i:s').'.'.sprintf('%03d', (MS() % 1000));	
}

// --------------------------------------------------------------
function AGENT_INFO()
{
    $info = array();
    $info['ip'] = $_SERVER['REMOTE_ADDR'];
    $info['host'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $info['agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '?';
    $info['lang'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '?';  // gives info about the language of the user
    return $info; 
}

// --------------------------------------------------------------
function AGENT_INFO_STR()
{
    return var_export(AGENT_INFO(), true);
}

// --------------
function err($msg)
{
    @error_log(TIMESTAMP().': ERROR: '.$msg);
    @error_log('AGENT: '.AGENT_INFO_STR());
    $ts = MS() - $GLOBALS['sts'];
    @error_log('Script Runtime: '.$ts.'ms');
    @session_destroy();
    usleep(rand(100000, 300000));  // thats between 100ms and 300ms 
    ob_end_clean();
    if($GLOBALS['debug']) echo 'ERROR: ', $msg;
    die();
}


$ok_come_from_api = 1;
require('../config_dont_touch.php');

$GLOBALS['fp_log'] = $GLOBALS['config']['dir_sec_l'].'log_'.date('ymd').'_get_login_js_php.y7';

ini_set('log_errors', 1);
ini_set('error_log', $fp_log);

//-------------------------------------------------
// ---------------------------- from here on logging shall work
//-------------------------------------------------


$ok = 0;

if(isset($_GET['a']) && isset($_GET['b']))
{
  $a = (int)($_GET['a']);
  $b = ''.$_GET['b'];  // this must be the HASH(SHA-256) of SESSION[b]

  if(strlen($b) > 64) err("b64");

  session_start();

  if(isset($_SESSION['sec']) && is_array($_SESSION['sec']))
  {
    if(isset($_SESSION['sec']['a']) && isset($_SESSION['sec']['b']))
    {
      $b2 = hash('sha256', $_SESSION['sec']['b'], false);

      if($a == $_SESSION['sec']['a'] && $b == $b2)
      {
          $_SESSION['sec']['c'] = rand(1,10000000);
          $_SESSION['sec']['d'] = rand(1,10000000);
          $_SESSION['sec']['e'] = rand(1,10000000);
          $ok = 1;
      }
      else err("HASH-Check!");
    }
    else err("Session_isset");
  }
  else err("Session_array_isset");

  if($ok)
  {
    if($_SESSION['sec']["mode"] == "ro")
    {
      echo $_SESSION['sec']['c'];
      die();
    } 
    else
    {   
      $u = "u".$_SESSION['sec']['d'];
      $p = "p".$_SESSION['sec']['e'];
  
      $_SESSION['sec']['uts'] = gmdate("ymd");
  
      echo "var sec_c=", $_SESSION['sec']['c'], ", f", $_SESSION['sec']['c'], "=\"<div class='control txt'><label for='{$u}'>\" + LLS('login-un') + \"</label><input class='inp' type='text' name='{$u}' required='required' autocomplete='username'/></div><div class='control txt'><label for='{$p}'>\" + LLS('login-pwd') + \"</label><input class='inp' type='password' name='{$p}' required='required' autocomplete='current-password'/></div>\";";
  
    }
  }
  else
  {
    err("Login failed by !ok");
  }
}
else if(isset($_GET['al']))
{
  if(strlen($_GET['al']) > 64) err('al64');
  // Login for Auto-Login
  session_start();

  if(!isset($_SESSION['al-s']) || strlen($_SESSION['al-s']) < 20) err("{\"err\":\"als1\"}");
  if($_GET['al'] != $_SESSION['al-s']) err("{\"err\":\"als2\"}");

}
else err("Param_isset");

// -----------------------------------------------------------------------------------
?>

var uid = "", 
    un = "", 
    uy = 0,   // TODO: Closure to encapsulate un and uy 
    _PP, 
    _PD, 
    _PE, 
    uts = "", 
    _AUTH_CC = 0, 
    _AUTH_TOK = 0, 
    _AUTH_V = 0, 
    debug = 0,
    enc = new TextEncoder("utf-8"),
    dec = new TextDecoder("utf-8");


// --------------------------------------
function BUF2BASE64(buf)   // TODO: Improve performance or find something more native 
{
  let r = "", i,
      by = new Uint8Array(buf),
      l = by.byteLength;
  for (i = 0; i < l; i++) r += String.fromCharCode(by[i]);
  return btoa(r);
}


// --------------------------------------
function BASE642BUF(b64) // TODO: Improve performance or find something more native
{
  let bs = atob(b64),
      l = bs.length,
      by = new Uint8Array(l), 
      i;
  for (i = 0; i < l; i++) by[i] = bs.charCodeAt(i);
  return by.buffer;
}


// --------------------------------------
function BUF2HEX(buf) // TODO: Improve performance or find something more native
{
  return [...new Uint8Array (buf)].map (b => b.toString (16).padStart (2, "0")).join("");
}

// --------------------------------------
function HEX2BUF(hex) // TODO: Improve performance or find something more native
{
  return new Uint8Array(hex.match(/.{1,2}/g).map(b => parseInt(b, 16)));
}


// --------------------------------------
function AUTH_TS(obj)
{
  var d = new Date(),
      dy = ("" + d.getUTCFullYear()).substr(2),
      dm = ("" + (d.getUTCMonth() + 1)).padStart(2, 0),
      dd = ("" + d.getUTCDate()).padStart(2, 0);
  return dy + dm + dd;
}


// --------------------------------------
async function AUTH_PARAM(obj)
{
  var r0 = rint(),
      r1 = rint(),
      f = await _PP;

  var m_test = "" + r0 + "-" + _AUTH_V + "-" + uts + "-" + uid + "-" + r1;

  var hm = await HASH(m_test);

  if(typeof obj == UN) obj = {};
  obj.r0 = r0;
  obj.r1 = r1;
  obj.a = uid;
  obj.b = await f(hm);

  return obj;
}


// ---------------------------------------------------
function POSTA(url, data, dj)  // dj: data as json, by default false
{
  // TODO: Is there a better way than to wrap a Promise in a Promise?!
  return new Promise((ok, no) =>
  {
    AUTH_PARAM(data).then((d2) =>
    {
      POST(url, d2, dj).then((d) =>
      {
        ok(d);
      },
      (ex) =>
      {
        no("Auth-POST failed: " + ex); 
      });
    },
    (ex) =>
    {
      no("Auth-PARAM failed: " + ex); 
    });  
  });
}




// --------------------------------------
async function HMAC_key(p)
{
  return await crypto.subtle.importKey(
    "raw", // raw : Uint8Array
    enc.encode(p),
    { 
        name: "HMAC",
        hash: {name: "SHA-256"}
    },
    false, // export = false
    ["sign", "verify"]); // what this key can do
}

// --------------------------------------
async function HMAC_k(k, msg)
{
  return BUF2BASE64(await crypto.subtle.sign("HMAC", k, enc.encode(msg)));
}

// --------------------------------------
async function HMAC(p, msg)
{
  return await HMAC_k(await HMAC_key(p), msg);
}

// --------------------------------------
async function HMAC_verfiy_k(k, msg, sig)
{
  return await crypto.subtle.verfify("HMAC", 
                k, 
                enc.encode(sig), 
                enc.encode(msg));
}


// --------------------------------------
async function HMAC_verfiy(p, msg, sig)     // was done before by comparision. I expect (hope) verify is faster, than sign and ==
{
  return await HMAC_verfiy_k(await HMAC_key(p), msg, sig);
}


// --------------------------------------
async function ENC_key(p)
{
  return await crypto.subtle.importKey(
    "raw",
    enc.encode(p),
    "PBKDF2",
    false,
    ["deriveBits", "deriveKey"]);
}

// --------------------------------------
async function DECRYPT(p, oj)
{
  return await DECRYPT_k(await ENC_key(p), oj)
}

// --------------------------------------
async function DECRYPT_k(k0, oj)
{
  var ey = BASE642BUF(oj.c),
      st = HEX2BUF(oj.s),
      iv = HEX2BUF(oj.i); 
  
  const k1 = await crypto.subtle.deriveKey(
    {
      name: "PBKDF2",
      salt: st,
      iterations: 999,
      hash: "SHA-256"
    },
    k0,
    { 
      name: "AES-CBC", 
      length: 256
    },
    true,
    ["decrypt"]);

  const dy = await crypto.subtle.decrypt(
    {
        name: "AES-CBC",
        iv: iv,
    },
    k1, //from generateKey or importKey above
    ey); //ArrayBuffer of the data

  return dec.decode(dy);  

  /*
    var oj = JSON.parse(msg),
        ey = oj.c,
        st = CryptoJS.enc.Hex.parse(oj.s),
        iv = CryptoJS.enc.Hex.parse(oj.i),
        key = CryptoJS.PBKDF2(pwd, st, {hasher:CryptoJS.algo.SHA256,keySize:8,iterations:999}),
        dy = CryptoJS.AES.decrypt(ey, key, { iv: iv});
    return dy.toString(CryptoJS.enc.Utf8);
  */
}

// --------------------------------------
async function ENCRYPT(pwd, msg)
{
  return await ENCRYPT_k(await ENC_key(pwd), msg)
}


// --------------------------------------
async function ENCRYPT_k(k0, msg)
{
  var st = crypto.getRandomValues(new Uint8Array(256)),
      iv = crypto.getRandomValues(new Uint8Array(16)); 

  const k1 = await crypto.subtle.deriveKey(
    {
      name: "PBKDF2",
      salt: st,
      iterations: 999,
      hash: "SHA-256"
    },
    k0,
    {
      name: "AES-CBC", 
      length: 256
    },
    true,
    [ "encrypt"]);
    
  
  const c = await crypto.subtle.encrypt(
      {
          name: "AES-CBC",
          //Don't re-use initialization vectors! // Always generate a new iv every time your encrypt!
          iv: iv,
      },
      k1, // from generateKey or importKey above
      enc.encode(msg));  // ArrayBuffer of data you want to encrypt
 
    // return {c: c, s: st, i: iv};
    return {c: BUF2BASE64(c), s: BUF2HEX(st), i: BUF2HEX(iv)};

  /*

  var st = CryptoJS.lib.WordArray.random(256),
      iv = CryptoJS.lib.WordArray.random(16),
      key = CryptoJS.PBKDF2(pwd, st, {hasher:CryptoJS.algo.SHA256,keySize:8,iterations:999}),
      ey = CryptoJS.AES.encrypt(msg, key, {iv: iv}),
      data = { c : CryptoJS.enc.Base64.stringify(ey.ciphertext),
               s : CryptoJS.enc.Hex.stringify(st),
               i : CryptoJS.enc.Hex.stringify(iv) };
  return JSON.stringify(data);
  */
}

// idea: create secret salt and iv at server and send it here encrypted and reuse it for faster encryption (?!?!)
// TODO: Need an encryption-expert!



// ----------------------------------------
async function pwd(v)
{
  const _p = await HMAC_key(v);
  return function(msg) { return HMAC_k(_p, msg); };
}

// ----------------------------------------
async function pwd_decrypt(v)
{
  const _p = await ENC_key(v);
  return function(msg) { return DECRYPT_k(_p, msg);};
}

// ----------------------------------------
async function pwd_encrypt(v)
{
  const _p = await ENC_key(v);
  return function(msg) { return ENCRYPT_k(_p, msg);};
}


// ---------------------------------------------------------------
function doLogin(a, b0, b)
{
  _uok=0;

  var fv = "f" + sec_c,
      html = "<form>" + window[fv], 
      df = fE("login-form");

  if(_auto_login)
  {
    html +="<div id='div-chk-login-auto' class='div-input'><input id='chk-login-auto' type='checkbox'";
    var al = fI(_LS.getItem("chk-auto-login"));
    if (typeof al != UN && al)
    {
      html +=" checked='checked' ";
    }
    html +=" /><label for='chk-login-auto'><span>" + LLS('login-auto') + "</span></label></div>";
  }

  html += "<button type='submit'>" + LLS('login-but') + "</button></form>",


  // idea: TODO: make many random inputs and hide the real ones as good as possible, Expert?!
  df.APs(html);
  df.Q("input").focus();

  async function dl9()
  {

    let df = fE("login-form"),
        u0 = await HASH(df.Q("input").value.toLowerCase()),
        p0 = await HASH(df.Q("input[type=\"password\"]").value),
        al = 0;

    // idea: use extra-clojures for u0 and p0

    if(_auto_login)
    {
      al = fE("chk-login-auto").checked ? 1 : 0;
      _LS.setItem("chk-auto-login", al);
    }


    loading_start();  
    var d = fE("div-script-login");
    if(d) d.REM();
    lightbox_clean(1);
    popup_clean(1);
    d = fE("login-dialog-here");
    if(d) d.EMP();

    const rand1 = rint(),
         msg0 = await HASH("" + rand1 + a + b0 + sec_c),
         v = await ENCRYPT(msg0, u0);

    let data0 = {m: "I_confirm_to_be_an_authentic_person_with_respect_and_dignity",
         a: a,
         b: b,
         r: rand1};

        // idea: make all login-AJAX-call synchronous so that less happens in between, timeout, exclusive, ...

    data0[fv] = v;

    let data1 = await POST(_xr_ut+"_login/login.php", data0, 1);

    if(typeof(data1.uid) == UN) return;

    uid = data1.uid;

    let rand2 = rint(),
        msg1 = await HASH("" + rand2 + u0 + msg0),
        hash64 = await HMAC(p0, msg1),
        data2 = {m: "My_privacy_is_my_natural_right",
        a: a,
        b: b,
        r: rand2,
        u: uid,
        al: al,
        smallscreen: (_b.Ch("screen-small") ? 1 : 0),
        portrait: (_b.Ch("portrait") ? 1 : 0)};
       
        /*
                  "msg1":msg1,
                "msg0":msg0,
              "raw1":""+rand2+u0+msg0};*/
    
    u0 = "";
    data2[fv] = hash64;

    const data3 = await POST(_xr_ut + "_login/login.php", data2, 1);
 
    if(typeof(data3.x) == UN) return;
    
    const oj = JSON.parse(data3.x, true);
    const arr_test = await DECRYPT(p0, oj);
  
    p0 = "";
    var data4 = JSON.parse(arr_test, true);

    if(typeof data4.n == UN || typeof data4.p == UN || typeof data4.cc == UN) return;

    login_ok(data4);
    return 1;
  }

  df.Q("button").Ea("click", function(e)
  {
    e.preventDefault();

    if(!_iT && !e.isTrusted)
    {
      console.error("click");
      return;
    }
 
    dl9().then((ok) => 
    {
      if(ok) 
      {
        console.log("Login ok!");
      }
      else
      {
        console.log("Login failed!");
        login_failed();
      } 
    },
    (err) =>
    {
      console.error("Login failed with exception!", err);
      login_failed();
    });  
  });

}

// ------------------------------------------------------------------
function login_ok(d)
{
  _PP = pwd(d.p);  // idea: not use p0 alone but add salt to it (as well at php)
  _PD = pwd_decrypt(d.p);
  _PE = pwd_encrypt(d.p);
  p_test = "";

  _AUTH_CC = fI(d.cc);
  _AUTH_TOK = fI(d.tok);
  _AUTH_V =  fI(d.v);

  if(typeof d.al == "string" && typeof d["al-p"] == "string")
  {
    if(d.al.length > 20 && d["al-p"].length > 20)
    {
      _LS.setItem("al", d.al);
      _LS.setItem("al-p", d["al-p"]);
      _LS.setItem("al-s", d["al-s"]);
    }
  }

  uts = d.uts;

  un = d.n; // idea: use extra-clojure for un as well
  uy = fI(d.y);

  _uok = 1;
  _uok0 = 1; // prepare for admin-interface in SEC asynch

  _b.Ca("login-ok");
}



// ---------------------------------------
function login_failed()
{
  _uok0 = _uok = 0; 

  if(debug)
  {
    console.error("DEBUG: LOGIN FAILED!!! but skipped location.reload!!!");
    return;
  } 

  var href = document.location.href,
    pos_dash = href.lastIndexOf("#");
  if(pos_dash > 0)
  {
    href = href.substr(0, pos_dash);
  }

  document.location.href = href + "#login-failed"
 
  setTimeout(function()
  {
    location.reload();
  }, Math.floor(Math.random() * 777) + 1);
}


<?php
usleep(rand(10000, 50000));  // thats between 10ms and 50ms 
ob_end_flush();
die();
