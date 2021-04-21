
// Note: Using nimiq qr-code-scripts import(module)
// Note: HACK: Had to add this line to scanner.min.js: window.QrScanner = e;


var QR_win=0;

// ----------------------------------------------------------------
function QR_print(txt)
{
  if(!QR_win || QR_win.closed)
  {
    QR_win = window.open();
    QR_win.document.write("<head><title>QR-Code</title><style>.qr-out,.qr-out>canvas{width:9cm;height:9cm;}</style></head><body><div class='qr-out'></div></body>");
  }
  else
  {
    QR_win.focus();
  }

  var dp = QR_win.document.querySelector(".qr-out"),
      dc;
  while(dc = dp.lastElementChild) dp.removeChild(dc);  // empty

  QR_out(dp, txt);

  setTimeout(function() { QR_win.print(); }, 300);
}


// ----------------------------------------------------------------
function QR_out(dp, txt)
{
  try
  {
     import('../_3p/qr/qr-code.min.js').then((mod) =>
     {
      console.log("Creating QR: ", txt, dp);
      QrCode.render({
      "text": txt,
      "radius": "0",
      "fill": "#191919",
      "background": null,  // transparent
      "size": "512",
      "label": "",
      "mode": 0,
    }, dp);
    });
  }
  catch (err)
  {
    console.error("QR_out catch-err: ", err);
  }
}

// ----------------------------------------------------------------
function QR_scan(f_ready)
{
  QR_in(0, f_ready);
}


// f_ready(result) must be a function with one param
// ----------------------------------------------------------------
function QR_in(dp, f_ready)
{
  // f_ready("http://localhost/www/charbu-online/_Total/_HTM/?y=package&x=55&charge=A89292"); return;  // PC-Test (no-cam)

  try
  {
    import('../_3p/qr/qr-scanner.min.js').then((mod) =>
    {
      QrScanner.WORKER_PATH = '../3p/qr/qr-scanner-worker.min.js';

      console.log("Scanning QR... ");

      var hasCam = QrScanner.hasCamera();
      if(!hasCam)
      {
        warn(["<lang lang='en'>No camera available!</lang>",
	"<lang lang='de'>Leider ist keine Kamera aktivierbar.</lang>"].join(""));
        return;
      }

      var dv=0;
      if(typeof dp != UN && dp.length)
      {
        // dp ok?
        dv = dp.CE("<video>").A({muted: "muted", playsinline: "playsinline", class: "qr-in-video"});
      }
      else
      {
        lightbox("<video muted playsinline class='qr-in-video'></video>");
        dv = fE("lightbox").Q(".qr-in-video");
      }

      var scanner = new QrScanner(dv, (res) => { f_ready(res); });
      scanner.start();
    });
  }
  catch (err)
  {
    console.error("QR_in (QR-Scanner) catch-err: ", err);
  }
}
