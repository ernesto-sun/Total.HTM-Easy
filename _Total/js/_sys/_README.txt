/*
This folder is for js-files that are loaded dynamically later on with JS.

If you e.g. want to use QR-Code-Function you have in the file:

_Total/js/_lazy/qr.js

Then you call a function inside by using this:

LAZY("qr", function(){ QR_print("pla"); });

or

LAZY("qr", function(){ QR_scan(function(d){console.log("DONE",d);})});

*/
