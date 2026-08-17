/**
 * Vue island runtime: component registry, mounter and lifecycle management.
 *
 * Components are registered platform-wide by PascalCase name and mounted as
 * independent Vue apps ("islands") on their kebab-case tag or on
 * [data-vue-component] mount points. Mounting is driven by ui.additions, so
 * islands inherit every code path that applies additions today: initial page
 * load, pjax navigation, modals, stream loading and widget reloads.
 *
 * See docs/develop/ui-js-vuejs.md
 */
humhub.module('vue', function (module, require, $) {
    var additions = require('ui.additions');
    var i18n = require('i18n');
    var client = require('client');

    // PascalCase, e.g. LikeButton, HButton, PDFViewer — validated further below
    // against the tag toTagName() derives from it (must contain a dash).
    var NAME_PATTERN = /^[A-Z][A-Za-z0-9]*$/;

    var components = {}; // name -> component definition
    var tagNames = {};   // tag  -> name
    var apps = new Map(); // root element -> mounted Vue app, or a unique reservation token while mounting
    var pending = new Map(); // root element -> in-flight mount promise

    // A reservation token (see mountElement) is a plain {} — only a real
    // mounted Vue app instance has an `unmount` function. Used everywhere an
    // `apps` entry needs to be treated as "not actually mounted yet".
    var isMountedApp = function (value) {
        return !!value && typeof value.unmount === 'function';
    };

    // module.log is attached by the core only at the ready sweep, but register()
    // already runs at script-load time — fall back to the console until then.
    // module.log briefly equals this exported object itself (module.export
    // assigns it), so guard against self-reference.
    var log = {};
    var logSafe = function (level, args) {
        var attached = (module.log && module.log !== log) ? module.log : null;
        if (attached && typeof attached[level] === 'function') {
            attached[level].apply(attached, args);
        } else if (typeof console !== 'undefined' && typeof console[level] === 'function') {
            console[level].apply(console, args);
        }
    };
    ['error', 'warn', 'info', 'debug'].forEach(function (level) {
        log[level] = function () {
            logSafe(level, arguments);
        };
    });

    var toTagName = function (name) {
        return name
            // word boundary: lowercase/digit followed by uppercase (likeButton -> like-Button)
            .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
            // word boundary inside a run of capitals, before the run's last letter
            // starts a new (lowercase-led) word (PDFViewer -> PDF-Viewer)
            .replace(/([A-Z])([A-Z][a-z])/g, '$1-$2')
            .toLowerCase();
    };

    var VUE_ADDITION_ID = 'vue-components';
    var vueAdditionRegistered = false;

    var applyMount = function ($match) {
        $match.each(function () {
            mountElement(this);
        });
    };

    var registerMountPoint = function (tag, name) {
        var selector = tag + ', [data-vue-component="' + name + '"]';

        if (!vueAdditionRegistered) {
            vueAdditionRegistered = true;
            // Registering an addition after page init applies it to the current
            // document immediately, which mounts any mount points already waiting
            // in the DOM ("HTML first, script afterwards" ordering is safe).
            additions.register(VUE_ADDITION_ID, selector, applyMount);
            return;
        }

        // Every component shares a single addition id so applyTo() — run on
        // every pjax navigation, modal open and stream/widget reload — does
        // one DOM query total, not one per registered component (see
        // humhub.ui.additions.js applyTo(), which calls module.apply(), i.e.
        // one .find() per id in _order).
        //
        // Deliberately NOT using extend()'s own `applyOnInit` to reproduce the
        // "immediate apply for late registration" behavior above: as of this
        // writing humhub.ui.additions.js:352 calls `module.apply('body', id)`
        // with a plain string instead of `$('body')`, and apply() unconditionally
        // does `$element.find(...)` — so applyOnInit throws
        // ("body".find is not a function) the moment it fires on an extended
        // addition. Mounting the newly-added selector's own waiting elements
        // directly below sidesteps that bug while still keeping one
        // consolidated addition id for every future sweep.
        additions.register(VUE_ADDITION_ID, selector, applyMount, { extend: true });

        if (humhub.initialized) {
            $(document.body).find(selector).addBack(selector).each(function () {
                mountElement(this);
            });
        }
    };

    var register = function (name, component) {
        if (!NAME_PATTERN.test(name) || toTagName(name).indexOf('-') === -1) {
            log.error('Invalid Vue component name "' + name + '" — PascalCase producing a dashed tag name required');
            return;
        }

        if (components[name]) {
            log.error('Vue component "' + name + '" is already registered');
            return;
        }

        var tag = toTagName(name);
        if (tagNames[tag]) {
            log.error('Vue component "' + name + '" derives tag <' + tag + '>, which is already used by "' + tagNames[tag] + '"');
            return;
        }

        components[name] = component;
        tagNames[tag] = name;

        // Make the component available to islands that mounted earlier —
        // registered components are global in every island app (see mountElement).
        apps.forEach(function (app) {
            if (isMountedApp(app)) {
                app.component(name, component);
            }
        });

        registerMountPoint(tag, name);
    };

    var componentFor = function (element) {
        var name = element.getAttribute('data-vue-component') || tagNames[element.tagName.toLowerCase()];
        return { name: name, component: name ? components[name] : null };
    };

    var coerce = function (value, declaration) {
        var type = (declaration && declaration.type !== undefined) ? declaration.type : declaration;
        var types = Array.isArray(type) ? type : [type];

        if (types.indexOf(Boolean) !== -1) {
            return value !== 'false' && value !== '0';
        }

        if (types.indexOf(Number) !== -1 && value !== '') {
            return Number(value);
        }

        return value;
    };

    var parseProps = function (element, component) {
        var props = {};
        var jsonAttribute = element.hasAttribute('data-vue-component') ? 'data-props' : 'props';
        var json = element.getAttribute(jsonAttribute);

        if (json) {
            try {
                $.extend(props, JSON.parse(json));
            } catch (e) {
                log.error('Invalid JSON in "' + jsonAttribute + '" attribute of Vue mount point', e);
            }
        }

        var declarations = component.props || {};
        $.each(element.attributes, function (index, attribute) {
            var name = attribute.name;
            if (name === 'props' || name === 'class' || name === 'style' || name === 'id' || name.indexOf('data-') === 0) {
                return;
            }
            var propName = name.replace(/-([a-z])/g, function (match, character) {
                return character.toUpperCase();
            });
            var declaredType = Object.prototype.hasOwnProperty.call(declarations, propName)
                ? declarations[propName]
                : undefined;
            props[propName] = coerce(attribute.value, declaredType);
        });

        return props;
    };

    var mountElement = function (element) {
        if (apps.has(element)) {
            var current = apps.get(element);
            if (isMountedApp(current)) {
                return Promise.resolve(current);
            }
            // Reserved (mount in flight) but not yet a real app: return the
            // in-flight promise so concurrent callers share one mount instead
            // of one resolving null (see `pending` below).
            return pending.get(element) || Promise.resolve(null);
        }

        var resolved = componentFor(element);
        if (!resolved.component) {
            var label = resolved.name || element.tagName.toLowerCase();
            log.warn('No registered Vue component for mount point "' + label + '"');
            return Promise.resolve(null);
        }

        // Unique identity for this reservation (rather than a bare `null`), so
        // a stale continuation (see below) can tell whether it was superseded
        // by a newer mountElement() call on the same element, not just cancelled.
        var token = {};
        apps.set(element, token); // reserve against double mounting while preloading

        var categories = resolved.component.i18nCategories || [];
        var preload;
        try {
            preload = categories.length ? i18n.preload(categories) : Promise.resolve();
        } catch (e) {
            preload = Promise.reject(e);
        }
        preload = preload.catch(function () {
            log.warn('Translation preload failed for Vue component "' + resolved.name + '"');
        });

        var promise = preload.then(function () {
            // The reservation was superseded while we were awaiting preload:
            // either cancelled (unmountElement()/unload() ran on this element,
            // e.g. a pjax navigation away) or replaced by a newer
            // mountElement() call on the same element that reserved its own
            // token. Bail out — draining `pending` only when it is still ours:
            // on plain cancellation (no remount) it is, and must be cleared or
            // the entry leaks forever; on supersede it already holds the newer
            // mount's promise, so the guard below leaves it untouched.
            if (apps.get(element) !== token) {
                if (pending.get(element) === promise) {
                    pending.delete(element);
                }
                return null;
            }

            if (typeof Vue === 'undefined') {
                log.error('Vue runtime is not loaded — is the VueAsset bundle registered?');
                apps.delete(element);
                if (pending.get(element) === promise) {
                    pending.delete(element);
                }
                return null;
            }

            var app = Vue.createApp(resolved.component, parseProps(element, resolved.component));

            // All registry components are globally available in every island,
            // enabling cross-module nesting without imports.
            $.each(components, function (name, component) {
                app.component(name, component);
            });

            app.config.errorHandler = function (err, instance, info) {
                log.error('Vue component error in "' + resolved.name + '" (' + info + ')', err);
            };

            app.mount(element);
            apps.set(element, app);
            if (pending.get(element) === promise) {
                pending.delete(element);
            }
            return app;
        }).catch(function (e) {
            apps.delete(element);
            if (pending.get(element) === promise) {
                pending.delete(element);
            }
            log.error(e, true);
            return null;
        });

        pending.set(element, promise);
        return promise;
    };

    var unmountElement = function (element) {
        var app = apps.get(element);
        apps.delete(element); // also drops a still-in-flight reservation (cancellation)
        if (isMountedApp(app)) {
            app.unmount();
        }
    };

    var getApp = function (element) {
        var app = apps.get(element);
        return isMountedApp(app) ? app : null;
    };

    var config = function (moduleId) {
        return humhub.config.module(moduleId);
    };

    module.init = function () {
        // Safety net: unmount islands whose root left the DOM (closed modal,
        // deleted stream entry) to release watchers and listeners.
        var observer = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                if (mutations[i].removedNodes.length) {
                    apps.forEach(function (app, element) {
                        if (!element.isConnected) {
                            unmountElement(element);
                        }
                    });
                    return;
                }
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    };

    module.unload = function () {
        // Pjax replaces #layout-content wholesale; unmount all islands inside it.
        var layout = document.getElementById('layout-content');
        apps.forEach(function (app, element) {
            if (!element.isConnected || (layout && layout.contains(element))) {
                unmountElement(element);
            }
        });
    };

    // `log` is exported for callers that want the same pre-ready-safe wrapper
    // this module uses internally. The core replaces `module.log` with the
    // real logger at the ready sweep (see the top-of-file comment on `log`
    // above) — this wrapper only matters in the window before that happens.
    module.export({
        register: register,
        mountElement: mountElement,
        unmountElement: unmountElement,
        getApp: getApp,
        client: client,
        i18n: i18n,
        log: log,
        config: config,
    });
});
