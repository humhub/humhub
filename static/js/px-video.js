function InitPxVideo(options) {

	"use strict";

	// Utilities for caption time codes
	function video_timecode_min(tc) {
		var tcpair = [];
		tcpair = tc.split(' --> ');
		return videosub_tcsecs(tcpair[0]);
	}

	function video_timecode_max(tc) {
		var tcpair = [];
		tcpair = tc.split(' --> ');
		return videosub_tcsecs(tcpair[1]);
	}

	function videosub_tcsecs(tc) {
		if (tc === null || tc === undefined) {
			return 0;
		}
		else {
			var tc1 = [],
				tc2 = [],
				seconds;
			tc1 = tc.split(',');
			tc2 = tc1[0].split(':');
			seconds = Math.floor(tc2[0]*60*60) + Math.floor(tc2[1]*60) + Math.floor(tc2[2]);
			return seconds;
		}
	}

	// For "manual" captions, adjust caption position when play time changed (via rewind, clicking progress bar, etc.)
	function adjustManualCaptions(obj) {
		obj.subcount = 0;
		while (video_timecode_max(obj.captions[obj.subcount][0]) < obj.movie.currentTime.toFixed(1)) {
			obj.subcount++;
			if (obj.subcount > obj.captions.length-1) {
				obj.subcount = obj.captions.length-1;
				break;
			}
		}
	}

	// Display captions container and button (for initialization)
	function showCaptionContainerAndButton(obj) {
		obj.captionsBtnContainer.className = "px-video-captions-btn-container show";
		obj.captionsContainer.parentNode.parentNode.className = "has-captions";
		if (obj.isCaptionDefault) {
			obj.captionsContainer.className = "px-video-captions show";
			obj.captionsBtn.setAttribute("checked", "checked");
		}
	}

	// Unfortunately, due to scattered support, browser sniffing is required
	function browserSniff() {
		var nVer = navigator.appVersion,
			nAgt = navigator.userAgent,
			browserName = navigator.appName,
			fullVersion = ''+parseFloat(navigator.appVersion),
			majorVersion = parseInt(navigator.appVersion,10),
			nameOffset,
			verOffset,
			ix;

		// MSIE 11
		if ((navigator.appVersion.indexOf("Windows NT") !== -1) && (navigator.appVersion.indexOf("rv:11") !== -1)) {
			browserName = "IE";
			fullVersion = "11;";
		}
		// MSIE
		else if ((verOffset=nAgt.indexOf("MSIE")) !== -1) {
			browserName = "IE";
			fullVersion = nAgt.substring(verOffset+5);
		}
		// Chrome
		else if ((verOffset=nAgt.indexOf("Chrome")) !== -1) {
			browserName = "Chrome";
			fullVersion = nAgt.substring(verOffset+7);
		}
		// Safari
		else if ((verOffset=nAgt.indexOf("Safari")) !== -1) {
			browserName = "Safari";
			fullVersion = nAgt.substring(verOffset+7);
			if ((verOffset=nAgt.indexOf("Version")) !== -1) {
				fullVersion = nAgt.substring(verOffset+8);
			}
		}
		// Firefox
		else if ((verOffset=nAgt.indexOf("Firefox")) !== -1) {
			browserName = "Firefox";
			fullVersion = nAgt.substring(verOffset+8);
		}
		// In most other browsers, "name/version" is at the end of userAgent
		else if ( (nameOffset=nAgt.lastIndexOf(' ')+1) < (verOffset=nAgt.lastIndexOf('/')) ) {
			browserName = nAgt.substring(nameOffset,verOffset);
			fullVersion = nAgt.substring(verOffset+1);
			if (browserName.toLowerCase()==browserName.toUpperCase()) {
				browserName = navigator.appName;
			}
		}
		// Trim the fullVersion string at semicolon/space if present
		if ((ix=fullVersion.indexOf(";")) !== -1) {
			fullVersion=fullVersion.substring(0,ix);
		}
		if ((ix=fullVersion.indexOf(" ")) !== -1) {
			fullVersion=fullVersion.substring(0,ix);
		}
		// Get major version
		majorVersion = parseInt(''+fullVersion,10);
		if (isNaN(majorVersion)) {
			fullVersion = ''+parseFloat(navigator.appVersion);
			majorVersion = parseInt(navigator.appVersion,10);
		}
		// Return data
		return [browserName, majorVersion];
	}

	//https://developer.mozilla.org/en-US/docs/Web/Guide/API/DOM/Using_full_screen_mode
	// launch fullscreen
	function launchFullScreen(elem) {
	  if (!elem.fullscreenElement &&    // alternative standard method
	      !elem.mozFullScreenElement && !elem.webkitFullscreenElement && !elem.msFullscreenElement ) {  // current working methods				
  		var requestFullScreen = elem.requestFullscreen || elem.msRequestFullscreen || elem.mozRequestFullScreen || elem.webkitRequestFullscreen;
  		requestFullScreen.call(elem);
	  }
	}
 
	// change styles of fullscreen accordingly
	function fullScreenStyles() {
		if (document.fullscreen || document.mozFullScreen || document.webkitIsFullScreen || document.msFullscreenElement) {
			obj.fullScreenBtn.checked = true;
			//must apply other styles in container
			obj.container.setAttribute("style", "width: 100%; height: 100%;");
			obj.controls.className = "px-video-controls js-fullscreen-controls";
			obj.captionsContainer.className = "px-video-captions js-fullscreen-captions";
			obj.movie.setAttribute('width', '100%'); 
			obj.movie.setAttribute('height', '100%'); 
		} else {
			obj.fullScreenBtn.checked = false;
		// revert back to default styles
			// obj.container.setAttribute("style", "width:" + obj.movieWidth + "px");
			obj.controls.className = "px-video-controls"; 
			obj.captionsContainer.className = "px-video-captions";
			obj.movie.setAttribute('width', obj.movieWidth); 
			obj.movie.setAttribute('height', obj.movieHeight);
		}
	}
 
	// exit fullscreen
	function exitFullScreen() {
		// get appropriate vendor prefix and then call it with respect to the document
		var exitFullScreen = document.exitFullscreen || document.msExitFullscreen || document.mozCancelFullScreen || document.webkitExitFullscreen;
    exitFullScreen.call(document);
	}

	// Global variable
	var obj = {};

	obj.arBrowserInfo = browserSniff();
	obj.browserName = obj.arBrowserInfo[0];
	obj.browserMajorVersion = obj.arBrowserInfo[1];

	// If IE8, stop customization (use fallback)
	// If IE9, stop customization (use native controls)
	if (obj.browserName === "IE" && (obj.browserMajorVersion === 8 || obj.browserMajorVersion === 9) ) {
		return false;
	}

	// If smartphone or tablet, stop customization as video (and captions in latest devices) are handled natively
	obj.isSmartphoneOrTablet = /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent);
	if (obj.isSmartphoneOrTablet) {
		return false;
	}

	// Set debug mode
	if (typeof(options.debug)==='undefined') {
		options.debug = false;
	}
	obj.debug = options.debug;

	// Output browser info to log if debug on
	if (options.debug) {
		console.log(obj.browserName + " " + obj.browserMajorVersion);
	}

	// Set up aria-label for Play button with the videoTitle option
	if ((typeof(options.videoTitle)==='undefined') || (options.videoTitle==="")) {
		obj.playAriaLabel = "Play";
	}
	else {
		obj.playAriaLabel = "Play video, " + options.videoTitle;
	}

	// Get the container, video element, and controls container
	obj.container = document.getElementById(options.videoId);
	obj.movie = obj.container.getElementsByTagName('video')[0];
	obj.controls = obj.container.getElementsByClassName('px-video-controls')[0];

	// Remove native video controls
	obj.movie.removeAttribute("controls");

	// Generate random number for ID/FOR attribute values for controls
	obj.randomNum = Math.floor(Math.random() * (10000));

	// Insert custom video controls
	if (options.debug) {
		console.log("Inserting custom video controls");
	}
	obj.controls.innerHTML = '<div class="px-progress-bar">' +
		'<progress class="px-video-progress" max="100" value="0"><span>0</span>% played</progress>' +
		'</div>' +
		'<div class="px-video-time">' +
			'<span class="sr-only">time</span> <span class="px-video-duration">00:00</span>' +
		'</div>' +
		'<div class="px-video-playback-buttons">' +
			'<button class="px-video-restart"><span class="sr-only">Restart</span></button>' +
			'<button class="px-video-rewind"><span class="sr-only">rewind <span class="px-seconds">10</span> seconds</span></button>' +
			'<button class="px-video-play" aria-label="'+obj.playAriaLabel+'"><span class="sr-only">Play</span></button>' +
			'<button class="px-video-pause hide"><span class="sr-only">Pause</span></button>' +
			'<button class="px-video-forward"><span class="sr-only">forward <span class="px-seconds">10</span> seconds</span></button>' +
		'</div>' +
		'<div class="px-video-volume-controls">' +
			'<div class="px-video-mute-btn-container">' +
				'<input class="px-video-mute sr-only" id="btnMute'+obj.randomNum+'" type="checkbox" data-prevent-addition />' +
				'<label id="labelMute'+obj.randomNum+'" for="btnMute'+obj.randomNum+'"><span class="sr-only">Mute</span></label>' +
			'</div>' +
			'<div class="px-video-volume-slider">' +
				'<label for="volume'+obj.randomNum+'" class="sr-only">Volume:</label><input id="volume'+obj.randomNum+'" class="px-video-volume" type="range" min="0" max="10" value="5" />' +
			'</div>' +
			'<div class="px-video-captions-btn-container hide">' +
				'<input class="px-video-btnCaptions sr-only" id="btnCaptions'+obj.randomNum+'" type="checkbox" data-prevent-addition />' +
				'<label for="btnCaptions'+obj.randomNum+'"><span class="sr-only">Captions</span></label>' +
			'</div>' +
			'<div class="px-video-fullscreen-btn-container show">' +
			'<input class="px-video-btnFullScreen sr-only" id="btnFullscreen'+obj.randomNum+'" type="checkbox" data-prevent-addition />' +
			'<label for="btnFullscreen'+obj.randomNum+'"><span class="sr-only">Fullscreen</span></label>' +
		'</div>' +
		'</div>';

	// Adjust layout per width of video - container
	obj.movieWidth = obj.movie.width;
	if (obj.movieWidth < 360) {
		obj.movieWidth = 360;
	}
	// obj.container.setAttribute("style", "width:" + obj.movieWidth + "px");

	// Adjust layout per width of video - controls/mute offset
	obj.labelMute = document.getElementById("labelMute" + obj.randomNum);
	obj.labelMuteOffset = obj.movieWidth - 390;
	if (obj.browserName==="Firefox") { // adjust for Firefox rendering
		obj.labelMuteOffset = obj.labelMuteOffset - 10;
	}
	if (obj.labelMuteOffset < 0) {
		obj.labelMuteOffset = 0;
	}
	obj.labelMute.setAttribute("style", "margin-left:" + obj.labelMuteOffset + "px");

	// Get URL of caption file if exists
	var captionSrc = "",
		kind,
		children = obj.movie.childNodes;

	for (var i = 0; i < children.length; i++) {
		if (children[i].nodeName.toLowerCase() === 'track') {
			kind = children[i].getAttribute('kind');
			if (kind === 'captions') {
				captionSrc = children[i].getAttribute('src');
			}
		}
	}

	// Record if caption file exists or not
	obj.captionExists = true;
	if (captionSrc === "") {
		obj.captionExists = false;
		if (options.debug) {
			console.log("No caption track found.");
		}
	}
	else {
		if (options.debug) {
			console.log("Caption track found; URI: " + captionSrc);
		}
	}

	// Set captions on/off - on by default
	if (typeof(options.captionsOnDefault) === 'undefined') {
		options.captionsOnDefault = true;
	}
	obj.isCaptionDefault = options.captionsOnDefault;

	// Number of seconds for rewind and forward buttons
	if (typeof(options.seekInterval) === 'undefined') {
		options.seekInterval = 10;
	}
	obj.seekInterval = options.seekInterval;

	// Get the elements for the controls
	obj.btnPlay = obj.container.getElementsByClassName('px-video-play')[0];
	obj.btnPause = obj.container.getElementsByClassName('px-video-pause')[0];
	obj.btnRestart = obj.container.getElementsByClassName('px-video-restart')[0];
	obj.btnRewind = obj.container.getElementsByClassName('px-video-rewind')[0];
	obj.btnForward = obj.container.getElementsByClassName('px-video-forward')[0];
	obj.btnVolume = obj.container.getElementsByClassName('px-video-volume')[0];
	obj.btnMute = obj.container.getElementsByClassName('px-video-mute')[0];
	obj.progressBar = obj.container.getElementsByClassName('px-video-progress')[0];
	obj.progressBarSpan = obj.progressBar.getElementsByTagName('span')[0];
	obj.captionsContainer = obj.container.getElementsByClassName('px-video-captions')[0];
	obj.captionsBtn = obj.container.getElementsByClassName('px-video-btnCaptions')[0];
	obj.captionsBtnContainer = obj.container.getElementsByClassName('px-video-captions-btn-container')[0];
	obj.duration = obj.container.getElementsByClassName('px-video-duration')[0];
	obj.txtSeconds = obj.container.getElementsByClassName('px-seconds');
	obj.fullScreenBtn = obj.container.getElementsByClassName('px-video-btnFullScreen')[0];
	obj.fullScreenBtnContainer = obj.container.getElementsByClassName('px-video-fullscreen-btn-container')[0];

	// Update number of seconds in rewind and fast forward buttons
	obj.txtSeconds[0].innerHTML = obj.seekInterval;
	obj.txtSeconds[1].innerHTML = obj.seekInterval;

	// Determine if HTML5 textTracks is supported (for captions)
	obj.isTextTracks = false;
	if (obj.movie.textTracks) {
		obj.isTextTracks = true;
	}

	// Play
	obj.btnPlay.addEventListener('click', function() {
		obj.movie.play();
		obj.btnPlay.className = "px-video-play hide";
		obj.btnPause.className = "px-video-pause px-video-show-inline";
		obj.btnPause.focus();
	}, false);

	// Pause
	obj.btnPause.addEventListener('click', function() {
		obj.movie.pause();
		obj.btnPlay.className = "px-video-play px-video-show-inline";
		obj.btnPause.className = "px-video-pause hide";
		obj.btnPlay.focus();
	}, false);

	// Restart
	obj.btnRestart.addEventListener('click', function() {
		// Move to beginning
		obj.movie.currentTime = 0;

		// Special handling for "manual" captions
		if (!obj.isTextTracks) {
			obj.subcount = 0;
		}

		// Play and ensure the play button is in correct state
		obj.movie.play();
		obj.btnPlay.className = "px-video-play hide";
		obj.btnPause.className = "px-video-pause px-video-show-inline";

	}, false);

	// Rewind
	obj.btnRewind.addEventListener('click', function() {
	    var targetTime = obj.movie.currentTime - obj.seekInterval;
	    if (targetTime < 0) {
	      obj.movie.currentTime = 0;
	    }
	    else {
	      obj.movie.currentTime = targetTime;
	    }
		// Special handling for "manual" captions
		if (!obj.isTextTracks) {
			adjustManualCaptions(obj);
		}
	}, false);

	// Fast forward
	obj.btnForward.addEventListener('click', function() {
	    var targetTime = obj.movie.currentTime + obj.seekInterval;
		if (targetTime > obj.movie.duration) {
			obj.movie.currentTime = obj.movie.duration;
		}
		else {
			obj.movie.currentTime = targetTime;
		}
		// Special handling for "manual" captions
		if (!obj.isTextTracks) {
			adjustManualCaptions(obj);
		}
	}, false);

	// Get the HTML5 range input element and append audio volume adjustment on change
	obj.btnVolume.addEventListener('change', function() {
		obj.movie.volume = parseFloat(this.value / 10);
	}, false);

	// Mute
	obj.btnMute.addEventListener('click', function() {
		if (obj.movie.muted === true) {
			obj.movie.muted = false;
		}
		else {
			obj.movie.muted = true;
		}
	}, false);
 
	obj.btnMute.onkeypress = function(e) {
		if(e.keyCode == 13){ // enter key
			e.preventDefault();
			if (this.checked == true) {
				this.checked = false;
			}
			else {
				this.checked = true;
			}
			if (obj.movie.muted === true) {
				obj.movie.muted = false;
			}
			else {
				obj.movie.muted = true;
			}
		}
	}

	// Duration
	obj.movie.addEventListener("timeupdate", function() {
		obj.secs = parseInt(obj.movie.currentTime % 60);
		obj.mins = parseInt((obj.movie.currentTime / 60) % 60);

		// Ensure it's two digits. For example, 03 rather than 3.
		obj.secs = ("0" + obj.secs).slice(-2);
		obj.mins = ("0" + obj.mins).slice(-2);

		// Render
		obj.duration.innerHTML = obj.mins + ':' + obj.secs;
	}, false);

	// Progress bar
	obj.movie.addEventListener('timeupdate', function() {
		obj.percent = (100 / obj.movie.duration) * obj.movie.currentTime;
		if (obj.percent > 0) {
			obj.progressBar.value = obj.percent;
			obj.progressBarSpan.innerHTML = obj.percent;
		}
	}, false);

	// Skip when clicking progress bar
	obj.progressBar.addEventListener('click', function(e) {
                obj.pos = (e.pageX - $(e.target).offset().left) / this.offsetWidth;
		obj.movie.currentTime = obj.pos * obj.movie.duration;

		// Special handling for "manual" captions
		if (!obj.isTextTracks) {
			adjustManualCaptions(obj);
		}
	});

	// Toggle display of fullscreen button
	obj.fullScreenBtn.addEventListener('click', function() { 
		if (this.checked) {
			launchFullScreen(obj.container);
		} else {
			exitFullScreen();
		}
	}, false);
	obj.fullScreenBtn.onkeypress = function(e) {
		if (e.keyCode == 13){ // enter key
			e.preventDefault();
			if (this.checked == true) {
				this.checked = false;
				exitFullScreen();
			}
			else {
				this.checked = true;
				launchFullScreen(obj.container);
			}
		}
	}

	// Clear captions at end of video
	obj.movie.addEventListener('ended', function() {
		obj.captionsContainer.innerHTML = "";
	});

	// ***
	// Captions
	// ***

	// Toggle display of captions via captions button
	obj.captionsBtn.addEventListener('click', function() { 
		if (this.checked) {
			obj.captionsContainer.className = "px-video-captions show";
		} else {
			obj.captionsContainer.className = "px-video-captions hide";
		}
	  // if fullscreen add fullscreen class
    if (document.fullscreen || document.mozFullScreen || document.webkitIsFullScreen || document.msFullscreenElement) {
      var currClass = obj.captionsContainer.className;
      obj.captionsContainer.className = currClass + ' js-fullscreen-captions';
    }
	}, false);
	obj.captionsBtn.onkeypress = function(e) {
		if (e.keyCode == 13){ // enter key
			e.preventDefault();
			if (this.checked == true) {
				this.checked = false;
			}
			else {
				this.checked = true;
			}
			if (this.checked) {
				obj.captionsContainer.className = "px-video-captions show";
			} else {
				obj.captionsContainer.className = "px-video-captions hide";
			}
		}
	}

	// If no caption file exists, hide container for caption text
	if (!obj.captionExists) {
		obj.captionsContainer.className = "px-video-captions hide";
	}

	// If caption file exists, process captions
	else {

		// Can't use native captioning in the follow browsers:
		if ((obj.browserName==="IE" && obj.browserMajorVersion===10) || 
				(obj.browserName==="IE" && obj.browserMajorVersion===11) || 
				(obj.browserName==="Firefox" && obj.browserMajorVersion>=31) || 
				(obj.browserName==="Chrome" && obj.browserMajorVersion===43) || 
				(obj.browserName==="Safari" && obj.browserMajorVersion>=7)) {
			if (options.debug) {
				console.log("Detected browser unable to play HTML5 captions; using custom captions");
			}
			// set to false so skips to 'manual' captioning
			obj.isTextTracks = false;

			// turn off native caption rendering to avoid double captions [doesn't work in Safari 7; see patch below]
			var track = {};
			var tracks = obj.movie.textTracks;
			for (var j=0; j < tracks.length; j++) {
				track = obj.movie.textTracks[j];
				track.mode = "hidden";
			}
		}

		// Rendering caption tracks - native support required - http://caniuse.com/webvtt
		if (obj.isTextTracks) {
			if (options.debug) {
				console.log("textTracks supported");
			}
			showCaptionContainerAndButton(obj);

			var track = {};
			var tracks = obj.movie.textTracks;
			for (var j=0; j < tracks.length; j++) {
				track = obj.movie.textTracks[j];
				track.mode = "hidden";
				if (track.kind === "captions") {
					track.addEventListener("cuechange",function() {
						if (this.activeCues[0]) {
							if (this.activeCues[0].hasOwnProperty("text") || this.activeCues[0].text !== "") {
								obj.captionsContainer.innerHTML = this.activeCues[0].text;
							}
						}
					},false);
				}
			}
		}
		// Caption tracks not natively supported
		else {
			if (options.debug) {
				console.log("textTracks not supported so rendering captions 'manually'");
			}
			showCaptionContainerAndButton(obj);

			// Render captions from array at appropriate time
			obj.currentCaption = '';
			obj.subcount = 0;
			obj.captions = [];

			obj.movie.addEventListener('timeupdate', function() {
				// Check if the next caption is in the current time range
				if (obj.movie.currentTime.toFixed(1) > video_timecode_min(obj.captions[obj.subcount][0]) &&
					obj.movie.currentTime.toFixed(1) < video_timecode_max(obj.captions[obj.subcount][0])) {
						obj.currentCaption = obj.captions[obj.subcount][1];
				}
				// Is there a next timecode?
				if (obj.movie.currentTime.toFixed(1) > video_timecode_max(obj.captions[obj.subcount][0]) &&
					obj.subcount < (obj.captions.length-1)) {
						obj.subcount++;
				}
				// Render the caption
				obj.captionsContainer.innerHTML = obj.currentCaption;
			}, false);

			if (captionSrc != "") {
				// Create XMLHttpRequest object
				var xhr;
				if (window.XMLHttpRequest) {
					xhr = new XMLHttpRequest();
				} else if (window.ActiveXObject) { // IE8
					xhr = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xhr.onreadystatechange = function() {
					if (xhr.readyState === 4) {
						if (xhr.status === 200) {
							if (options.debug) {
								console.log("xhr = 200");
							}

							obj.captions = [];
							var records = [],
								record,
								req = xhr.responseText;
							records = req.split('\n\n');
							for (var r=0; r < records.length; r++) {
								record = records[r];
								obj.captions[r] = [];
								obj.captions[r] = record.split('\n');
							}
							// Remove first element ("VTT")
							obj.captions.shift();

							if (options.debug) {
								console.log('Successfully loaded the caption file via ajax.');
							}
						} else {
							if (options.debug) {
								console.log('There was a problem loading the caption file via ajax.');
							}
						}
					}
				}
				xhr.open("get", captionSrc, true);
				xhr.send();
			}
		}

		// If Safari 7, removing track from DOM [see 'turn off native caption rendering' above]
		if (obj.browserName === "Safari" && obj.browserMajorVersion === 7) {
			console.log("Safari 7 detected; removing track from DOM");
			var tracks = obj.movie.getElementsByTagName("track");
			obj.movie.removeChild(tracks[0]);
		}

	}

	document.addEventListener("fullscreenchange", function () {
		fullScreenStyles();
	}, false);
	 
	document.addEventListener("mozfullscreenchange", function () {
		fullScreenStyles();
	}, false);
	 
	document.addEventListener("webkitfullscreenchange", function () {
	  fullScreenStyles();
	}, false);
	 
	document.addEventListener("msfullscreenchange", function () {
	 	fullScreenStyles();
	}, false);
};