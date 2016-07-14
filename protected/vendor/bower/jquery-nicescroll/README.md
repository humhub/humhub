#jQuery.NiceScroll
v. 3.6.8 02-29-2016

 - [Web Site: nicescroll.areaaperta.com](http://nicescroll.areaaperta.com)
 - [Repo: github.com/inuyaksa/jquery.nicescroll](https://github.com/inuyaksa/jquery.nicescroll)
 - [Twitter: @nicescroll](https://twitter.com/nicescroll)

 [![Join the chat at https://gitter.im/inuyaksa/jquery.nicescroll](https://badges.gitter.im/inuyaksa/jquery.nicescroll.svg)](https://gitter.im/inuyaksa/jquery.nicescroll?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

> Nicescroll as a Greasemonkey plugin: http://userscripts.org/scripts/show/119910 (freezed)


> Nicescroll is a jquery plugin, for nice scrollbars with a very similar ios/mobile style.

  - HORIZONAL scrollbar support!
  - It supports DIVs, IFrames, textarea, and document page (body) scrollbars.
  - Compatible with all desktop browser: Firefox 4+, Chrome 5+, Safari 4+ (win/mac), Opera 10+, IE 6+. (all A-grade browsers)
  - Compatible with mobile device: iPad/iPhone/iPod, Android 2.2+, Blackberry phones and Playbook (WebWorks/Table OS), Windows Phone 7.5 Mango.
  - Compatible with all touch devices: iPad, Android tablets, Window Surface.
  - Compabible with multi-input device (mouse with touch or pen): Window Surface, Chrome Desktop on touch notebook.
  - Compatible with 2 directions mice: Apple Magic Mouse, Apple Mouser with 2-dir wheel, PC mouse with 2-dir wheel (if browser support it).

So you have scrollable divs with momentum for iPad 4+ and you have consistent scrollable areas for all desktop and mobile platforms.

Sexy zoom feature, you can "zoom-in" the content of any nicescroll'ed div.
Nice to use and nice to see, all the content of the div in fullscreen mode.
It works on desktop (double click on div) either in mobile/touch devices using pinch gesture.

On modern browsers hardware accelerated scrolling has implemented.
Using animationFrame for a smoothest and cpu-saving scrolling. (when browser supports)

"Use strict" tested script for maximum code quality.
Bower and AMD ready.

Warning for IE6 users (why do you uses IE6 yet? Please updgrade to a more stable and modern browser), some feature can't work for limitation of the browser.
Document (body) scrollbars can't appears, old (native browser) one is used. Some issues with IFrame scrolling.


## FEATURES

- simple installation and activation, it works with NO modification of your code. (some exceptions can happen, so you can write to me)
- very stylish scrollbars, with no occupation on your window (original browser scrollbars need some of page space and reduces window/div usable width)
- you can style main document scrollbar (body) too!! (not all script implements this feature)
- on all browsers you can scroll: dragging the cursor, mouse wheel (speed customizable), keyboard navigation (cursor keys, pagup/down keys, home/end keys)
- scroll is smooth (as modern tablet browsing), speed is customizable
- zoom feature
- hardware accelerated scroll (where available)
- animation frame support for smoth scrolling and cpu-saving
- dragging scroll mode with scrolling momentum (as touch device)
- tested for all major browsers desktop and mobile versions
- support for touch devices
- support for multi-input devices (IE10 with MSPointer)
- compatible with many other browsers, including IE6, Safari on Mac and WP7 Mango!
- very customizable aspect of bar
- native scroll events are working yet
- fully integrated with jQuery
- compatibile with jQuery UI, jQuery Touch, jQuery Mobile


## DEPENDENCIES
>> It's a plugin for the jquery framework, you need to include jquery in your scripts.
>> From 1.5.x version and on. (I'd better to use 1.8.3+ minimum)


* INSTALLATION
Put loading script tag after jquery script tag and loading the zoom image in the same folder of the script:

<script src="jquery.nicescroll.js"></script>

Copy image "zoomico.png" in the same folder of jquery.nicescroll.js.


* HOW TO USE

Initialize nicescroll ALWAYS in (document) ready statement.
```javascript
// 1. Simple mode, it styles document scrollbar:
$(document).ready(function() {  
    $("html").niceScroll();
});

// 2. Instance with object returned:
var nice = false;
$(document).ready(function() {  
    nice = $("html").niceScroll();
});

// 3. Style a DIV and chage cursor color:
$(document).ready(function() {  
    $("#thisdiv").niceScroll({cursorcolor:"#00F"});
});

// 4. DIV with "wrapper", formed by two divs, the first is the vieport, the latter is the content:
$(document).ready(function() {
    $("#viewportdiv").niceScroll("#wrapperdiv",{cursorcolor:"#00F"});
});

// 5. Get nicescroll object:
var nice = $("#mydiv").getNiceScroll();

// 6. Hide scrollbars:
$("#mydiv").getNiceScroll().hide();

// 7. Check for scrollbars resize (when content or position have changed):
$("#mydiv").getNiceScroll().resize();

// 8. Scrolling to a position:
$("#mydiv").getNiceScroll(0).doScrollLeft(x, duration); // Scroll X Axis
$("#mydiv").getNiceScroll(0).doScrollTop(y, duration); // Scroll Y Axis
```

## CONFIGURATION PARAMETERS
When you call "niceScroll" you can pass some parameters to custom visual aspects:

```javascript
$("#thisdiv").niceScroll({
    cursorcolor: "#424242", // change cursor color in hex
    cursoropacitymin: 0, // change opacity when cursor is inactive (scrollabar "hidden" state), range from 1 to 0
    cursoropacitymax: 1, // change opacity when cursor is active (scrollabar "visible" state), range from 1 to 0
    cursorwidth: "5px", // cursor width in pixel (you can also write "5px")
    cursorborder: "1px solid #fff", // css definition for cursor border
    cursorborderradius: "5px", // border radius in pixel for cursor
    zindex: "auto" | <number>, // change z-index for scrollbar div
    scrollspeed: 60, // scrolling speed
    mousescrollstep: 40, // scrolling speed with mouse wheel (pixel)
    touchbehavior: false, // enable cursor-drag scrolling like touch devices in desktop computer
    hwacceleration: true, // use hardware accelerated scroll when supported
    boxzoom: false, // enable zoom for box content
    dblclickzoom: true, // (only when boxzoom=true) zoom activated when double click on box
    gesturezoom: true, // (only when boxzoom=true and with touch devices) zoom activated when pinch out/in on box
    grabcursorenabled: true // (only when touchbehavior=true) display "grab" icon
    autohidemode: true, // how hide the scrollbar works, possible values: 
      true | // hide when no scrolling
      "cursor" | // only cursor hidden
      false | // do not hide,
      "leave" | // hide only if pointer leaves content
      "hidden" | // hide always
      "scroll", // show only on scroll          
    background: "", // change css for rail background
    iframeautoresize: true, // autoresize iframe on load event
    cursorminheight: 32, // set the minimum cursor height (pixel)
    preservenativescrolling: true, // you can scroll native scrollable areas with mouse, bubbling mouse wheel event
    railoffset: false, // you can add offset top/left for rail position
    bouncescroll: false, // (only hw accell) enable scroll bouncing at the end of content as mobile-like 
    spacebarenabled: true, // enable page down scrolling when space bar has pressed
    railpadding: { top: 0, right: 0, left: 0, bottom: 0 }, // set padding for rail bar
    disableoutline: true, // for chrome browser, disable outline (orange highlight) when selecting a div with nicescroll
    horizrailenabled: true, // nicescroll can manage horizontal scroll
    railalign: right, // alignment of vertical rail
    railvalign: bottom, // alignment of horizontal rail
    enabletranslate3d: true, // nicescroll can use css translate to scroll content
    enablemousewheel: true, // nicescroll can manage mouse wheel events
    enablekeyboard: true, // nicescroll can manage keyboard events
    smoothscroll: true, // scroll with ease movement
    sensitiverail: true, // click on rail make a scroll
    enablemouselockapi: true, // can use mouse caption lock API (same issue on object dragging)
    cursorfixedheight: false, // set fixed height for cursor in pixel
    hidecursordelay: 400, // set the delay in microseconds to fading out scrollbars
    directionlockdeadzone: 6, // dead zone in pixels for direction lock activation
    nativeparentscrolling: true, // detect bottom of content and let parent to scroll, as native scroll does
    enablescrollonselection: true, // enable auto-scrolling of content when selection text
    cursordragspeed: 0.3, // speed of selection when dragged with cursor
    rtlmode: "auto", // horizontal div scrolling starts at left side
    cursordragontouch: false, // drag cursor in touch / touchbehavior mode also
    oneaxismousemode: "auto", // it permits horizontal scrolling with mousewheel on horizontal only content, if false (vertical-only) mousewheel don't scroll horizontally, if value is auto detects two-axis mouse
    scriptpath: "" // define custom path for boxmode icons ("" => same script path)
    preventmultitouchscrolling: true // prevent scrolling on multitouch events
    disablemutationobserver: false // force MutationObserver disabled
});
```

Related projects
----------------

* [Nicescroll for Angular](https://github.com/tushariscoolster/angular-nicescroll)

* LICENSE

## Copyright 2011-16 InuYaksa

######Licensed under the MIT License, http://www.opensource.org/licenses/mit-license.php
######Images used for zoom icons have derived from OLPC interface, http://laptop.org/8.2.0/manual/Browse_ChangingView.html
