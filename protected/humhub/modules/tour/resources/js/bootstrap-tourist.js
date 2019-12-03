/* ========================================================================
 *
 * Bootstrap Tourist v0.2.0
 * Copyright FFS 2019
 * @ IGreatlyDislikeJavascript on Github
 *
 * This code is a fork of bootstrap-tour, with a lot of extra features
 * and fixes. You can read about why this fork exists here:
 *
 * https://github.com/sorich87/bootstrap-tour/issues/713
 *
 * The entire purpose of this fork is to start rewriting bootstrap-tour
 * into native ES6 instead of the original coffeescript, and to implement
 * the features and fixes requested in the github repo. Ideally this fork
 * will then be taken back into the main repo and become part of
 * bootstrap-tour again - this is not a fork to create a new plugin!
 *
 * I'm not a JS coder, so suggest you test very carefully and read the
 * docs (comments) below before using.
 *
 * If anyone would like to take on the creation of proper docs for
 * Tourist, please feel free and post here:
 * https://github.com/IGreatlyDislikeJavascript/bootstrap-tourist/issues/15
 *
 * ========================================================================
 * ENTIRELY BASED UPON:
 *
 * bootstrap-tour - v0.2.0
 * http://bootstraptour.com
 * ========================================================================
 * Copyright 2012-2015 Ulrich Sossou
 *
 * ========================================================================
 * Licensed under the MIT License (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://opensource.org/licenses/MIT
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================================
 *
 * Updated for CS by FFS 2018
 *
 * Changes in v0.2.0
 *  - Version update as major fix to bug preventing element: function(){...} feature under BS4/popper.js
 *  - published as release
 *
 * Changes IN v0.12 FROM v0.11:
 *	- note version labelling change in this changelog!
 *  - fixes to the button text change code and better prep for localization (thanks to @DancingDad, @thenewbeat, @bardware)
 *	- fixed css for BS4 progress text to correctly use float-right (thanks to @macroscian, @thenewbeat)
 *
 * Changes from v0.10:
 *  - added support for changing button texts (thanks to @vneri)
 *	- added dummy init() to support drop-in replacement for Tour (thanks to @pau1phi11ips)
 *
 * Changes from 0.9:
 *  - smartPlacement option removed, deprecated
 *  - default params compatibility for IE
 *  - auto progress bar was killed in changes 0.7 -> 0.8 due to Bootstrap sanitizer, this is readded
 *  - major change to manipulation of BS4 popper.js for orphan steps
 *  - change to implementation of backdrop
 *
 * Changes from 0.8:
 *	- The fast fix in v0.7 didn't work for Bootstrap 4. This release is to ensure fully working popovers in BS4. Issue is that the Bootstrap CDN
 *		doesn't actually have the whitelist property, so developing against it is basically useless :(
 *	- Improved BS4 support and template switching. Changed options for framework vs template.
 *
 * Changes from 0.7:
 *  - Fast release to fix breaking change in Bootstrap 3.4.1, fixes this issue: https://github.com/sorich87/bootstrap-tour/issues/723#issuecomment-471107788
 *		Issue is caused by the BS sanitizer, to avoid this reoccurring the "sanitizeWhitelist:" and "sanitizeFunction:" global options added
 *
 * Changes from 0.6:
 *	- Fixed invalid call to debug in _showNextStep()
 *	- Added onPreviouslyEnded() callback: https://github.com/sorich87/bootstrap-tour/issues/720
 *	- Added selector to switch between bootstrap3 and bootstrap4 or custom template, thanks to: https://github.com/sorich87/bootstrap-tour/pull/643
 *
 * Changes from 0.5:
 *	- Added "unfix" for bootstrap selectpicker to revert zindex after step that includes this plugin
 *  - Fixed issue with Bootstrap dialogs. Handling of dialogs is now robust
 *  - Fixed issue with BootstrapDialog plugin: https://nakupanda.github.io/bootstrap3-dialog/ . See notes below for help.
 *  - Improved the background overlay and scroll handling, unnecessary work removed


 ---------


 This fork and code adds following features to Bootstrap Tour

 1. onNext/onPrevious - prevent auto-move to next step, allow .goTo
 2. *** Do not call Tour.init *** - fixed tours with hidden elements on page reload
 3. Dynamically determine step element by function
 4. Only continue tour when reflex element is clicked using reflexOnly
 5. Call onElementUnavailable if step element is missing
 6. Scroll flicker/continual step reload fixed
 7. Magic progress bar and progress text, plus options to customize per step
 8. Prevent user interaction with element using preventInteraction
 9. Wait for arbitrary DOM element to be visible before showing tour step/crapping out due to missing element, using delayOnElement
 10. Handle bootstrap modal dialogs better - autodetect modals or children of modals, and call onModalHidden to handle when user dismisses modal without following tour steps
 11. Automagically fixes drawing issues with Bootstrap Selectpicker (https://github.com/snapappointments/bootstrap-select/)
 12. Call onPreviouslyEnded if tour.start() is called for a tour that has previously ended (see docs)
 13. Switch between Bootstrap 3 or 4 (popover methods and template) automatically using tour options
 14. Added sanitizeWhitelist and sanitizeFunction global options
 15. Added support for changing button texts

 --------------
	1. Control flow from onNext() / onPrevious() options:
 			Returning false from onNext/onPrevious handler will prevent Tour from automatically moving to the next/previous step.
			Tour flow methods (Tour.goTo etc) now also work correctly in onNext/onPrevious.
			Option is available per step or globally:

			var tourSteps = [
								{
									element: "#inputBanana",
									title: "Bananas!",
									content: "Bananas are yellow, except when they're not",
									onNext: function(tour){
										if($('#inputBanana').val() !== "banana")
										{
											// no banana? highlight the banana field
											$('#inputBanana').css("background-color", "red");
											// do not jump to the next tour step!
											return false;
										}
									}
								}
							];

			var Tour=new Tour({
								steps: tourSteps,
								framework: "bootstrap3",	// or "bootstrap4" depending on your version of bootstrap
                buttonTexts:{           // customize or localize button texts
                  nextButton:"go on",
                  endTourButton:"ok it's over",
                }
								onNext: function(tour)
										{
											if(someVar = true)
											{
												// force the tour to jump to slide 3
												tour.goTo(3);
												// Prevent default move to next step - important!
												return false;
											}
										}
							});

 --------------
	2. Do not call Tour.init
			When setting up Tour, do not call Tour.init().
			Call Tour.start() to start/resume the Tour from previous step
			Call Tour.restart() to always start Tour from first step

			Tour.init() was a redundant method that caused conflict with hidden Tour elements.

			As of Tourist v0.11, calling Tour.init() will generate a warning in the console (thanks to @pau1phi11ips).

---------------
	3. Dynamically determine element by function
			Step "element:" option allows element to be determined programmatically. Return a jquery object.
			The following is possible:

			var tourSteps = [
								{
									element: function() { return $(document).find("...something..."); },
									title: "Dynamic",
									content: "Element found by function"
								},
								{
									element: "#static",
									title: "Static",
									content: "Element found by static ID"
								}
							];

---------------
	4. Only continue tour when reflex element is clicked
			Use step option reflexOnly in conjunction with step option reflex to automagically hide the "next" button in the tour, and only continue when the user clicks the element:
			var tourSteps = [
								{
									element: "#myButton",
									reflex: true,
									reflexOnly: true,
									title: "Click it",
									content: "Click to continue, or you're stuck"
								}
							];

----------------
	5. Call function when element is missing
			If the element specified in the step (static or dynamically determined as per feature #3), onElementUnavailable is called.
			Function signature: function(tour, stepNumber) {}
			Option is available at global and per step levels.

			Use it per step to have a step-specific error handler:
				function tourStepBroken(tour, stepNumber)
				{
					alert("Uhoh, the tour broke on the #btnMagic element);
				}

				var tourSteps = [
									{
										element: "#btnMagic",
										onElementUnavailable: tourStepBroken,
										title: "Hold my beer",
										content: "now watch this"
									}
								];


			Use it globally, and optionally override per step, to have a robust and comprehensive error handler:
				function tourBroken(tour, stepNumber)
				{
					alert("The default error handler: tour element is done broke on step number " + stepNumber);
				}

				var tourSteps = [
									{
										element: "#btnThis",
										//onElementUnavailable: COMMENTED OUT, therefore default global handler used
										title: "Some button",
										content: "Some content"
									},
									{
										element: "#btnThat",
										onElementUnavailable: 	function(tour, stepNumber)
																{
																	// override the default global handler for this step only
																	alert("The tour broke on #btnThat step");
																},
										title: "Another button",
										content: "More content"
									}
								];

				var Tour=new Tour({
									steps: tourSteps,
									framework: "bootstrap3",	// or "bootstrap4" depending on your version of bootstrap
									onElementUnavailable: tourBroken, // default "element unavailable" handler for all tour steps
								});

---------------
	6. Scroll flicker / continue reload fixed
			Original Tour constantly reloaded the current tour step on scroll & similar events. This produced flickering, constant reloads and therefore constant calls to all the step function calls.
			This is now fixed. Scrolling the browser window does not cause the tour step to reload.

			IMPORTANT: orphan steps are stuck to the center of the screen. However steps linked to elements ALWAYS stay stuck to their element, even if user scrolls the element & tour popover
						off the screen. This is my personal preference, as original functionality of tour step moving with the scroll even when the element was off the viewport seemed strange.

---------------
	7. Progress bar & progress text:
			With thanks to @macroscian, @thenewbeat for fixes to this code, incorporated in Tourist v0.12

			Use the following options globally or per step to show tour progress:
			showProgressBar - shows a bootstrap progress bar for tour progress at the top of the tour content
			showProgressText - shows a textual progress (N/X, i.e.: 1/24 for slide 1 of 24) in the tour title

			var tourSteps = [
								{
									element: "#inputBanana",
									title: "Bananas!",
									content: "Bananas are yellow, except when they're not",
								},
								{
									element: "#inputOranges",
									title: "Oranges!",
									content: "Oranges are not bananas",
									showProgressBar: false,	// don't show the progress bar on this step only
									showProgressText: false, // don't show the progress text on this step only
								}
							];
			var Tour=new Tour({
								framework: "bootstrap3",	// or "bootstrap4" depending on your version of bootstrap
								steps: tourSteps,
								showProgressBar: true, // default show progress bar
								showProgressText: true, // default show progress text
							});

	7b. Customize the progressbar/progress text:
			In conjunction with 7a, provide the following functions globally or per step to draw your own progressbar/progress text:

			getProgressBarHTML(percent)
			getProgressTextHTML(stepNumber, percent, stepCount)

			These will be called when each step is shown, with the appropriate percentage/step number etc passed to your function. Return an HTML string of a "drawn" progress bar/progress text
			which will be directly inserted into the tour step.

			Example:
			var tourSteps = [
								{
									element: "#inputBanana",
									title: "Bananas!",
									content: "Bananas are yellow, except when they're not",
								},
								{
									element: "#inputOranges",
									title: "Oranges!",
									content: "Oranges are not bananas",
									getProgressBarHTML:	function(percent)
														{
															// override the global progress bar function for this step
															return '<div>You're ' + percent + ' of the way through!</div>';
														}
								}
							];
			var Tour=new Tour({
								steps: tourSteps,
								showProgressBar: true, // default show progress bar
								showProgressText: true, // default show progress text
								getProgressBarHTML: 	function(percent)
														{
															// default progress bar for all steps. Return valid HTML to draw the progress bar you want
															return '<div class="progress"><div class="progress-bar progress-bar-striped" role="progressbar" style="width: ' + percent + '%;"></div></div>';
														},
								getProgressTextHTML: 	function(stepNumber, percent, stepCount)
														{
															// default progress text for all steps
															return 'Slide ' + stepNumber + "/" + stepCount;
														},

							});

----------------
	8. Prevent interaction with element
			Sometimes you want to highlight a DOM element (button, input field) for a tour step, but don't want the user to be able to interact with it.
			Use preventInteraction to stop the user touching the element:

			var tourSteps = [
								{
									element: "#btnMCHammer",
									preventInteraction: true,
									title: "Hammer Time",
									content: "You can't touch this"
								}
							];

----------------
	9. Wait for an element to appear before continuing tour
			Sometimes a tour step element might not be immediately ready because of transition effects etc. This is a specific issue with bootstrap select, which is relatively slow to show the selectpicker
			dropdown after clicking.
			Use delayOnElement to instruct Tour to wait for **ANY** element to appear before showing the step (or crapping out due to missing element). Yes this means the tour step element can be one DOM
			element, but the delay will wait for a completely separate DOM element to appear. This is really useful for hidden divs etc.
			Use in conjunction with onElementUnavailable for robust tour step handling.

			delayOnElement is an object with the following:
							delayOnElement: {
												delayElement: "#waitForMe", // the element to wait to become visible, or the string literal "element" to use the step element
												maxDelay: 2000 // optional milliseconds to wait/timeout for the element, before crapping out. If maxDelay is not specified, this is 2000ms by default,
											}

			var tourSteps = [
								{
									element: "#btnPrettyTransition",
									delayOnElement:	{
														delayElement: "element" // use string literal "element" to wait for this step's element, i.e.: #btnPrettyTransition
													},
									title: "Ages",
									content: "This button takes ages to appear"
								},
								{
									element: "#inputUnrelated",
									delayOnElement:	{
														delayElement: "#divStuff" // wait until DOM element "divStuff" is visible before showing this tour step against DOM element "inputUnrelated"
													},
									title: "Waiting",
									content: "This input is nice, but you only see this step when the other div appears"
								},
								{
									element: "#btnDontForgetThis",
									delayOnElement:	{
														delayElement: "element", // use string literal "element" to wait for this step's element, i.e.: #btnDontForgetThis
														maxDelay: 5000	// wait 5 seconds for it to appear before timing out
													},
									title: "Cool",
									content: "Remember the onElementUnavailable option!",
									onElementUnavailable: 	function(tour, stepNumber)
															{
																// This will be called if btnDontForgetThis is not visible after 5 seconds
																console.log("Well that went badly wrong");
															}
								},
							];

----------------
	10. Trigger when modal closes
			If tour element is a modal, or is a DOM element inside a modal, the element can disappear "at random" if the user dismisses the dialog.
			In this case, onModalHidden global and per step function is called. Only functional when step is not an orphan.
			This is useful if a tour includes a step that launches a modal, and the tour requires the user to take some actions inside the modal before OK'ing it and moving to the next
			tour step.

			Return (int) step number to immediately move to that step
			Return exactly false to not change tour state in any way - this is useful if you need to reshow the modal because some validation failed
			Return anything else to move to the next step

			element === Bootstrap modal, or element parent === bootstrap modal is automatically detected.

			var Tour=new Tour({
								steps: tourSteps,
								framework: "bootstrap3",	// or "bootstrap4" depending on your version of bootstrap
								onModalHidden: 	function(tour, stepNumber)
												{
													console.log("Well damn, this step's element was a modal, or inside a modal, and the modal just done got dismissed y'all. Moving to step 3.");

													// move to step number 3
													return 3;
												},
							});


			var Tour=new Tour({
								steps: tourSteps,
								onModalHidden: 	function(tour, stepNumber)
												{
													if(validateSomeModalContent() == false)
													{
														// The validation failed, user dismissed modal without properly taking actions.
														// Show the modal again
														showModalAgain();

														// Instruct tour to stay on same step
														return false;
													}
													else
													{
														// Content was valid. Return null or do nothing to instruct tour to continue to next step
													}
												},
							});



	10b. Handle Dialogs and BootstrapDialog plugin better https://nakupanda.github.io/bootstrap3-dialog/
			Plugin makes creating dialogs very easy, but it does some extra stuff to the dialogs and dynamically creates/destroys them. This
			causes issues with plugins that want to include a modal dialog in the steps using this plugin.

			To use Tour to highlight an element in a dialog, just use the element ID as you would for any normal step. The dialog will be automatically
			detected and handled.

			To use Tour to highlight an entire dialog, set the step element to the dialog div. Tour will automatically realize this is a dialog, and
			shift the element to use the modal-content div inside the dialog. This makes life friendly, because you can do this:

			<div class="modal" id="myModal" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
					...blah...
					</div>
				</div>
			</div>

			Then use element: myModal in the Tour.


			FOR BOOTSTRAPDIALOG PLUGIN: this plugin creates random UUIDs for the dialog DOM ID. You need to fix the ID to something you know. Do this:

				dlg = new BootstrapDialog.confirm({
													....all the options...
												});

				// BootstrapDialog gives a random GUID ID for dialog. Give it a proper one
				$objModal = dlg.getModal();
				$objModal.attr("id", "myModal");
				dlg.setId("myModal");


			Now you can use element: myModal in the tour, even when the dialog is created by BootstrapDialog plugin.


----------------
	11.	Fix conflict with Bootstrap Selectpicker: https://github.com/snapappointments/bootstrap-select/
		Selectpicker draws a custom select. Tour now automagically finds and adjusts the selectpicker dropdown so that it appears correctly within the tour


----------------
	12.	Call onPreviouslyEnded if tour.start() is called for a tour that has previously ended
		See the following github issue: https://github.com/sorich87/bootstrap-tour/issues/720
		Original behavior for a tour that had previously ended was to call onStart() callback, and then abort without calling onEnd(). This has been altered so
		that calling start() on a tour that has previously ended (cookie step set to end etc) will now ONLY call onPreviouslyEnded().

		This restores the functionality that allows app JS to simply call tour.start() on page load, and the Tour will now only call onStart() / onEnd() when
		the tour really is started or ended.

			var Tour=new Tour({
								steps: [ ..... ],
								framework: "bootstrap3",	// or "bootstrap4" depending on your version of bootstrap
								onPreviouslyEnded: 	function(tour)
													{
														console.log("Looks like this tour has already ended");
													},
							});

			tour.start();

----------------
	13.	Switch between Bootstrap 3 or 4 (popover methods, template) automatically using tour options, or use a custom template
		With thanks to this thread: https://github.com/sorich87/bootstrap-tour/pull/643

		Tour is compatible with bootstrap 3 and 4 if the right template and framework is used for the popover. Bootstrap3 framework compatibility is used by default.

		To select the correct template and framework, use the "framework" global option. Note this option does more than just select a template, it also changes which
		methods are used to manage the Tour popovers to be BS3 or BS4 compatible.

			var Tour=new Tour({
								steps: tourSteps,
								template: null,			// template option is null by default. Tourist will use the appropriate template
														// for the framework version, in this case BS3 as per next option
								framework: "bootstrap3", // can be string literal "bootstrap3" or "bootstrap4"
							});


		To use a custom template, use the "template" global option:

			var Tour=new Tour({
								steps: tourSteps,
								framework: "bootstrap4", // can be string literal "bootstrap3" or "bootstrap4"
								template: '<div class="popover" role="tooltip">....blah....</div>'
							});

		Review the following logic:
			- If template == null, default framework template is used based on whether framework is set to "bootstrap3" or "bootstrap4"
			- If template != null, the specified template is always used
			- If framework option is not literal "bootstrap3" or "bootstrap4", error will occur


		To add additional templates, search the code for "PLACEHOLDER: TEMPLATES LOCATION". This will take you to an array that contains the templates, simply edit
		or add as required.


----------------
	14. Options to manipulate the Bootstrap sanitizer, and fix the sanitizer related breaking change in BS 3.4.x
		BS 3.4.1 added a sanitizer to popover and tooltips - this breaking change strips non-whitelisted DOM elements from popover content, title etc.
		See: https://getbootstrap.com/docs/3.4/javascript/#js-sanitizer and https://blog.getbootstrap.com/2019/02/13/bootstrap-4-3-1-and-3-4-1/

		This Bootstrap change resulted in Tour navigation buttons being killed from the DOM: https://github.com/sorich87/bootstrap-tour/issues/723#issuecomment-471107788

		This has been fixed in code, Tour navigation buttons now appear and work by default.

		To prevent future similar reoccurrences, and also allow the manipulation of the sanitizer "allowed list" for Tours that want to add extra content into
		tour steps, two features added to global options. To understand the purpose and operation of these features, review the following information on the Bootstrap
		sanitizer: https://getbootstrap.com/docs/3.4/javascript/#js-sanitizer

		--IMPORTANT NOTE-- SECURITY RISK: if you do not understand the purpose of the sanitizer, why it exists in bootstrap or how it relates to Tour, do not use these options.

		Global options:

			sanitizeWhitelist:	specify an object that will be merged with the Bootstrap Popover default whitelist. Use the same structure as the default Bootstrap
								whitelist.

			sanitizeFunction:	specify a function that will be used to sanitize Tour content, with the following signature: string function(content).
								Specifying a function for this option will cause sanitizeWhitelist to be ignored.
								Specifying anything other than a function for this option will be ignored, and sanitizeWhitelist will be used

		Examples:

			Allow tour step content to include a button with attributes data-someplugin1="..." and data-somethingelse="...". Allow content to include a selectpicker.
				var Tour=new Tour({
									steps: tourSteps,
									sanitizeWhitelist:	{
															"button"	: ["data-someplugin1", "data-somethingelse"],	// allows <button data-someplugin1="abc", data-somethingelse="xyz">
															"select"	: []											// allows <select>
														}
								});


			Use a custom whitelist function for sanitizing tour steps:
				var Tour=new Tour({
									steps: tourSteps,
									sanitizeFunction:	function(stepContent)
														{
															// Bypass Bootstrap sanitizer using custom function to clean the tour step content.
															// stepContent will contain the content of the step, i.e.: tourSteps[n].content. You must
															// clean this content to prevent XSS and other vulnerabilities. Use your own code or a lib like DOMPurify
															return DOMPurify.sanitize(stepContent);
														}
								});


			Note: if you have complete control over the tour content (i.e.: no risk of XSS or similar attacks), you can use sanitizeFunction to bypass all sanitization
				and use your step content exactly as is by simply returning the content:

				var Tour=new Tour({
									steps: tourSteps,
									sanitizeFunction:	function(stepContent)
														{
															// POTENTIAL SECURITY RISK
															// bypass Bootstrap sanitizer, perform no sanitization, tour step content will be exactly as templated in tourSteps.
															return stepContent;
														}
								});

----------------
	15. Change text for the buttons in the popup (also, preparation for future localization options)
		With thanks to @vneri (https://github.com/IGreatlyDislikeJavascript/bootstrap-tourist/pull/8) for the original change
		With thanks to @DancingDad, @thenewbeat, @bardware for the fixes/updates

		You can now change the text displayed for the buttons used in the tour step popups.	For this, there is a new object you can pass to the options, called "localization".
		This option only applies to the default templates. If you specify your own custom template, the localization.buttonTexts option has no effect on the basis that
		you will make any changes to your own template directly.

			var tour = new Tour({
									framework: "bootstrap3",	// or "bootstrap4" depending on your version of bootstrap
									steps:	[ .....	],
									localization:
									{
										buttonTexts:	{
															prevButton: 'Back',
															nextButton: 'Go',
															pauseButton: 'Wait',
															resumeButton: 'Continue',
															endTourButton: 'Ok, enough'
														}
									}
								});

		You may specify only the labels you want to change. Unspecified labels will remain at their defaults:

			var tour = new Tour({
									localization:
									{
										buttonTexts:	{
															endTourButton: 'Adios muchachos'
														}
									}
								});


 *
 */



(function (window, factory) {
    if (typeof define === 'function' && define.amd) {
        return define(['jquery'], function (jQuery) {
            return window.Tour = factory(jQuery);
        });
    } else if (typeof exports === 'object') {
        return module.exports = factory(require('jquery'));
    } else {
        return window.Tour = factory(window.jQuery);
    }
})(window, function ($) {

    var Tour, document, objTemplates, objTemplatesButtonTexts;

    document = window.document;

    Tour = (function () {

        function Tour(options)
        {
            var storage;
            try
            {
                storage = window.localStorage;
            }
            catch (error)
            {
                storage = false;
            }

            // CUSTOMIZABLE TEXTS FOR BUTTONS
            // set defaults. We could of course add this to the $.extend({..localization: {} ...}) directly below.
            // However this is configured here, prior to the $.extend of options below, to enable a potential
            // future option of loading localization externally perhaps using $.getScript() etc.
            //
            // Note that these only affect the "default" templates (see objTemplates in this func below). The assumption is
            // that if user creates a tour with a custom template, they will name the buttons as required. We could force the
            // naming even in custom templates by identifying buttons in templates with data-role="...", but it seems more logical
            // NOT to do that...
            //
            // Finally, it's simple to allow different localization/button texts per tour step. To do this, alter the $.extend in
            // Tour.prototype.getStep() and subsequent code to load the per-step localization, identify the buttons by data-role, and
            // make the appropriate changes. That seems like a very niche requirement so it's not implemented here.
            objTemplatesButtonTexts =	{
                prevButton: "Prev",
                nextButton: "Next",
                pauseButton: "Pause",
                resumeButton: "Resume",
                endTourButton: "End Tour"
            };


            // take default options and overwrite with this tour options
            this._options = $.extend(true,
                {
                    name: 'tour',
                    steps: [],
                    container: 'body',
                    autoscroll: true,
                    keyboard: true,
                    storage: storage,
                    debug: false,
                    backdrop: false,
                    backdropContainer: 'body',
                    backdropPadding: 0,
                    redirect: true,
                    orphan: false,
                    duration: false,
                    delay: false,
                    basePath: '',
                    template: null,
                    localization:	{
                        buttonTexts: objTemplatesButtonTexts
                    },
                    framework: 'bootstrap3',
                    sanitizeWhitelist: [],
                    sanitizeFunction: null,// function(content) return sanitizedContent
                    showProgressBar: true,
                    showProgressText: true,
                    getProgressBarHTML: null,//function(percent) {},
                    getProgressTextHTML: null,//function(stepNumber, percent, stepCount) {},
                    afterSetState: function (key, value) {},
                    afterGetState: function (key, value) {},
                    afterRemoveState: function (key) {},
                    onStart: function (tour) {},
                    onEnd: function (tour) {},
                    onShow: function (tour) {},
                    onShown: function (tour) {},
                    onHide: function (tour) {},
                    onHidden: function (tour) {},
                    onNext: function (tour) {},
                    onPrev: function (tour) {},
                    onPause: function (tour, duration) {},
                    onResume: function (tour, duration) {},
                    onRedirectError: function (tour) {},
                    onElementUnavailable: null, // function (tour, stepNumber) {},
                    onPreviouslyEnded: null, // function (tour) {},
                    onModalHidden: null, // function(tour, stepNumber) {}
                }, options);


            if(this._options.framework !== "bootstrap3" && this._options.framework !== "bootstrap4")
            {
                this._debug('Invalid framework specified: ' + this._options.framework);
                throw "Bootstrap Tourist: Invalid framework specified";
            }


            // create the templates

            // SEARCH PLACEHOLDER: TEMPLATES LOCATION
            objTemplates = {
                bootstrap3	: '<div class="popover" role="tooltip"> <div class="arrow"></div> <h3 class="popover-title"></h3> <div class="popover-content"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-default" data-role="prev">&laquo; ' + this._options.localization.buttonTexts.prevButton + '</button> <button class="btn btn-sm btn-default" data-role="next">' + this._options.localization.buttonTexts.nextButton + ' &raquo;</button> <button class="btn btn-sm btn-default" data-role="pause-resume" data-pause-text="' + this._options.localization.buttonTexts.pauseButton + '" data-resume-text="' + this._options.localization.buttonTexts.resumeButton + '">' + this._options.localization.buttonTexts.pauseButton + '</button> </div> <button class="btn btn-sm btn-default" data-role="end">' + this._options.localization.buttonTexts.endTourButton + '</button> </div> </div>',
                bootstrap4	: '<div class="popover" role="tooltip"> <div class="arrow"></div> <h3 class="popover-header"></h3> <div class="popover-body"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-outline-secondary" data-role="prev">&laquo; ' + this._options.localization.buttonTexts.prevButton + '</button> <button class="btn btn-sm btn-outline-secondary" data-role="next">' + this._options.localization.buttonTexts.nextButton + ' &raquo;</button> <button class="btn btn-sm btn-outline-secondary" data-role="pause-resume" data-pause-text="' + this._options.localization.buttonTexts.pauseButton + '" data-resume-text="' + this._options.localization.buttonTexts.resumeButton + '">' + this._options.localization.buttonTexts.pauseButton + '</button> </div> <button class="btn btn-sm btn-outline-secondary" data-role="end">' + this._options.localization.buttonTexts.endTourButton + '</button> </div> </div>',
            };

            // template option is default null. If not null after extend, caller has set a custom template, so don't touch it
            if(this._options.template === null)
            {
                // no custom template, so choose the template based on the framework
                if(objTemplates[this._options.framework] != null && objTemplates[this._options.framework] != undefined)
                {
                    // there's a default template for the framework type specified in the options
                    this._options.template = objTemplates[this._options.framework];

                    this._debug('Using framework template: ' + this._options.framework);
                }
                else
                {
                    this._debug('Warning: ' + this._options.framework + ' specified for template (no template option set), but framework is unknown. Tour will not work!');
                }
            }
            else
            {
                this._debug('Using custom template');
            }

            if(typeof(this._options.sanitizeFunction) == "function")
            {
                this._debug("Using custom sanitize function in place of bootstrap - security implications, be careful");
            }
            else
            {
                this._options.sanitizeFunction = null;

                this._debug("Extending Bootstrap sanitize options");

                // no custom function, add our own
                // bootstrap 3.4.1 has whitelist functionality that strips tags from title, content etc of popovers and tooltips. Need to
                // add buttons to the whitelist otherwise the navigation buttons will be stripped from the popover content.
                // See issue: https://github.com/sorich87/bootstrap-tour/issues/723#issuecomment-471107788
                //
                // ** UPDATE: BS3 and BS4 have the whitelist function. However:
                //		BS3 uses $.fn.popover.Constructor.DEFAULTS.whiteList
                //		BS4 uses $.fn.popover.Constructor.Default.whiteList
                //	Even better, the CDN version of BS4 doesn't seem to include a whitelist property at all, which utterly screwed the first attempt at implementing
                // this, making it seem like my fix was working when in fact it was utterly broken.
                var defaultWhiteList = [];

                if(this._options.framework == "bootstrap4" && $.fn.popover.Constructor.Default.whiteList !== undefined)
                {
                    defaultWhiteList = $.fn.popover.Constructor.Default.whiteList;
                }

                if(this._options.framework == "bootstrap3" && $.fn.popover.Constructor.DEFAULTS.whiteList !== undefined)
                {
                    defaultWhiteList = $.fn.popover.Constructor.DEFAULTS.whiteList;
                }

                var whiteListAdditions = {
                    "button":	["data-role", "style"],
                    "img":		["style"],
                    "div":		["style"]
                };


                // whitelist is object with properties that are arrays. Need to merge "manually", as using $.extend with recursion will still overwrite the arrays . Try
                // var whiteList = $.extend(true, {}, defaultWhiteList, whiteListAdditions, this._options.sanitizeWhitelist);
                // and inspect the img property to see the issue - the default whitelist "src" (array elem 0) is overwritten with additions "style"

                // clone the default whitelist object first, otherwise we change the defaults for all of bootstrap!
                var whiteList = $.extend(true, {}, defaultWhiteList);

                // iterate the additions, and merge them into the defaults. We could just hammer them in manually but this is a little more expandable for the future
                $.each(whiteListAdditions,	function( index, value )
                {
                    if(whiteList[index] == undefined)
                    {
                        whiteList[index] = [];
                    }

                    $.merge(whiteList[index], value);
                });

                // and now do the same with the user specified whitelist in tour options
                $.each(this._options.sanitizeWhitelist,	function( index, value )
                {
                    if(whiteList[index] == undefined)
                    {
                        whiteList[index] = [];
                    }

                    $.merge(whiteList[index], value);
                });

                // save the merged whitelist back to the options, this is used by popover initialization when each step is shown
                this._options.sanitizeWhitelist = whiteList;
            }

            this._current = null;
            this.backdrops = [];

            return this;
        }

        Tour.prototype.addSteps = function (steps) {
            var j,
                len,
                step;
            for (j = 0, len = steps.length; j < len; j++) {
                step = steps[j];
                this.addStep(step);
            }
            return this;
        };

        Tour.prototype.addStep = function (step) {
            this._options.steps.push(step);
            return this;
        };

        Tour.prototype.getStepCount = function() {
            return this._options.steps.length;
        };

        Tour.prototype.getStep = function (i) {
            if (this._options.steps[i] != null) {

                if(typeof(this._options.steps[i].element) == "function")
                {
                    this._options.steps[i].element = this._options.steps[i].element();
                }

                // Set per step options: take the global options then override with this step's options.
                this._options.steps[i] =  $.extend(true,
                    {
                        id: "step-" + i,
                        path: '',
                        host: '',
                        placement: 'right',
                        title: '',
                        content: '<p></p>',
                        next: i === this._options.steps.length - 1 ? -1 : i + 1,
                        prev: i - 1,
                        animation: true,
                        container: this._options.container,
                        autoscroll: this._options.autoscroll,
                        backdrop: this._options.backdrop,
                        backdropContainer: this._options.backdropContainer,
                        backdropPadding: this._options.backdropPadding,
                        redirect: this._options.redirect,
                        reflexElement: this._options.steps[i].element,
                        preventInteraction: false,
                        orphan: this._options.orphan,
                        duration: this._options.duration,
                        delay: this._options.delay,
                        template: this._options.template,
                        showProgressBar: this._options.showProgressBar,
                        showProgressText: this._options.showProgressText,
                        getProgressBarHTML: this._options.getProgressBarHTML,
                        getProgressTextHTML: this._options.getProgressTextHTML,
                        onShow: this._options.onShow,
                        onShown: this._options.onShown,
                        onHide: this._options.onHide,
                        onHidden: this._options.onHidden,
                        onNext: this._options.onNext,
                        onPrev: this._options.onPrev,
                        onPause: this._options.onPause,
                        onResume: this._options.onResume,
                        onRedirectError: this._options.onRedirectError,
                        onElementUnavailable: this._options.onElementUnavailable,
                        onModalHidden: this._options.onModalHidden,
                        internalFlags:	{
                            elementModal: null,					// will store the jq modal object for a step
                            elementModalOriginal: null,			// will store the original step.element string in steps that use a modal
                            elementBootstrapSelectpicker: null	// will store jq bootstrap select picker object
                        }
                    },
                    this._options.steps[i]
                );

                return this._options.steps[i];
            }
        };

        // step flags are used to remember specific internal step data across a tour
        Tour.prototype._setStepFlag = function(stepNumber, flagName, value)
        {
            if(this._options.steps[stepNumber] != null)
            {
                this._options.steps[stepNumber].internalFlags[flagName] = value;
            }
        };

        Tour.prototype._getStepFlag = function(stepNumber, flagName)
        {
            if(this._options.steps[stepNumber] != null)
            {
                return this._options.steps[stepNumber].internalFlags[flagName];
            }
        };


        //=======================================================================================================================================
        // Initiate tour and movement between steps

        Tour.prototype.init = function ()
        {
            console.log('You should remove Tour.init() from your code. It\'s not required with Bootstrap Tourist');
        }

        Tour.prototype.start = function ()
        {
            // Test if this tour has previously ended, and start() was called
            if(this.ended())
            {
                if(this._options.onPreviouslyEnded != null && typeof(this._options.onPreviouslyEnded) == "function")
                {
                    this._debug('Tour previously ended, exiting. Call tour.restart() to force restart. Firing onPreviouslyEnded()');
                    this._options.onPreviouslyEnded(this);
                }
                else
                {
                    this._debug('Tour previously ended, exiting. Call tour.restart() to force restart');
                }

                return this;
            }

            // Call setCurrentStep() without params to start the tour using whatever step is recorded in localstorage. If no step recorded, tour starts
            // from first step. This provides the "resume tour" functionality.
            // Tour restart() simply removes the step from local storage
            this.setCurrentStep();

            this._initMouseNavigation();
            this._initKeyboardNavigation();

            // BS3: resize event must destroy and recreate both popper and background to ensure correct positioning
            // BS4: resize must destroy and recreate background, but popper.js handles popper positioning.
            // TODO: currently we destroy and recreate for both BS3 and BS4. Improvement could be to reposition backdrop overlay only when using BS4
            var _this = this;
            $(window).on("resize.tour-" + _this._options.name,	function()
                {
                    _this.reshowCurrentStep();
                }
            );


            // Note: this call is not required, but remains here in case any future forkers want to reinstate the code that moves a non-orphan popover
            // when window is scrolled. Note that simply uncommenting this will not reinstate the code - _showPopoverAndOverlay automatically detects
            // if the current step is visible and will not reshow it. Therefore, to fully reinstate the "redraw on scroll" code, uncomment this and
            // also add appropriate code (to move popover & overlay) to the end of showPopover()
//			this._onScroll((function (_this)
//							{
//								return function ()
//								{
//									return _this._showPopoverAndOverlay(_this._current);
//								};
//							}
//						));

            // start the tour - see if user provided onStart function, and if it returns a promise, obey that promise before calling showStep
            var promise = this._makePromise(this._options.onStart != null ? this._options.onStart(this) : void 0);
            this._callOnPromiseDone(promise, this.showStep, this._current);

            return this;
        };

        Tour.prototype.next = function () {
            var promise;
            promise = this.hideStep();
            return this._callOnPromiseDone(promise, this._showNextStep);
        };

        Tour.prototype.prev = function () {
            var promise;
            promise = this.hideStep();
            return this._callOnPromiseDone(promise, this._showPrevStep);
        };

        Tour.prototype.goTo = function (i) {
            var promise;
            this._debug("goTo step " + i);
            promise = this.hideStep();
            return this._callOnPromiseDone(promise, this.showStep, i);
        };

        Tour.prototype.end = function () {
            var endHelper,
                promise;
            endHelper = (function (_this) {
                return function (e) {
                    $(document).off("click.tour-" + _this._options.name);
                    $(document).off("keyup.tour-" + _this._options.name);
                    $(window).off("resize.tour-" + _this._options.name);
                    $(window).off("scroll.tour-" + _this._options.name);
                    _this._setState('end', 'yes');
                    _this._clearTimer();

                    if (_this._options.onEnd != null)
                    {
                        return _this._options.onEnd(_this);
                    }
                };
            })(this);
            promise = this.hideStep();
            return this._callOnPromiseDone(promise, endHelper);
        };

        Tour.prototype.ended = function () {
            return this._getState('end') == 'yes';
        };

        Tour.prototype.restart = function ()
        {
            this._removeState('current_step');
            this._removeState('end');
            this._removeState('redirect_to');
            return this.start();
        };

        Tour.prototype.pause = function () {
            var step;
            step = this.getStep(this._current);
            if (!(step && step.duration)) {
                return this;
            }
            this._paused = true;
            this._duration -= new Date().getTime() - this._start;
            window.clearTimeout(this._timer);
            this._debug("Paused/Stopped step " + (this._current + 1) + " timer (" + this._duration + " remaining).");
            if (step.onPause != null) {
                return step.onPause(this, this._duration);
            }
        };

        Tour.prototype.resume = function () {
            var step;
            step = this.getStep(this._current);
            if (!(step && step.duration)) {
                return this;
            }
            this._paused = false;
            this._start = new Date().getTime();
            this._duration = this._duration || step.duration;
            this._timer = window.setTimeout((function (_this) {
                return function () {
                    if (_this._isLast()) {
                        return _this.next();
                    } else {
                        return _this.end();
                    }
                };
            })(this), this._duration);
            this._debug("Started step " + (this._current + 1) + " timer with duration " + this._duration);
            if ((step.onResume != null) && this._duration !== step.duration) {
                return step.onResume(this, this._duration);
            }
        };

        // fully closes and reopens the current step, triggering all callbacks etc
        Tour.prototype.reshowCurrentStep = function()
        {
            this._debug("Reshowing current step " + this.getCurrentStepIndex());
            var promise;
            promise = this.hideStep();
            return this._callOnPromiseDone(promise, this.showStep, this._current);
        };


        //=======================================================================================================================================


        // hides current step
        Tour.prototype.hideStep = function ()
        {
            var hideDelay,
                hideStepHelper,
                promise,
                step;

            step = this.getStep(this.getCurrentStepIndex());

            if (!step)
            {
                return;
            }

            this._clearTimer();
            promise = this._makePromise(step.onHide != null ? step.onHide(this, this.getCurrentStepIndex()) : void 0);

            hideStepHelper = (function (_this)
            {
                return function (e)
                {
                    var $element;

                    $element = $(step.element);
                    if (!($element.data('bs.popover') || $element.data('popover')))
                    {
                        $element = $('body');
                    }

                    if(_this._options.framework == "bootstrap3")
                    {
                        $element.popover('destroy');
                    }

                    if(_this._options.framework == "bootstrap4")
                    {
                        $element.popover('dispose');
                    }

                    $element.removeClass("tour-" + _this._options.name + "-element tour-" + _this._options.name + "-" + _this.getCurrentStepIndex() + "-element").removeData('bs.popover');

                    if (step.reflex)
                    {
                        $(step.reflexElement).removeClass('tour-step-element-reflex').off((_this._reflexEvent(step.reflex)) + ".tour-" + _this._options.name);
                    }

                    _this._hideOverlayElement(step);
                    _this._unfixBootstrapSelectPickerZindex(step);

                    // If this step was pointed at a modal, revert changes to the step.element. See the notes in showStep for explanation
                    var tmpModalOriginalElement = _this._getStepFlag(_this.getCurrentStepIndex(), "elementModalOriginal");
                    if(tmpModalOriginalElement != null)
                    {
                        _this._setStepFlag(_this.getCurrentStepIndex(), "elementModalOriginal", null);
                        step.element = tmpModalOriginalElement;
                    }

                    if (step.onHidden != null)
                    {
                        return step.onHidden(_this);
                    }
                };
            })(this);

            hideDelay = step.delay.hide || step.delay;
            if ({}
                .toString.call(hideDelay) === '[object Number]' && hideDelay > 0) {
                this._debug("Wait " + hideDelay + " milliseconds to hide the step " + (this._current + 1));
                window.setTimeout((function (_this) {
                    return function () {
                        return _this._callOnPromiseDone(promise, hideStepHelper);
                    };
                })(this), hideDelay);
            } else {
                this._callOnPromiseDone(promise, hideStepHelper);
            }
            return promise;
        };

        // loads all required step info and prepares to show
        Tour.prototype.showStep = function (i) {
            var path,
                promise,
                showDelay,
                showStepHelper,
                skipToPrevious,
                step,
                $element;


            if(this.ended())
            {
                // Note: see feature addition #12 and "onPreviouslyEnded" option to understand when this._options.onEnd is called vs this._options.onPreviouslyEnded()
                this._debug('Tour ended, showStep prevented.');
                if(this._options.onEnd != null)
                {
                    this._options.onEnd(this);
                }

                return this;
            }

            step = this.getStep(i);
            if (!step) {
                return;
            }

            skipToPrevious = i < this._current;
            promise = this._makePromise(step.onShow != null ? step.onShow(this, i) : void 0);
            this.setCurrentStep(i);

            path = (function () {
                switch ({}
                    .toString.call(step.path)) {
                    case '[object Function]':
                        return step.path();
                    case '[object String]':
                        return this._options.basePath + step.path;
                    default:
                        return step.path;
                }
            }).call(this);


            if (step.redirect && this._isRedirect(step.host, path, document.location)) {
                this._redirect(step, i, path);
                if (!this._isJustPathHashDifferent(step.host, path, document.location)) {
                    return;
                }
            }

            // will be set to element <div class="modal"> if modal in use
            $modalObject = null;

            // is element a modal?
            if(step.orphan === false && ($(step.element).hasClass("modal") || $(step.element).data('bs.modal')))
            {
                // element is exactly the modal div
                $modalObject = $(step.element);

                // This is a hack solution. Original Tour uses step.element in multiple places and converts to jquery object as needed. This func uses $element,
                // but multiple other funcs simply use $(step.element) instead - keeping the original string element id in the step data and using jquery as needed.
                // This creates problems with dialogs, especially BootStrap Dialog plugin - in code terms, the dialog is everything from <div class="modal-dialog">,
                // but the actual visible positioned part of the dialog is <div class="modal-dialog"><div class="modal-content">. The tour must attach the popover to
                // modal-content div, NOT the modal-dialog div. But most coders + dialog plugins put the id on the modal-dialog div.
                // So for display, we must adjust the step element to point at modal-content under the modal-dialog div. However if we change the step.element
                // permanently to the modal-content (by changing tour._options.steps), this won't work if the step is reshown (plugin destroys modal, meaning
                // the element jq object is no longer valid) and could potentially screw up other
                // parts of a tour that have dialogs. So instead we record the original element used for this step that involves modals, change the step.element
                // to the modal-content div, then set it back when the step is hidden again.
                //
                // This is ONLY done because it's too difficult to unpick all the original tour code that uses step.element directly.
                this._setStepFlag(this.getCurrentStepIndex(), "elementModalOriginal", step.element);

                // fix the tour element, the actual visible offset comes from modal > modal-dialog > modal-content and step.element is used to calc this offset & size
                step.element = $(step.element).find(".modal-content:first");
            }

            $element = $(step.element);

            // is element inside a modal?
            if($modalObject === null && $element.parents(".modal:first").length)
            {
                // find the parent modal div
                $modalObject = $element.parents(".modal:first");
            }

            if($modalObject && $modalObject.length > 0)
            {
                this._debug("Modal identified, onModalHidden callback available");

                // store the modal element for other calls
                this._setStepFlag(i, "elementModal", $modalObject)

                // modal in use, add callback
                var funcModalHelper = 	function(_this, $_modalObject)
                {
                    return function ()
                    {
                        _this._debug("Modal close triggered");

                        if(typeof(step.onModalHidden) == "function")
                        {
                            // if step onModalHidden returns false, do nothing. returns int, move to the step specified.
                            // Otherwise continue regular next/end functionality
                            var rslt;

                            rslt = step.onModalHidden(_this, i);

                            if(rslt === false)
                            {
                                _this._debug("onModalHidden returned exactly false, tour step unchanged");
                                return;
                            }

                            if(Number.isInteger(rslt))
                            {
                                _this._debug("onModalHidden returned int, tour moving to step " + rslt + 1);

                                $_modalObject.off("hidden.bs.modal", funcModalHelper);
                                return _this.goTo(rslt);
                            }

                            _this._debug("onModalHidden did not return false or int, continuing tour");
                        }

                        $_modalObject.off("hidden.bs.modal", funcModalHelper);

                        if (_this._isLast())
                        {
                            return _this.next();
                        }
                        else
                        {
                            return _this.end();
                        }
                    };
                }(this, $modalObject);

                $modalObject.off("hidden.bs.modal", funcModalHelper).on("hidden.bs.modal", funcModalHelper);
            }

            // Helper function to actually show the popover using _showPopoverAndOverlay
            showStepHelper = (function (_this) {
                return function (e) {
                    if (_this._isOrphan(step)) {
                        if (step.orphan === false)
                        {
                            _this._debug("Skip the orphan step " + (_this._current + 1) + ".\nOrphan option is false and the element " + step.element + " does not exist or is hidden.");

                            if(typeof(step.onElementUnavailable) == "function")
                            {
                                _this._debug("Calling onElementUnavailable callback");
                                step.onElementUnavailable(_this, _this._current);
                            }

                            if (skipToPrevious) {
                                _this._showPrevStep(true);
                            } else {
                                _this._showNextStep(true);
                            }
                            return;
                        }
                        _this._debug("Show the orphan step " + (_this._current + 1) + ". Orphans option is true.");
                    }

                    //console.log(step);

                    if (step.autoscroll && !_this._isOrphan(step))
                    {
                        _this._scrollIntoView(i);
                    }
                    else
                    {
                        _this._showPopoverAndOverlay(i);
                    }

                    if (step.duration) {
                        return _this.resume();
                    }
                };
            })(this);


            // delay in millisec specified in step options
            showDelay = step.delay.show || step.delay;
            if ({}
                .toString.call(showDelay) === '[object Number]' && showDelay > 0) {
                this._debug("Wait " + showDelay + " milliseconds to show the step " + (this._current + 1));
                window.setTimeout((function (_this) {
                    return function () {
                        return _this._callOnPromiseDone(promise, showStepHelper);
                    };
                })(this), showDelay);
            }
            else
            {
                if(step.delayOnElement)
                {
                    // delay by element existence or max delay (default 2 sec)
                    var $delayElement = null;
                    var delayFunc = null;
                    var _this = this;

                    var revalidateDelayElement = function() {
                        if(typeof(step.delayOnElement.delayElement) == "function")
                            return step.delayOnElement.delayElement();
                        else if(step.delayOnElement.delayElement == "element")
                            return $(step.element);
                        else
                            return $(step.delayOnElement.delayElement);
                    };
                    var $delayElement = revalidateDelayElement();

                    var delayElementLog = $delayElement.length > 0 ? $delayElement[0].tagName : step.delayOnElement.delayElement;

                    var delayMax = (step.delayOnElement.maxDelay ? step.delayOnElement.maxDelay : 2000);
                    this._debug("Wait for element " + delayElementLog + " visible or max " + delayMax + " milliseconds to show the step " + (this._current + 1));

                    delayFunc = window.setInterval(	function()
                    {
                        _this._debug("Wait for element " + delayElementLog + ": checking...");
                        if($delayElement.length === 0) {
                            $delayElement = revalidateDelayElement();
                        }
                        if($delayElement.is(':visible'))
                        {
                            _this._debug("Wait for element " + delayElementLog + ": found, showing step");
                            window.clearInterval(delayFunc);
                            delayFunc = null;
                            return _this._callOnPromiseDone(promise, showStepHelper);
                        }
                    }, 250);

                    //	set max delay to greater than default interval check for element appearance
                    if(delayMax < 250)
                        delayMax = 251;

                    // Set timer to kill the setInterval call after max delay time expires
                    window.setTimeout(	function ()
                    {
                        if(delayFunc)
                        {
                            _this._debug("Wait for element " + delayElementLog + ": max timeout reached without element found");
                            window.clearInterval(delayFunc);

                            // showStepHelper will handle broken/missing/invisible element
                            return _this._callOnPromiseDone(promise, showStepHelper);
                        }
                    }, delayMax);
                }
                else
                {
                    // no delay by milliseconds or delay by time
                    this._callOnPromiseDone(promise, showStepHelper);
                }
            }

            return promise;
        };

        Tour.prototype.getCurrentStepIndex = function () {
            return this._current;
        };

        Tour.prototype.setCurrentStep = function (value) {
            if (value != null)
            {
                this._current = value;
                this._setState('current_step', value);
            }
            else
            {
                this._current = this._getState('current_step');
                this._current = this._current === null ? 0 : parseInt(this._current, 10);
            }

            return this;
        };


        Tour.prototype._setState = function (key, value) {
            var e,
                keyName;
            if (this._options.storage) {
                keyName = this._options.name + "_" + key;
                try {
                    this._options.storage.setItem(keyName, value);
                } catch (error) {
                    e = error;
                    if (e.code === DOMException.QUOTA_EXCEEDED_ERR) {
                        this._debug('LocalStorage quota exceeded. State storage failed.');
                    }
                }
                return this._options.afterSetState(keyName, value);
            } else {
                if (this._state == null) {
                    this._state = {};
                }
                return this._state[key] = value;
            }
        };

        Tour.prototype._removeState = function (key) {
            var keyName;
            if (this._options.storage) {
                keyName = this._options.name + "_" + key;
                this._options.storage.removeItem(keyName);
                return this._options.afterRemoveState(keyName);
            } else {
                if (this._state != null) {
                    return delete this._state[key];
                }
            }
        };

        Tour.prototype._getState = function (key) {
            var keyName,
                value;
            if (this._options.storage) {
                keyName = this._options.name + "_" + key;
                value = this._options.storage.getItem(keyName);
            } else {
                if (this._state != null) {
                    value = this._state[key];
                }
            }
            if (value === void 0 || value === 'null') {
                value = null;
            }
            this._options.afterGetState(key, value);
            return value;
        };

        Tour.prototype._showNextStep = function (skipOrphan) {
            var promise,
                showNextStepHelper,
                step;

            var skipOrphan = skipOrphan || false;

            showNextStepHelper = (function (_this) {
                return function (e) {
                    return _this.showStep(_this._current + 1);
                };
            })(this);

            promise = void 0;

            step = this.getStep(this._current);

            // only call the onNext handler if this is a click and NOT an orphan skip due to missing element
            if (skipOrphan === false &&  step.onNext != null)
            {
                rslt = step.onNext(this);

                if(rslt === false)
                {
                    this._debug("onNext callback returned false, preventing move to next step");
                    return this.showStep(this._current);
                }

                promise = this._makePromise(rslt);
            }

            return this._callOnPromiseDone(promise, showNextStepHelper);
        };

        Tour.prototype._showPrevStep = function (skipOrphan) {
            var promise,
                showPrevStepHelper,
                step;

            var skipOrphan = skipOrphan || false;

            showPrevStepHelper = (function (_this) {
                return function (e) {
                    return _this.showStep(step.prev);
                };
            })(this);

            promise = void 0;
            step = this.getStep(this._current);

            // only call the onPrev handler if this is a click and NOT an orphan skip due to missing element
            if (skipOrphan === false && step.onPrev != null)
            {
                rslt = step.onPrev(this);

                if(rslt === false)
                {
                    this._debug("onPrev callback returned false, preventing move to previous step");
                    return this.showStep(this._current);
                }

                promise = this._makePromise(rslt);
            }

            return this._callOnPromiseDone(promise, showPrevStepHelper);
        };

        Tour.prototype._debug = function (text) {
            if (this._options.debug) {
                return window.console.log("[ Bootstrap Tour: '" + this._options.name + "' ] " + text);
            }
        };

        Tour.prototype._isRedirect = function (host, path, location) {
            var currentPath;
            if ((host != null) && host !== '' && (({}
                .toString.call(host) === '[object RegExp]' && !host.test(location.origin)) || ({}
                .toString.call(host) === '[object String]' && this._isHostDifferent(host, location)))) {
                return true;
            }
            currentPath = [location.pathname, location.search, location.hash].join('');
            return (path != null) && path !== '' && (({}
                .toString.call(path) === '[object RegExp]' && !path.test(currentPath)) || ({}
                .toString.call(path) === '[object String]' && this._isPathDifferent(path, currentPath)));
        };

        Tour.prototype._isHostDifferent = function (host, location) {
            switch ({}
                .toString.call(host)) {
                case '[object RegExp]':
                    return !host.test(location.origin);
                case '[object String]':
                    return this._getProtocol(host) !== this._getProtocol(location.href) || this._getHost(host) !== this._getHost(location.href);
                default:
                    return true;
            }
        };

        Tour.prototype._isPathDifferent = function (path, currentPath) {
            return this._getPath(path) !== this._getPath(currentPath) || !this._equal(this._getQuery(path), this._getQuery(currentPath)) || !this._equal(this._getHash(path), this._getHash(currentPath));
        };

        Tour.prototype._isJustPathHashDifferent = function (host, path, location) {
            var currentPath;
            if ((host != null) && host !== '') {
                if (this._isHostDifferent(host, location)) {
                    return false;
                }
            }
            currentPath = [location.pathname, location.search, location.hash].join('');
            if ({}
                .toString.call(path) === '[object String]') {
                return this._getPath(path) === this._getPath(currentPath) && this._equal(this._getQuery(path), this._getQuery(currentPath)) && !this._equal(this._getHash(path), this._getHash(currentPath));
            }
            return false;
        };

        Tour.prototype._redirect = function (step, i, path) {
            var href;
            if ($.isFunction(step.redirect)) {
                return step.redirect.call(this, path);
            } else {
                href = {}
                    .toString.call(step.host) === '[object String]' ? "" + step.host + path : path;
                this._debug("Redirect to " + href);
                if (this._getState('redirect_to') === ("" + i)) {
                    this._debug("Error redirection loop to " + path);
                    this._removeState('redirect_to');
                    if (step.onRedirectError != null) {
                        return step.onRedirectError(this);
                    }
                } else {
                    this._setState('redirect_to', "" + i);
                    return document.location.href = href;
                }
            }
        };

        // Tests if the step is orphan
        // Step can be "orphan" (unattached to any element) if specifically set as such in tour step options, or with an invalid/hidden element
        Tour.prototype._isOrphan = function (step)
        {
            var isOrphan = (step.orphan == true) || (step.element == null) || !$(step.element).length || $(step.element).is(':hidden') && ($(step.element)[0].namespaceURI !== 'http://www.w3.org/2000/svg');

            return isOrphan;
        };


        Tour.prototype._isLast = function () {
            return this._current < this._options.steps.length - 1;
        };

        // wraps the calls to show the tour step in a popover and the background overlay.
        // Note this is ALSO called by scroll event handler. Individual funcs called will determine whether redraws etc are required.
        Tour.prototype._showPopoverAndOverlay = function (i)
        {
            var step;

            if (this.getCurrentStepIndex() !== i || this.ended()) {
                return;
            }
            step = this.getStep(i);

            if (step.backdrop)
            {
                this._showOverlayElements(step);
            }

            this._fixBootstrapSelectPickerZindex(step);

            // Ensure this is called last, to allow preceeding calls to check whether current step popover is already visible.
            // This is required because this func is called by scroll event. showPopover creates the actual popover with
            // current step index as a class. Therefore all preceeding funcs can check if they are being called because of a
            // scroll event (popover class using current step index exists), or because of a step change (class doesn't exist).
            this._showPopover(step, i);

            if (step.onShown != null)
            {
                step.onShown(this);
            }

            return this;
        };

        // handles view of popover
        Tour.prototype._showPopover = function (step, i) {
            var $element,
                $tip,
                isOrphan,
                options,
                title,
                content,
                percentProgress,
                modalObject;

            isOrphan = this._isOrphan(step);


            // is this step already visible? _showPopover is called by _showPopoverAndOverlay, which is called by window scroll event. This
            // check prevents the continual flickering of the current tour step - original approach reloaded the popover every scroll event.
            // Why is this check here and not in _showPopoverAndOverlay? This allows us to selectively redraw elements on scroll.
            if($(document).find(".popover.tour-" + this._options.name + ".tour-" + this._options.name + "-" + this.getCurrentStepIndex()).length == 0)
            {
                // Step not visible, draw first time

                $(".tour-" + this._options.name).remove();

                step.template = this._template(step, i);

                if (isOrphan)
                {
                    // Note: BS4 popper.js requires additional fiddling to work, see below where popOpts object is created
                    step.element = 'body';
                    step.placement = 'top';
                }

                $element = $(step.element);

                $element.addClass("tour-" + this._options.name + "-element tour-" + this._options.name + "-" + i + "-element");


                if (step.reflex && !isOrphan)
                {
                    $(step.reflexElement).addClass('tour-step-element-reflex').off((this._reflexEvent(step.reflex)) + ".tour-" + this._options.name).on((this._reflexEvent(step.reflex)) + ".tour-" + this._options.name, (function (_this) {
                        return function () {
                            if (_this._isLast()) {
                                return _this.next();
                            } else {
                                return _this.end();
                            }
                        };
                    })(this));
                }


                title = step.title;
                content = step.content;
                percentProgress = parseInt(((i + 1) / this.getStepCount()) * 100);

                if(step.showProgressBar)
                {
                    if(typeof(step.getProgressBarHTML) == "function")
                    {
                        content = step.getProgressBarHTML(percentProgress) + content;
                    }
                    else
                    {
                        content = '<div class="progress"><div class="progress-bar progress-bar-striped" role="progressbar" style="width: ' + percentProgress + '%;"></div></div>' + content;
                    }
                }

                if(step.showProgressText)
                {
                    if(typeof(step.getProgressTextHTML) == "function")
                    {
                        title += step.getProgressTextHTML(i, percentProgress, this.getStepCount());
                    }
                    else
                    {
                        if(this._options.framework == "bootstrap3")
                        {
                            title += '<span class="pull-right">' + (i + 1) + '/' + this.getStepCount() + '</span>';
                        }
                        if(this._options.framework == "bootstrap4")
                        {
                            title += '<span class="float-right">' + (i + 1) + '/' + this.getStepCount() + '</span>';
                        }
                    }
                }

                // Tourist v0.10 - split popOpts out of bootstrap popper instantiation due to BS3 / BS4 diverging requirements
                var popOpts = {
                    placement: step.placement, // When auto is specified, it will dynamically reorient the popover.
                    trigger: 'manual',
                    title: title,
                    content: content,
                    html: true,
                    //sanitize: false, // turns off all bootstrap sanitization of popover content, only use in last resort case - use whiteListAdditions instead!
                    whiteList: this._options.sanitizeWhitelist, // ignored if sanitizeFn is specified
                    sanitizeFn: this._options.sanitizeFunction,
                    animation: step.animation,
                    container: step.container,
                    template: step.template,
                    selector: step.element,
                    //boundary: "viewport", // added for BS4 popper testing. Do not enable, creates visible jump on orphan step scroll to bottom
                };

                if(this._options.framework == "bootstrap4")
                {
                    if(isOrphan)
                    {
                        // BS4 uses popper.js, which doesn't have a method of fixing the popper to the center of the viewport without an element. However
                        // BS4 wrapper does some extra funky stuff that means we can't just replace the BS4 popper init code. Instead, fudge the popper
                        // using the offset feature, which params don't seem to be documented properly!
                        popOpts.offset = function(obj)
                        {
                            //console.log(obj);

                            var top = Math.max(0, ( ($(window).height() - obj.popper.height) / 2) );
                            var left = Math.max(0, ( ($(window).width() - obj.popper.width) / 2) );

                            obj.popper.position="fixed";
                            obj.popper.top = top;
                            obj.popper.bottom = top + obj.popper.height;
                            obj.popper.left = left;
                            obj.popper.right = top + obj.popper.width;
                            return obj;
                        }
                    }
                    else
                    {
                        // BS3 popover accepts jq object or string literal. BS4 popper.js of course doesn't, just to make life extra irritating.
                        popOpts.selector = "#" + step.element[0].id;
                    }
                }

                // BS4 / popper.js does not accept a jquery object as element. BS3 popover does!
                if(this._options.framework == "bootstrap4" && isOrphan == false)
                {
                    popOpts.selector = "#" + step.element[0].id;
                }

                $element.popover(popOpts);
                $element.popover('show');

                if(this._options.framework == "bootstrap3")
                {
                    $tip = $element.data('bs.popover') ? $element.data('bs.popover').tip() : $element.data('popover').tip();

                    // For BS3 only. BS4 popper.js reverts this change
                    if ($element.css('position') === 'fixed')
                    {
                        $tip.css('position', 'fixed');
                    }

                    if (isOrphan)
                    {
                        this._center($tip);
                        $tip.css('position', 'fixed');
                    }
                    else
                    {
                        this._reposition($tip, step);
                    }
                }

                if(this._options.framework == "bootstrap4")
                {
                    $tip = $( ($element.data('bs.popover') ? $element.data('bs.popover').getTipElement() : $element.data('popover').getTipElement() ) );
                }

                $tip.attr('id', step.id);

                this._debug("Step " + (this._current + 1) + " of " + this._options.steps.length);
            }
            else
            {
                // Step is already visible, something has requested a redraw. Uncomment code to force redraw on scroll etc
                //$element = $(step.element);
                //$tip = $element.data('bs.popover') ? $element.data('bs.popover').tip() : $element.data('popover').tip();

                if (isOrphan)
                {
                    // unnecessary re-call, when tour step is set up centered it's fixed to the middle.
                    //this._center($tip);
                }
                else
                {
                    // Add some code to shift the popover wherever is required.
                    // NOTE: this approach works for BS3 ONLY. BS4 with popper.js requires manipulation of offset, see popOpts.offset above.
                    //this._reposition($tip, step);
                }
            }
        };

        Tour.prototype._template = function (step, i) {
            var $navigation,
                $next,
                $prev,
                $resume,
                $template,
                template;
            template = step.template;
            if (this._isOrphan(step) && {}
                .toString.call(step.orphan) !== '[object Boolean]') {
                template = step.orphan;
            }
            $template = $.isFunction(template) ? $(template(i, step)) : $(template);
            $navigation = $template.find('.popover-navigation');
            $prev = $navigation.find('[data-role="prev"]');
            $next = $navigation.find('[data-role="next"]');
            $resume = $navigation.find('[data-role="pause-resume"]');
            if (this._isOrphan(step)) {
                $template.addClass('orphan');
            }
            $template.addClass("tour-" + this._options.name + " tour-" + this._options.name + "-" + i);
            if (step.reflex) {
                $template.addClass("tour-" + this._options.name + "-reflex");
            }
            if (step.prev < 0) {
                $prev.addClass('disabled').prop('disabled', true).prop('tabindex', -1);
            }
            if (step.next < 0) {
                $next.addClass('disabled').prop('disabled', true).prop('tabindex', -1);
            }
            if (step.reflexOnly) {
                $next.hide();
            }
            if (!step.duration) {
                $resume.remove();
            }
            return $template.clone().wrap('<div>').parent().html();
        };

        Tour.prototype._reflexEvent = function (reflex) {
            if ({}
                .toString.call(reflex) === '[object Boolean]') {
                return 'click';
            } else {
                return reflex;
            }
        };

        Tour.prototype._reposition = function ($tip, step) {
            var offsetBottom,
                offsetHeight,
                offsetRight,
                offsetWidth,
                originalLeft,
                originalTop,
                tipOffset;
            offsetWidth = $tip[0].offsetWidth;
            offsetHeight = $tip[0].offsetHeight;

            tipOffset = $tip.offset();
            originalLeft = tipOffset.left;
            originalTop = tipOffset.top;

            offsetBottom = $(document).height() - tipOffset.top - $tip.outerHeight();
            if (offsetBottom < 0) {
                tipOffset.top = tipOffset.top + offsetBottom;
            }

            offsetRight = $('html').outerWidth() - tipOffset.left - $tip.outerWidth();
            if (offsetRight < 0) {
                tipOffset.left = tipOffset.left + offsetRight;
            }
            if (tipOffset.top < 0) {
                tipOffset.top = 0;
            }
            if (tipOffset.left < 0) {
                tipOffset.left = 0;
            }

            $tip.offset(tipOffset);

            if (step.placement === 'bottom' || step.placement === 'top') {
                if (originalLeft !== tipOffset.left) {
                    return this._replaceArrow($tip, (tipOffset.left - originalLeft) * 2, offsetWidth, 'left');
                }
            } else {
                if (originalTop !== tipOffset.top) {
                    return this._replaceArrow($tip, (tipOffset.top - originalTop) * 2, offsetHeight, 'top');
                }
            }
        };

        Tour.prototype._center = function ($tip)
        {
            $tip.css('top', $(window).outerHeight() / 2 - $tip.outerHeight() / 2);

            return $tip.css('left', $(window).outerWidth() / 2 - $tip.outerWidth() / 2);
        };

        Tour.prototype._replaceArrow = function ($tip, delta, dimension, position) {
            return $tip.find('.arrow').css(position, delta ? 50 * (1 - delta / dimension) + '%' : '');
        };

        Tour.prototype._scrollIntoView = function (i) {
            var $element,
                $window,
                counter,
                height,
                offsetTop,
                scrollTop,
                step,
                windowHeight;
            step = this.getStep(i);
            $element = $(step.element);

            if(this._isOrphan(step))
            {
                // If this is an orphan step, don't auto-scroll. Orphan steps are now css fixed to center of window
                return this._showPopoverAndOverlay(i);
            }

            if (!$element.length)
            {
                return this._showPopoverAndOverlay(i);
            }

            $window = $(window);
            offsetTop = $element.offset().top;
            height = $element.outerHeight();
            windowHeight = $window.height();
            scrollTop = 0;
            switch (step.placement) {
                case 'top':
                    scrollTop = Math.max(0, offsetTop - (windowHeight / 2));
                    break;
                case 'left':
                case 'right':
                    scrollTop = Math.max(0, (offsetTop + height / 2) - (windowHeight / 2));
                    break;
                case 'bottom':
                    scrollTop = Math.max(0, (offsetTop + height) - (windowHeight / 2));
            }
            this._debug("Scroll into view. ScrollTop: " + scrollTop + ". Element offset: " + offsetTop + ". Window height: " + windowHeight + ".");
            counter = 0;
            return $('body, html').stop(true, true).animate({
                scrollTop: Math.ceil(scrollTop)
            }, (function (_this) {
                return function () {
                    if (++counter === 2) {
                        _this._showPopoverAndOverlay(i);
                        return _this._debug("Scroll into view.\nAnimation end element offset: " + ($element.offset().top) + ".\nWindow height: " + ($window.height()) + ".");
                    }
                };
            })(this));
        };


        // Note: this method is not required, but remains here in case any future forkers want to reinstate the code that moves a non-orphan popover
        // when window is scrolled
        Tour.prototype._onScroll = function (callback, timeout) {
            return $(window).on("scroll.tour-" + this._options.name, function () {
                clearTimeout(timeout);
                return timeout = setTimeout(callback, 100);
            });
        };

        Tour.prototype._initMouseNavigation = function () {
            var _this;
            _this = this;
            return $(document).off("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='prev']").off("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='next']").off("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='end']").off("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='pause-resume']").on("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='next']", (function (_this) {
                return function (e) {
                    e.preventDefault();
                    return _this.next();
                };
            })(this)).on("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='prev']", (function (_this) {
                return function (e) {
                    e.preventDefault();
                    if (_this._current > 0) {
                        return _this.prev();
                    }
                };
            })(this)).on("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='end']", (function (_this) {
                return function (e) {
                    e.preventDefault();
                    return _this.end();
                };
            })(this)).on("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='pause-resume']", function (e) {
                var $this;
                e.preventDefault();
                $this = $(this);
                $this.text(_this._paused ? $this.data('pause-text') : $this.data('resume-text'));
                if (_this._paused) {
                    return _this.resume();
                } else {
                    return _this.pause();
                }
            });
        };

        Tour.prototype._initKeyboardNavigation = function () {
            if (!this._options.keyboard) {
                return;
            }
            return $(document).on("keyup.tour-" + this._options.name, (function (_this) {
                return function (e) {
                    if (!e.which) {
                        return;
                    }
                    switch (e.which) {
                        case 39:
                            e.preventDefault();
                            if (_this._isLast()) {
                                return _this.next();
                            } else {
                                return _this.end();
                            }
                            break;
                        case 37:
                            e.preventDefault();
                            if (_this._current > 0) {
                                return _this.prev();
                            }
                    }
                };
            })(this));
        };

        // If param is a promise, returns the promise back to the caller. Otherwise returns null.
        // Only purpose is to make calls to _callOnPromiseDone() simple - first param of _callOnPromiseDone()
        // accepts either null or a promise to smart call either promise or straight callback. This
        // pair of funcs therefore allows easy integration of user code to return callbacks or promises
        Tour.prototype._makePromise = function (possiblePromise)
        {
            if (possiblePromise && $.isFunction(possiblePromise.then))
            {
                return possiblePromise;
            }
            else
            {
                return null;
            }
        };

        // Creates a promise wrapping the callback if valid promise is provided as first arg. If
        // first arg is not a promise, simply uses direct function call of callback.
        Tour.prototype._callOnPromiseDone = function (promise, callback, arg)
        {
            if (promise)
            {
                return promise.then(
                    (function (_this)
                        {
                            return function (e)
                            {
                                return callback.call(_this, arg);
                            };
                        }
                    )(this)
                );
            }
            else
            {
                return callback.call(this, arg);
            }
        };

        // Bootstrap Select custom draws the drop down, force the Z index between Tour overlay and popoper
        Tour.prototype._fixBootstrapSelectPickerZindex = function(step)
        {
            if(this._isOrphan(step))
            {
                // If it's an orphan step, it can't be a selectpicker element
                return;
            }

            // is the current step already visible?
            if($(document).find(".popover.tour-" + this._options.name + ".tour-" + this._options.name + "-" + this.getCurrentStepIndex()).length != 0)
            {
                // don't waste time redoing the fix
                return;
            }

            var $selectpicker;
            // is this element or child of this element a selectpicker
            if($(step.element)[0].tagName.toLowerCase() == "select")
            {
                $selectpicker = $(step.element);
            }
            else
            {
                $selectpicker = $(step.element).find("select:first");
            }

            // is this selectpicker a bootstrap-select: https://github.com/snapappointments/bootstrap-select/
            if($selectpicker.length > 0 && $selectpicker.parent().hasClass("bootstrap-select"))
            {
                this._debug("Fixing Bootstrap SelectPicker");
                // set zindex to open dropdown over background element
                $selectpicker.parent().css("z-index", "1101");

                // store the element for other calls. Mainly for when step is hidden, selectpicker must be unfixed / z index reverted to avoid visual issues.
                // storing element means we don't need to find it again later
                this._setStepFlag(this.getCurrentStepIndex(), "elementBootstrapSelectpicker", $selectpicker);
            }
        }

        // Revert the Z index between Tour overlay and popoper
        Tour.prototype._unfixBootstrapSelectPickerZindex = function(step)
        {
            var $selectpicker = this._getStepFlag(this.getCurrentStepIndex(), "elementBootstrapSelectpicker");
            if($selectpicker)
            {
                this._debug("Unfixing Bootstrap SelectPicker");
                // set zindex to open dropdown over background element
                $selectpicker.parent().css("z-index", "auto");
            }
        }

        // Shows the preventInteraction div, and the background divs
        Tour.prototype._showOverlayElements = function (step) {
            var elementData,
                isRedraw;

            // check if the popover for the current step already exists (is this a redraw)
            if($(document).find(".popover.tour-" + this._options.name + ".tour-" + this._options.name + "-" + this.getCurrentStepIndex()).length == 0)
            {
                // not a redraw - could be a new step (i.e.: moving to the next step), or could be the result of a window resize event, which destroys the
                // old popover
                isRedraw = false;
            }
            else
            {
                // Yes. Likely this is because of a window scroll event
                isRedraw = true;

                return;
            }

            if(step.preventInteraction && !isRedraw)
            {
                $(step.backdropContainer).append("<div class='tour-prevent' id='tourPrevent'></div>");
                $("#tourPrevent").width($(step.element).outerWidth());
                $("#tourPrevent").height($(step.element).outerHeight());
                $("#tourPrevent").offset($(step.element).offset());
            }

            docHeight = $(document).height();
            docWidth = $(document).width();

            if ($(step.element).length === 0 || this._isOrphan(step))
            {
                var $backdrop = $('<div class="tour-backdrop tour-backdrop-orphan"></div>');
                $backdrop.offset({top: 0, left: 0});
                $backdrop.width(docWidth);
                $backdrop.height(docHeight);
                $("body").append($backdrop);
            }
            else
            {
                elementData =	{
                    width: $(step.element).innerWidth(),
                    height: $(step.element).innerHeight(),
                    offset: $(step.element).offset()
                };

                if (step.backdropPadding)
                {
                    elementData = this._applyBackdropPadding(step.backdropPadding, elementData);
                }

                var $backdropTop	= $('<div class="tour-backdrop top"></div>');
                $backdropTop.offset({top: 0, left: 0});
                $backdropTop.width(docWidth);
                $backdropTop.height(elementData.offset.top);

                var $backdropLeft	= $('<div class="tour-backdrop left"></div>');
                $backdropLeft.width(elementData.offset.left);
                $backdropLeft.height(elementData.height);
                $backdropLeft.offset({top: elementData.offset.top, left: 0});

                var $backdropRight	= $('<div class="tour-backdrop right"></div>');
                $backdropRight.width(docWidth - (elementData.width + elementData.offset.left));
                $backdropRight.height(elementData.height);
                $backdropRight.offset({top: elementData.offset.top, left: elementData.offset.left + elementData.width});

                var $backdropBottom = $('<div class="tour-backdrop bottom"></div>');
                $backdropBottom.width(docWidth);
                $backdropBottom.height(docHeight - elementData.offset.top - elementData.height);
                $backdropBottom.offset({top: elementData.offset.top + elementData.height, left: 0});

                $(step.backdropContainer).append($backdropTop);
                $(step.backdropContainer).append($backdropLeft);
                $(step.backdropContainer).append($backdropRight);
                $(step.backdropContainer).append($backdropBottom);
            }
        };

        Tour.prototype._hideOverlayElement = function (step)
        {
            // remove any previous interaction overlay
            if($("#tourPrevent").length)
            {
                $("#tourPrevent").remove();
            }

            $(".tour-backdrop").remove();
        };

        Tour.prototype._applyBackdropPadding = function (padding, data)
        {
            if (typeof padding === 'object') {
                if (padding.top == null) {
                    padding.top = 0;
                }
                if (padding.right == null) {
                    padding.right = 0;
                }
                if (padding.bottom == null) {
                    padding.bottom = 0;
                }
                if (padding.left == null) {
                    padding.left = 0;
                }
                data.offset.top = data.offset.top - padding.top;
                data.offset.left = data.offset.left - padding.left;
                data.width = data.width + padding.left + padding.right;
                data.height = data.height + padding.top + padding.bottom;
            } else {
                data.offset.top = data.offset.top - padding;
                data.offset.left = data.offset.left - padding;
                data.width = data.width + (padding * 2);
                data.height = data.height + (padding * 2);
            }
            return data;
        };

        Tour.prototype._clearTimer = function () {
            window.clearTimeout(this._timer);
            this._timer = null;
            return this._duration = null;
        };


        // =============================================================================================================================

        Tour.prototype._getProtocol = function (url) {
            url = url.split('://');
            if (url.length > 1) {
                return url[0];
            } else {
                return 'http';
            }
        };

        Tour.prototype._getHost = function (url) {
            url = url.split('//');
            url = url.length > 1 ? url[1] : url[0];
            return url.split('/')[0];
        };

        Tour.prototype._getPath = function (path) {
            return path.replace(/\/?$/, '').split('?')[0].split('#')[0];
        };

        Tour.prototype._getQuery = function (path) {
            return this._getParams(path, '?');
        };

        Tour.prototype._getHash = function (path) {
            return this._getParams(path, '#');
        };

        Tour.prototype._getParams = function (path, start) {
            var j,
                len,
                param
                params,
                paramsObject;
            params = path.split(start);
            if (params.length === 1) {
                return {};
            }
            params = params[1].split('&');
            paramsObject = {};
            for (j = 0, len = params.length; j < len; j++) {
                param = params[j];
                param = param.split('=');
                paramsObject[param[0]] = param[1] || '';
            }
            return paramsObject;
        };

        Tour.prototype._equal = function (obj1, obj2) {
            var j,
                k,
                len,
                obj1Keys,
                obj2Keys,
                v;
            if ({}
                .toString.call(obj1) === '[object Object]' && {}
                .toString.call(obj2) === '[object Object]') {
                obj1Keys = Object.keys(obj1);
                obj2Keys = Object.keys(obj2);
                if (obj1Keys.length !== obj2Keys.length) {
                    return false;
                }
                for (k in obj1) {
                    v = obj1[k];
                    if (!this._equal(obj2[k], v)) {
                        return false;
                    }
                }
                return true;
            } else if ({}
                .toString.call(obj1) === '[object Array]' && {}
                .toString.call(obj2) === '[object Array]') {
                if (obj1.length !== obj2.length) {
                    return false;
                }
                for (k = j = 0, len = obj1.length; j < len; k = ++j) {
                    v = obj1[k];
                    if (!this._equal(v, obj2[k])) {
                        return false;
                    }
                }
                return true;
            } else {
                return obj1 === obj2;
            }
        };

        return Tour;

    })();
    return Tour;
});
