Total.HTM Coding Style
===============

Our coding philosophy is KISS-like. **KISS**: Keep it small and simple. 

In other words: "As compact as possible, as readable as nice."

Thanks to the MAKE-script we can use lots of comments and console-output, all removed during MAKE.

At the end of the day, compact code not only loads faster, but also helps us maintain code-quality.

Please only contribute to the official code repository, if you do understand and respect this Coding Style. 
Otherwise please create your own fork or such. 

Thanks so much for your help. **You are welcome!** 


## Codebase Overview

At Total.HTM Easy, these computer languages are used:

* HTML (HyperText Meta Language, Version 5)
* SVG (Scalable Vector Graphic, basic use only for cross browser compatibility)
* CSS 3 (Cascaded Style Sheets)
* JS (JavaScript =~ ECMAScript 6) and JSON (JavaScript Object Notation)
* PHP 5 (Server-side code for Admin-tools and WYSIWYG-editing)

All code is defacto-web-standard: Supported among all common browsers. 
We use e.g. https://caniuse.com to check if the code runs everywhere relevant. 


## General Coding Guidelines

### Basic US Tech Mainstream English

So many variants of English. Have you heard of ('Basic English')[http://en.wikipedia.org/wiki/Basic_English]?
Not as old as Esperanto, but a great deal for international digital communication.

We use simple English, KISS of course. In case of doubt, e.g. "gray" or "grey" we use the US way. Thus: "gray". 

All files are UTF8 and all naming and commenting keeps in English. 

No, it's not fun to put a German, Spanish or Russian word or saying here or there. 

Keep with friendly clean basic English please!


### "Complex is ok, complicated is not."

If some code looks complicated, split it up into parts and lines and add comments explaining it step by step. 

The best case scenario is code that is damn simple, put into a clear context/scope, short, easy to read, for beginners even. 

A good function is always short, a long function is separated into short parts/blocks clearly.

A good function is easy to understand because it has one single clear purpose.

If the advantage of extra-compact code is little (e.g. small performance boost after an UX-event),
always prefer the less compact but more readable version of that code.   


### "One code to serve them all!" 

So far we hardly needed any browser-specific if-blocks and that's good. 

Some functions like 'fullscreen mode' are browser-specific, some CSS still needs vendor-prefixes. That is ok where unavoidable, if kept minimal, local and explicit. Over time it will be fixed. 

No need to support/test IE (Microsoft Internet Explorer), even though IE9 would do most of it.

We love web standards and we believe they offer all we need.

We try to share educating high quality code with the world. Sometimes challenging but very helpful in the long run.


### "There is always something TODO"

Wherever improvements and optimizations are possible, put a comment starting with **TODO**. This allows easy text finding.

```
function a(b) {}  // TODO: Support a second param
```


### "Fast changes are HACKs"

We come into situations to change something right now to fit some urgent need. In this cases use the keyword **HACK** with timestamp and name, so that even on text-level such HACKs are easy to find - and more importantly: hard to oversee!

```
a = b * 180;  // HACK: 20201224 Ernesto: This was 160 before, still trying...
```

In general feel welcome to provide timestamp and name in comments wherever useful.


### Some code locations deserve a "HERE"

All code of Total.HTM is mend to be read by interested people, and also to be modified by those who know what they do. To make it a bit easier we use lot's of comments. Especially useful places are marked by the keyword **HERE**. 

```
<!-- HERE is a good place to include your own scripts -->

```


### Folder- and File-Names

We want the file structure to work on any kind of common file system. 

Some recommendations to avoid future headache:

* Small-cap only, if no good reason to use capitals (e.g. for branding). 
* If using capitals, always write them exact, also at case-insensitive Windos.
* Use short file endings. Use '.jpg', not '.jpeg'. Use '.htm', not '.html'. Etc.
* No special characters, not many numbers. Never\* a number as first character.
* Folders can start with underline _, filenames can not. Underlined '_folders' stick at the top.   
* Never use a special character in file- or folder-names. Not even - or $.
* Never ever use a space in a file- or folder-name. Use underlines. E.g. 'my_nice_file.txt' 
* Only break naming rules if you have a good reason to do so.
* Prefer flat folder-structures over deep nested ones. 
* No need to keep file- or folder-names ultra short. Make them speaking for themselves.
* No file is timeless. Some are very time-specific. Use timestamps for time-specific files.

\* Timestamps are the one example where filenames can start with numbers. Always use timestamps 
with year first, month second and day last. This allows text-sorting. Example:

``` 
'20201221_engineers_diary__some_topic.txt'
``` 

### Bijective is the word 

Maybe read about this word and what it means in maths but also to coding. For me this word is the essence of my coding philosophy. Bijective definitions mean to me, winning over this 'evil' entropy one battle. Coding costs lots of energy, time, food, ... Bijective code is the result of dissipative structure formation during the unavoidable rise of entropy. Or, maybe more confusing: Well done definitions give value to our planet.


## HTML and SVG

SVG is seen as part of HTML and/or similar to HTML. Both are based on XML (and SGML) and both come with lots of legacy and well-known little problems. At least for me its not always easy with XML and alike. 


### HTML comments are great

We all want to love the language of the web. 

One way to show this love is using HTML comments. During MAKE they are removed anyway. 

One goal with 'Total.HTM Easy' is to grant users full control within the single Total.HTM file. Users can control CSS and JS by just adding and removing classes. HTML is the central point of control and this specific power needs to be explained to the users. Thus: Welcome HTML comments and explain everything well. 

```
<!-- Why do comments in HTML have such a bad reputation? Don't they look nice? -->
``` 

### HTML Indentation 

HTML often consists of blocks spanning a huge distance within the text-file. It is not like JS, PHP, CSS and other languages, that consist of lots of small blocks simple to oversee. Within a long HTML file it can be hard to find the closing ```</div>``` for some ```<div>``` a 1000 lines before. 

With hard to oversee block sizes typically comes a very irregular overall structure, flat and repeating over many lines, then suddenly deeply nested, then flat again. HTML code can be hard to manage as text.  

This is, why it is specially hard at HTML to find the right indentation. You can use indentation to make local DOM-structures more intuitive to read, but in general it's good to keep HTML flat.   


### HTML Attributes & Strings

In most web languages both single ' and double " quotes can be used. At HTML we only use double ". This is because also at JS we only use ", and if we have some HTML-string as JS-variable we use single quotes. We do not use spaces around the '=' of HTML-Attributes.

```
<form id="top-form" method="POST"></form>      // see the use of double quotes here at HTML

<script>
var html = "<input id='pla' type='text'/>";    // see the use of single quotes here at JS (writing HTML)
fE("top-form").APs(html); 
</script>

```

### EM is the best

The sizing entity 'px' is very very useful und highly used within Total.HTM. In fact I hardly ever write 'px' in my coding life since 'em' came to my attention. Just found a nice quote at a [W3C-site](https://www.w3schools.com/cssref/css_units.asp):

Tip: The em and rem units are practical in creating perfectly scalable layout!

Our strategy with layout is largely based on 'em', generous in vertical, sometimes using '%' for width, sometimes using 'vw' and 'vh'. Now, 'rem' is not really needed, because of the recommendation to always have a ``` <span> ``` around text.  


### Text has <span>

When using 'em' for layout, we get lots of advantages. But one thing is dangerous: Changing the font-size of block-elements. Why? Because margin and padding sizing done in 'em'. It becomes hard to estimate what 1em means at some nested ``` <div> ``` somewhere. 

That's why - and also because of other combined advantages arising trough it - we always use a ``` <span> ``` element to wrap text element or any content that is inline. This allows us, to set any font-size to the ``` <span> ``` elements, and all the ``` <div>s ``` that build the layout never change the font-size and thus, we can trust the 'em' of our ``` <div>s ```.

You got it?

Well. Within section-bodies users will often make ``` <p> and <div> and <li> ``` with text directly in it. This is ok, not ideal, but ok. BUT: You, as a developer, and apart from simple content, may care that all inline HTML-fragments are wrapped by a ``` <span> ``` element. Avoid nested ``` <span> ```, use them like end-points (leafs) in the DOM-tree.      

```
<main>
  <section>
    <div class="section-body">
	   <p><span>Here comes the text</span></p>
    </div>
	...
```


## JavaScript and PHP

Those two languages are 'General Purpose Languages' and they allow all kind of code constructs, also dangerous ones. Some recommendation are valid for both JavaScript and PHP.


### No eval()

Dynamic execution of code as string input must be avoid. I do not know any reason why a website or even web-app may need such a construct. Asynchronous script loading and importing/including is dynamic enough by far.


### Don't be greedy in vertical

We use the so called ['Allman Style'](https://en.wikipedia.org/wiki/Indentation_style#Allman_style) if it comes to nested code, and code blocks in general. Brackets that start and end a block of code, have their own lines each.  

Give your code vertical space, text file length and screen scrolling isn't limited in vertical. 

```

// ---------------------------------------------------------------------------
function someFunction(e, d)  	// Each bracket has it's own line...
{
	var a = 12,
		b = 67;    // each var it's own line, at least if a value is assigned

	// A line then and when in between commands and comments for better readability

	if(a < e)
	{
		calc(a, e);
	}
	else
	{
		calc(d, b);
		calc(b, e);	
	}
	return "happy";
}
```

### Give each function a line of dashes 

In the example before you see a comment line full of dashes '-', less than 80 chars long. Above the nice dash-line indicating a new function, keep at least two empty lines. This delivers the reader a clear optical impression where functions begin. 


### Do scripting with script languages  

Some schools teach data persistence and object oriented modelling without exception. You might not believe in good code beyond classes, if you e.g. come from a Java background.

We use short / simple / as-independent-as-possible functions that stand alone. We use local variables and global variables. Mostly primitives (int / double / string) and sometimes JSON / Arrays. That's about it. 

Scripts have to load at runtime - they are interpreted. Usually they are loaded to do something and to end fast. If there is something like a persistent object model, it's DOM.

Specially PHP scripts have no persistence at all. They only play INPUT -> OUTPUT, not even ping-pong. Ideally they are stateless and do not use much memory at all. Nice PHP code runs safe, ends fast.

Both PHP and JS were intended as interpreted scripting languages - I think that's just about right.


### Parse to number wherever possible

Both PHP and JS allow a variable to be 'int' or 'string' or even change at some point. Wherever possible, make sure you deal with numbers. It's much harder to do cracking with numbers than with strings. 

Be conservative in what you accept, specially server-side.

```
<?php

$v = 0;
if(isset($_POST['v'])) $v = (int)$_POST['v'];

```

### Spaces after , and : within lists and objects

After commas and after : usually comes a space, just to make reading easier. 

Do not make spaces between brackets in the same line. 

```
d.CSS({color: "red"});
someFunc(a, b, 13.67, "Hello");
```

### Spaces before and after operators

We allow ourselves just as many spaces as it makes reading nice. MAKE removes spaces anyway. After the main-bracket of an if- or an for-statement comes one space as well.

```
if(a > b) c = (d ? e : f);
if(e <= a && e != b) f++;

```

## Always the same flat switch indentation

Funny! I always liked 'switch' statements and I always hated them. I always thought they must execute damn fast because a compiler must be able to translate them into machine code so much better. At some point I also heard the opposite, and it depends a lot on language and environment, ... Old stories. But just recently I realized that 'break;' is just a regular statement, and that brackets make each case-block safe in scope, and that keeping flat makes 'nested switch' so much nicer. Well, this coding style is the result: 

```
switch(v)
{
case 'a':
{
	break;
}	
case 'b-special':
case 'b':
{
	break;
}
default:
	console.error("Invalid case: " + v);	
}

```


## Switch cases without break

If a case block ends with a 'return'-statement, its explicit, no break and nothing else is needed. Sometimes 'case'-blocks end neighter with a 'return'-, nor with a 'break'-statement. If such an open 'case'-block has code, make a comment like this:

```
switch(v)
{
case 'a':
{
	console.log("Only 'a'");
	// no break here to keep running
}
case 'b':
{
	console.log("Both 'a' and 'b'");
	break;
}
default:
	console.error("Invalid case: " + v);	
}

```
Without the 'no break' comment it would be unclear if the 'break' is missing, or if the block is intended to be without break. 



## JavaScript and JSON

### JS Strings in double ", not in single ' quotes 

With PHP the kind of quote you use for strings makes a real difference. With JavaScript (unfortunately) as well both quotes are allowed, not making any difference. At official JSON, double " is mandatory, but most parsers do allow JSON strings with single quotes. 

Specially with JSON, often used for transferring, it is highly important to use double quotes " always. 

Rule: At JavaScript and JSON we only use double quotes " for strings.


### Short variable names

Short variable names are great, best together with short functions.  

```
(e) =>				// e stands for event
{
	var d = fE("pla"),		// d stands for DOM-element 
		v1 = d.value,
		v2 = e.target.value;	// if a few of some var are needed, simple numbering is ok
}

```

### Use for-of-loops for arrays

JS knows a number of ways how to step trough the elements of an array. We always use the new ES6-way: 

```
for(d of arr) d.doit();

```


## PHP Specific Guidelines


### Use brackets to integrate variables into strings 

```
$v1 = "Hello";
$v2 = "User";

$s = "The message is: {$v1} {$v2} ";   // Do it this way

$s = 'The message is: '.$v1.' '.$v2;   // A less recommended way, sometimes useful though

$s = "The message is: $v1 $v2";   	   // This also works, but is NOT recommended!! 

```


### Single quotes for simple PHP strings

Also in PHP, strings can be used like "Hello" and as well like 'Hello'. But there is a significant difference here: Strings in single quotes execute faster because the PHP-Interpreter does not allow variables in them. 

```
$simple = 'test';

$complex = "The Test-Variable is: {$simple}, you get it?!"; 

```

### Use echo with commas

Since PHP is here to make one thing: output, we often use lots of 'echo' commands. Use 'echo', not 'print#. If we want to echo several things, we use comma in between, not the string concatenation. That would only result in extra work.

```

echo 'Hello ', $name, ', you are welcome!';   // That's correct and fast

echo 'Hello '.$name.', you are welcome!';     // Does the same but slower.

echo "Hello {$name} you are welcome!";     	  // Does the same as well, but still slower.

```


### No config but Confi Config

We use the ('PHP Confi Config')[https://github.com/ernesto-sun/ConfiConfigPHP] script to create and maintain configuration for PHP. Never store any configuration in a database, another file or anywhere else. 

Note: Client side config is done in the file '../js/setting.js' mainly. Some config is at the top of the file '../_HTM/Total.HTM'.   



## CSS

If you think CSS is primitive you might oversee how much it can influence the more complex JS world within a project. "Three well put CSS lines can substitute 100 lines of JS codes" ~ and execute faster and safer by the way.

CSS 3 is a real blessing to web developers that have been around the last decades. Nowadays CSS 3 is so well supported among all relevant browsers. CSS is always the best option. We try to realize as much as possible with CSS, because that also works without JS and it usually results in better performance and readability. 

No indentation for CSS code blocks. There is no nested code anyway. (Other than the rare @media-queries)

Use comments in CSS, they are removed by MAKE anyway. CSS-commenting works ``` /* like this */ ```

Use CSS-variables, and use them within the existing concept of CSS-variables already used. Most of them are at the top of the file 'Total.HTM' and some at the file 'form_basic.css'. Theming CSS files usually overwrite CSS-variables. Theming CSS files  match '../css/theme_*.css'. 

Give each bracket at CSS its own line. (As usual). Further: Empty lines between CSS statements. Spaces after colon ':'

CSS-definitions are not complex in syntax, but they can be complex in their effect on the end result. 

```

.num-regular > input, 
.com-radio > fieldset
{
border: var(--inp-border);
cursor: pointer;
width: calc(100% - 2em);
}

```

### Fancy animations optional

Animations that are not essential to UX may be wrapped by a statement that avoids the animation on slow devices or if a device is running out of battery etc. The browser tells us if it prefers reduced motion, we listen to it.  

```
@media only screen and (not(prefers-reduced-motion)) 
{

@keyframes anim-col-github
{
0% { fill: green; }
100% { fill: blue; }
}

#developer.in #svg-github-anim 
{
animation: anim-col-github 2s infinite alternate;
}

}
```





## Final words

Thanks for reading this set of guidelines and recommendations; or at least for flying over them. Critics, ideas and all kind of feedback are very welcome. I really hope you join us. Together we are a world bigger than before. 


Feel welcome! And have a nice day!

