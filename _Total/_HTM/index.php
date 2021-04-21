<?php

header('Content-type: text/html');
define("MODE_STRICT",0);

error_reporting(E_ALL | E_STRICT);

$is_admin = 0;
$_SESSION = array();

require_once("../_make/_3p/phpQuery-onefile.php");
$doc = phpQuery::newDocumentHTML(file_get_contents("Total.HTM"));

$err="";
if(isset($_POST['m']))
{
  if($_POST['m'] != "Acting against human dignity means bad to me and my beloved as well!") die("Bad ethics!");

  include("../version.php");

  $ok_come_from_api=1;
  include("../config_dont_touch.php");

  $GLOBALS["indexOk"] = 815;
  require_once("../_login/util_sec.php");
  if(!AUTH($GLOBALS["v"])) die("Au!");

  echo $doc["main"]->html();
  exit();

/*  if(strlen($err))
  {
    echo "ERROR: ",$err;
    die();
  }*/
}

//  $doc["main"]->empty();        // TODO use this!!!

$doc["#div-script-container"]->after('<div id="div-script-login"><script id="script-login" src="../_login/login_init.js"></script></div>');

echo $doc->html();
