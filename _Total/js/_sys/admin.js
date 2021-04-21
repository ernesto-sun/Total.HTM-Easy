
//
// Note: admin.js comes before tool.js and is inside _make because without tool.js it still allows logout and MAKE,
//       or better to say: Release-Management
//       This is useful for those who edit Total.HTM manually, make online-releases and don't want all the weight of tool.js
//
//  In short: In this file is the whole Client-Side MAKE/Release Management but no other Admin-Tools (as bundled in tool.js)
//

_b.Ca("login-total");   // means logged in

var dm = fE("menu-dyn"),
    hasAPP = 0;
    
dm.AP(NAV_LI_make(_LLAD.make, "fa-gear", MAKE).A("id", "but-admin-make"));

if(hasAPP)  // TODO
{
  dm.AP(NAV_LI_make(_LLAD["make-app"], "fa-gear", MAKE_APP).A("id", "but-admin-app"));
}

// ---------------------------------------------------------------------------------

// Check if tools are available and add them...

POSTA(_xr_ut + "_wysiwyg/get_tool_js.php").then((sc) =>
{
  _b.CE("script").APs(sc);
  setTimeout(() => { FRAME(TOOL_init) }, 250);
},
(ex) =>
{
  console.warn("WYSIWYG-tools not available, thats ok");
});


// -----------------------------------------------------------------------------
function MAKE()
{
  MH_close();

  POSTA(_xr_ut  + "_make/get_info_make.php", {}, 1).then((d) =>
  {
    last_release = "never so far";
    version = "fresh (or invalid)";
    if(typeof(d) != UN && typeof(d.last_release_ms) != UN)
    {
      last_release = DT_diff_txt(fI(d.last_release_ms / 1000)).toLowerCase();
      version = d.version;
    }

    var ht = [
         "<div class='align-center'><h3>MAKE Release and Test</h3><br/>",
         "<span>Release was made " + last_release + " ago. <br/>Current version is: " + version + "</span><br/><br/>",
         "<button id='but-make'>MAKE Release</button>",
         "<button id='but-make-test'>MAKE Test</button></div>"].join("\n");

    lightbox(ht,0,1);

    fE("but-make").Ea("click", MAKE_release);
    fE("but-make-test").Ea("click", MAKE_test);
  });
}

// -----------------------------------------------------------------------------
function MAKE_release()
{
  lightbox_clean();
  loading_start();
  POSTA(_xr_ut + "_make/index.php", {}, 1).then((d) =>
  {
    loading_end();
    if(typeof(d.link) == UN)
    {
      var str="Make of Release failed";
      if(typeof(d.err) != UN) str += " because: " + d.err;
      else str += "!";
      err(str);
    }
    else
    {
      msg("Make of Release done! <a href='" + d.link + "' onclick='aclick.call(this, event)'>Click here</a> to open it.", 20);
    }
  },
  (ex) =>
  {
    loading_end();

    var str="Make of Release failed";
    if(typeof(ex.err) != UN) str += " because: " + ex.err;
    else str += "!";
    err(str);
  });
}

// -----------------------------------------------------------------------------
function MAKE_test()
{
  lightbox_clean();
  loading_start();
  POSTA(_xr_ut + "_make/index.php", {test: "test"}, 1).then((d) =>
  {
    loading_end();
    if(typeof(d.link) == "undefined")
    {
      var str="Make of Test failed";
      if(typeof(d.err) != "undefined") str += " because: " + d.err;
      else str += "!";
      err(str);
    }
    else
    {
      msg("Make of Test done! <a href='" + d.link + "' onclick='aclick.call(this, event)'>Click here</a> to open it.", 20);
    }
  },
  (ex) =>
  {
    loading_end();

    var str="Make of Release failed";
    if(typeof(d.err) != "undefined") str += " because: " + d.err;
    else str += "!";
    err(str);
  });
}

// ---------------------------------------------------------------------------------
function MAKE_APP()
{
  // TODO
}

