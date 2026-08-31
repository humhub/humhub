humhub.module('client.pjax', function (module, require, $) {
    var event = require('event');

    var PJAX_CONTAINER_SELECTOR = '#layout-content';

    // Most layouts that use PJAX_CONTAINER_SELECTOR (space, profile,
    // dashboard, notification, ...) already mark their real content area
    // with this class, right next to .layout-nav-container /
    // .layout-sidebar-container. manageFocus() checks for it before falling
    // back to diffing the tree - see there for why.
    var LAYOUT_CONTENT_SELECTOR = '.layout-content-container';

    // Constants used by manageFocus()'s content-diffing helpers further
    // below. Unlike those helpers, these are pulled up to module scope so
    // they're not recreated on every pjax navigation - they don't depend on
    // anything local to a single call, so there's nothing to gain from
    // keeping them nested next to the functions that use them.
    var NOISE_TAGS = ['SCRIPT', 'LINK', 'STYLE', 'META', 'NOSCRIPT'];
    var AUTO_ID_TOKEN_PATTERN = /\bh\d+w\d+/g;
    var DIFF_MAX_ELEMENTS = 8000;
    var DIFF_MAX_DEPTH = 25;

    var init = function () {
        if (module.config.active) {
            $(document).pjax('a:not([data-pjax-prevent],[target],[data-bs-target],[data-bs-toggle],.exclude-from-pjax-client a)', PJAX_CONTAINER_SELECTOR, module.config.options);
            pjaxRedirectFix();
            module.installLoader();
        }
    };

    var post = function(evt) {
        var $options = $.extend({}, module.config.options);
        $options.url = evt.url;
        $options.container = PJAX_CONTAINER_SELECTOR;
        $options.type = 'POST';
        $.pjax($options);
    };

    var redirect = function(url) {
        $.pjax({url: url, container: PJAX_CONTAINER_SELECTOR, timeout : module.config.options.timeout});
    };

    var reload = function() {
        $.pjax.reload({container: PJAX_CONTAINER_SELECTOR, timeout : module.config.options.timeout});
    };

    var pjaxRedirectFix = function () {
        $(document).on("pjax:beforeSend", function (evt, xhr, options) {
            // Ignore links with data-bs-target attribute
            if ($(event.relatedTarget).data('target')) {
                return false;
            }

            event.trigger('humhub:modules:client:pjax:beforeSend', {
                'originalEvent': evt,
                'xhr': xhr,
                'options': options
            });
        });

        // captureFocusSnapshot()/manageFocus() are hooked to beforeReplace/end
        // rather than beforeSend/success so cached browser back/forward
        // navigation is covered too (defensively - HumHub actually runs pjax
        // with cache disabled via PjaxLayoutContent, so today this only ever
        // matters if that's ever turned on): on a cache hit pjax restores the
        // container straight from its history cache and only ever fires
        // beforeReplace/end - beforeSend/success never happen (see the
        // popstate handler in jquery.pjax.modified.js). Both events fire on
        // the normal request path as well (right before/after the container
        // is swapped), so this one pair covers both cases.
        //
        // Both handlers bubble up from whichever pjax container fired them,
        // not just ours - a module can nest its own Pjax::begin() (e.g. a
        // GridView), and without this check its paging/sorting would run our
        // focus logic against #layout-content instead of leaving it alone.
        $(document).on("pjax:beforeReplace", function (evt, contents, options) {
            if (!options || options.container !== PJAX_CONTAINER_SELECTOR) {
                return;
            }
            captureFocusSnapshot();
        });

        $(document).on("pjax:end", function (evt, xhr, options) {
            if (!options || options.container !== PJAX_CONTAINER_SELECTOR) {
                return;
            }
            manageFocus();
        });

        $(document).on("pjax:success", function (evt, data, status, xhr, options) {
            event.trigger('humhub:modules:client:pjax:success', {
                'originalEvent': evt,
                'data': data,
                'status': status,
                'xhr': xhr,
                'options': options
            });

            // Update default ajax url, used if no url is given.
            $.ajaxSetup({
                url: window.location.href
            });
        });

        $.ajaxPrefilter('html', function (options, originalOptions, jqXHR) {
            var orgErrorHandler = options.error;
            options.error = function (xhr, textStatus, errorThrown) {
                if (isPjaxRedirect(xhr)) {
                    options.url = xhr.getResponseHeader('X-PJAX-REDIRECT-URL');
                    options.replace = true;
                    module.log.info('Handled redirect to: ' + options.url);
                    $.pjax(options);
                } else {
                    orgErrorHandler(xhr, textStatus, errorThrown);
                }
            };
        });
    };

    // Clone of the container taken right before the pjax request, so
    // findContentTarget() can tell changed content from a persisting menu.
    var beforeSnapshot = null;

    // pjax:end also fires for a request that never replaced anything - pjax
    // aborts the in-flight request itself on a new navigation or a
    // popstate, and options.complete() (which fires pjax:end) runs on abort
    // and error too, not just success. manageFocus() only has a real swap to
    // react to when captureFocusSnapshot() actually ran for it first.
    var swapPending = false;

    var captureFocusSnapshot = function () {
        swapPending = true;

        var container = document.querySelector(PJAX_CONTAINER_SELECTOR);

        // Skip the deep clone below - the expensive part of this whole
        // mechanism - when manageFocus()'s fast path is going to win anyway.
        // The current container already having LAYOUT_CONTENT_SELECTOR is a
        // reasonable proxy for the swapped-in content having it too:
        // navigation rarely crosses between a layout that marks its content
        // area this way and one that doesn't. If it does, the only cost is
        // manageFocus() falling back to a less precise target, same as when
        // no snapshot is available at all (e.g. the very first navigation).
        if (container && container.querySelector(LAYOUT_CONTENT_SELECTOR)) {
            beforeSnapshot = null;
            return;
        }

        beforeSnapshot = container ? container.cloneNode(true) : null;
    };

    /**
     * Accessibility fix: a pjax swap removes the focused element from the
     * DOM, so browsers reset focus to <body> and Tab restarts at the top
     * of the page. We move focus into the new content instead, unless it
     * already focused something itself (e.g. an autofocused form field).
     */
    var manageFocus = function () {
        /**
         * Finds which part of `newRoot` is actually new by diffing it against
         * `oldRoot`: descend through single-child wrappers, and at each branch
         * skip children unchanged from their old counterpart (isEquivalentNode)
         * to continue into the largest changed one. Stops as soon as a level
         * has nothing unchanged left to skip past - that's the landing point.
         */
        function findContentTarget(oldRoot, newRoot) {
            // An element hidden via Bootstrap's .d-none, an unopened
            // .collapse, the "hidden" attribute, or an inline display:none
            // can never be a real focus target, and its presence/absence (or
            // its markup changing while it stays hidden - e.g. an
            // upload-progress indicator that only fills in once used) would
            // otherwise misalign the old/new comparison. getComputedStyle()
            // isn't used here: oldRoot and every node normalize() compares
            // (below) are detached clones, and computed style resolution for
            // a detached node isn't reliable across browsers.
            var isHiddenElement = function (el) {
                return el.classList.contains('d-none')
                    || (el.classList.contains('collapse') && !el.classList.contains('show'))
                    || el.hidden
                    || el.style.display === 'none';
            };

            var significantChildren = function (node) {
                if (!node) {
                    return [];
                }
                // Yii injects these inline wherever assets get registered; their
                // count and position vary per page and would break positional
                // comparison.
                return Array.prototype.filter.call(node.children, function (el) {
                    return NOISE_TAGS.indexOf(el.tagName) === -1 && !isHiddenElement(el);
                });
            };

            var isEquivalentNode = function (a, b) {
                if (!a || !b) {
                    return false;
                }
                // Yii auto-generates a per-request id like "h992827w75" for every
                // widget, and other attributes (e.g. data-action-target="#h...")
                // can reference it too - so we strip the bare token everywhere,
                // not just from id="...". No trailing \b: Yii sometimes appends a
                // suffix straight after the digits (id="h778195w33_progress").
                // AUTO_ID_TOKEN_PATTERN is declared at module scope, above.

                // Hidden descendants are dropped (on a clone, so the live
                // DOM is untouched) before comparing, same as
                // significantChildren() drops a hidden child from the
                // comparison entirely - see isHiddenElement() above for what
                // counts as hidden.
                var normalize = function (el) {
                    var clone = el.cloneNode(true);

                    var hidden = Array.prototype.filter.call(clone.querySelectorAll('*'), isHiddenElement);
                    for (var i = 0; i < hidden.length; i++) {
                        if (hidden[i].parentNode) {
                            hidden[i].parentNode.removeChild(hidden[i]);
                        }
                    }

                    // Menu widgets (e.g. the space menu) add the "active"
                    // class purely based on which link matches the current
                    // URL - the menu itself hasn't changed. Without this, two
                    // renders of the exact same space menu compare as
                    // "changed" whenever navigation crosses from one section
                    // to another (Home -> Wiki, ...), because a different
                    // link is marked active each time - defeating the whole
                    // point of skipping past an unchanged menu.
                    var activeEls = clone.querySelectorAll('.active');
                    for (var j = 0; j < activeEls.length; j++) {
                        activeEls[j].classList.remove('active');
                    }
                    if (clone.classList) {
                        clone.classList.remove('active');
                    }

                    return clone.outerHTML.replace(AUTO_ID_TOKEN_PATTERN, '');
                };

                return normalize(a) === normalize(b);
            };

            var countElements = function (node) {
                return node.querySelectorAll('*').length;
            };

            // DIFF_MAX_ELEMENTS/DIFF_MAX_DEPTH (safety limits against
            // pathological pages) are declared at module scope, above.
            if (!newRoot || countElements(newRoot) > DIFF_MAX_ELEMENTS) {
                return null;
            }

            var currentOld = oldRoot;
            var currentNew = newRoot;

            for (var depth = 0; depth < DIFF_MAX_DEPTH; depth++) {
                var children = significantChildren(currentNew);

                if (children.length === 0) {
                    break; // leaf, nothing to descend into
                }

                if (children.length === 1) {
                    // Plain wrapper - continue down without any comparison.
                    currentOld = significantChildren(currentOld)[0] || null;
                    currentNew = children[0];
                    continue;
                }

                var oldChildren = significantChildren(currentOld);
                var hasUnchangedSibling = false;
                var bestChanged = null;
                var bestScore = -1;

                for (var i = 0; i < children.length; i++) {
                    var newChild = children[i];
                    var oldChild = oldChildren[i];

                    if (isEquivalentNode(oldChild, newChild)) {
                        hasUnchangedSibling = true;
                        continue;
                    }

                    var score = (newChild.textContent || '').length;
                    if (score > bestScore) {
                        bestScore = score;
                        bestChanged = {oldChild: oldChild || null, newChild: newChild};
                    }
                }

                if (!hasUnchangedSibling || !bestChanged) {
                    // Either nothing here survived unchanged (no menu left to
                    // skip past - we've arrived) or nothing changed at all
                    // (the parent only differed by its own attributes).
                    break;
                }

                currentOld = bestChanged.oldChild;
                currentNew = bestChanged.newChild;
            }

            return currentNew;
        }

        if (!swapPending) {
            // pjax:end without a preceding beforeReplace for our container -
            // nothing was actually swapped (aborted/errored request), so
            // there's no new content to move focus into.
            return;
        }
        swapPending = false;

        var container = document.querySelector(PJAX_CONTAINER_SELECTOR);
        var snapshot = beforeSnapshot;
        beforeSnapshot = null; // only ever needed for this one comparison

        if (!container) {
            return;
        }

        var activeElement = document.activeElement;

        // The swap can happen while a modal is open in the background (e.g.
        // triggered by humhub.modules.client.reload()) - #globalModal lives
        // outside PJAX_CONTAINER_SELECTOR, so container.contains() below
        // wouldn't see it and we'd steal focus right out of an open dialog.
        // Checked from the document rather than activeElement.closest(): if
        // focus had already fallen to <body> while the modal was open,
        // closest() on <body> would find nothing and miss it.
        if (document.querySelector('.modal.show')) {
            return;
        }

        if (activeElement && activeElement !== document.body && container.contains(activeElement)) {
            // The new content already manages focus itself, don't interfere.
            return;
        }

        // When present, LAYOUT_CONTENT_SELECTOR (declared at module scope,
        // above) is a cheaper and more reliable signal than diffing the tree
        // below, so it takes priority; findContentTarget() remains the
        // fallback for content that doesn't follow this convention.
        var target = container.querySelector(LAYOUT_CONTENT_SELECTOR)
            || findContentTarget(snapshot, container)
            || container;

        // The target is not part of the natural tab order (tabindex="-1"),
        // it's only used as a programmatic focus target.
        if (!target.hasAttribute('tabindex')) {
            target.setAttribute('tabindex', '-1');
        }

        target.focus({preventScroll: true});
    };

    var isPjaxRedirect = function (xhr) {
        if (!xhr) {
            return false;
        }

        var redirect = (xhr.status >= 301 && xhr.status <= 303);
        return redirect && xhr.getResponseHeader('X-PJAX-REDIRECT-URL') != "" && xhr.getResponseHeader('X-PJAX-REDIRECT-URL') !== null;
    };

    var installLoader = function () {
        NProgress.configure({showSpinner: false});
        NProgress.configure({template: '<div class="bar" role="bar"></div>'});

        $(document).on('pjax:start', function (evt, xhr, options) {
            NProgress.start();
        });

        $(document).on('pjax:end', function (evt, xhr, options) {
            if (!isPjaxRedirect(xhr)) {
                NProgress.done();
            }
        });
    };

    var isActive = function () {
        return module.config.active;
    };

    module.export({
        init: init,
        sortOrder: 100,
        reload: reload,
        redirect: redirect,
        post: post,
        isActive: isActive,
        installLoader: installLoader,
    });
});
