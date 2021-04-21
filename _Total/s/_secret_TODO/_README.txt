
-------------------------------------------------------------
      The SECRET Total.HTM Static-Dir: /_Total/s/s/   (_secret_TODO)
-------------------------------------------------------------

This folder with all it's content can be password-protected. 

BUT: This require some action as file-admin on server-level. You need to
set the absolute path to the file '.htpasswd'. 

Because(!):  AuthUserFile in '.htaccess' can not be relative. 

This only works with Apache, if you run another syou will know what to do. 

Note: In your server admin console (e.g. CPANEL) you might have simple click-tools 
to protect a folder with Basic Auth. 
