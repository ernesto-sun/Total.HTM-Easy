------------------------------------------------------------------------------
				Safe Web Fonts
------------------------------------------------------------------------------

Seems the following fonts are usually installed on most devices. 

----------------------------
These are the famous font-families:
----------------------------

serif
sans-serif
monospace
cursive
fantasy


Note: Decision: NOT use font-families directly, instead the list below that
includes all font-families as fallback. Why? Because otherwise the Browser/OS 
would decide which font actually to use for a font-family. Thus, we need to 
make it explicit.    

----------------------------

var _FONT_ARR = [
["Arial", "arial, sans-serif"],
["Arial Black", "\"arial black\", sans-serif"],
["Arial Narrow", "\"arial narrow\", sans-serif"],
["Baskerville", "baskerville, serif"],
["Brush Script MT", "\"brush script mt\", cursive"],
["Courier New", "\"courier new\", monospace"],
["Georgia", "georgia, serif"],
["Lucida Bright", "\"lucida bright\", serif"],
["Lucida Sans Typewriter", "\"lucida sans typewriter\", monospace"],
["Palatino", "palatino, serif"],
["Papyrus", "papyrus, fantasy"],
["Tahoma", "tahoma, sans-serif"],
["Times New Roman", "\"times new roman\", serif"],
["Trebuchet MS", "\"trebuchet ms\", sans-serif"],
["Verdana", "verdana, sans-serif"],
];


-------------------------------------------------------------
A more complete but less safe list
-------------------------------------------------------------

American Typewriter 		, serif
Arial						, sans-serif
Arial Black					, sans-serif
Arial Narrow				, sans-serif
Avant Garde
Bookman
Baskerville					, serif
Bradley Hand 				, cursive
Brush Script MT				, cursive
Helvetica
Copperplate
Comic Sans					, cursive
Courier New					, monospace
Courier						, monospace
Candara
Calibri
Cambria
Didot						, serif
Garamond 					, serif
Geneva
Georgia						, serif
Helvetica 					, sans-serif
Impact						, sans-serif
Lucida Bright				, serif
Lucida Console 				, monospace
Lucida Sans Typewriter		, monospace
Luminari 					, fantasy
Monaco						, monospace
Optima
Palatino					, serif
Papyrus						, fantasy
Perpetua
Tahoma						, sans-serif
Times
Times New Roman				, serif
Trebuchet MS 				, sans-serif
Verdana						, sans-serif




_b.CSS({"font-family": "Didot"});



<details> … </details>
Used for additional information. User has
the option to view or hide this.
<summary> … </summary>
Used as a heading for the above tag. Is always
visible to the user.


<dialog>
<time>