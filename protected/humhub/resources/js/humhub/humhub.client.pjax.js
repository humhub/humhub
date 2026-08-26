humhub.module('client.pjax', function (module, require, $) {
    var event = require('event');

    var PJAX_CONTAINER_SELECTOR = '#layout-content';

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

            captureFocusSnapshot();

            event.trigger('humhub:modules:client:pjax:beforeSend', {
                'originalEvent': evt,
                'xhr': xhr,
                'options': options
            });
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

            manageFocus();
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

    var captureFocusSnapshot = function () {
        var container = document.querySelector(PJAX_CONTAINER_SELECTOR);
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
            var significantChildren = function (node) {
                if (!node) {
                    return [];
                }
                // Yii injects these inline wherever assets get registered; their
                // count and position vary per page and would break positional
                // comparison. Hidden (.d-none) elements - e.g. a toast/template
                // placeholder that isn't part of every page - are skipped too:
                // they can never be a real focus target, and their presence or
                // absence would otherwise misalign the old/new comparison.
                var NOISE_TAGS = {SCRIPT: true, LINK: true, STYLE: true, META: true, NOSCRIPT: true};
                return Array.prototype.filter.call(node.children, function (el) {
                    return !NOISE_TAGS[el.tagName] && !el.classList.contains('d-none');
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
                var AUTO_ID_TOKEN_PATTERN = /\bh\d+w\d+/g;

                // A hidden (.d-none) descendant - e.g. an upload-progress
                // indicator whose markup only appears once it's actually used -
                // can differ between renders without the visible content having
                // changed at all. It's dropped (on a clone, so the live DOM is
                // untouched) before comparing, same as significantChildren()
                // drops a hidden child from the comparison entirely.
                var normalize = function (el) {
                    var clone = el.cloneNode(true);
                    var hidden = clone.querySelectorAll('.d-none');
                    for (var i = 0; i < hidden.length; i++) {
                        if (hidden[i].parentNode) {
                            hidden[i].parentNode.removeChild(hidden[i]);
                        }
                    }
                    return clone.outerHTML.replace(AUTO_ID_TOKEN_PATTERN, '');
                };

                return normalize(a) === normalize(b);
            };

            var countElements = function (node) {
                return node.querySelectorAll('*').length;
            };

            // Safety limits against pathological pages.
            var DIFF_MAX_ELEMENTS = 8000;
            var DIFF_MAX_DEPTH = 25;

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

        var container = document.querySelector(PJAX_CONTAINER_SELECTOR);
        var snapshot = beforeSnapshot;
        beforeSnapshot = null; // only ever needed for this one comparison

        if (!container) {
            return;
        }

        var activeElement = document.activeElement;
        if (activeElement && activeElement !== document.body && container.contains(activeElement)) {
            // The new content already manages focus itself, don't interfere.
            return;
        }

        var target = findContentTarget(snapshot, container) || container;

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
