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
    var urlModule = require('url');
    // Built directly into humhub.core.js (not a separate humhub.module()),
    // so it is always available by the time this module's factory runs —
    // unlike ui.modal below, no ordering caveat applies here.
    var event = require('event');

    // PascalCase, e.g. LikeButton, HButton, PDFViewer — validated further below
    // against the tag toTagName() derives from it (must contain a dash).
    var NAME_PATTERN = /^[A-Z][A-Za-z0-9]*$/;

    var components = {}; // name -> component definition
    var tagNames = {};   // tag  -> name
    var apps = new Map(); // root element -> mounted Vue app, or a unique reservation token while mounting
    var pending = new Map(); // root element -> in-flight mount promise

    // `components` above is a plain object, not reactive — a Vue computed() reading
    // isRegistered() (see below) would otherwise never know a component it once found
    // missing has since registered. Bumped once per successful register() call; reading
    // `.count` from inside isRegistered() is what lets a computed track it as a
    // dependency, the same trick used for `slots` below. See ExtensionSlot.vue's
    // "components registry itself is NOT reactive" docblock note for the full picture.
    var componentsGeneration = Vue.reactive({ count: 0 });

    // slotName -> [{component, sortOrder}] in registration order (see registerSlotComponent()).
    // A genuine Vue.reactive() store (not just a generation counter like the one above):
    // ExtensionSlot.vue reads getSlotComponents() from inside a computed(), and this needs
    // to re-evaluate not only when an existing slot gains/loses entries but also the very
    // first time a brand new slot name is ever registered — plain-object property adds are
    // exactly what Vue 3's reactive() proxy tracks (unlike Vue 2).
    var slots = Vue.reactive({});
    var slotRegistrationSeq = 0; // registration order tiebreaker, see getSlotComponents()

    // menuId -> [entry, ...] in registration order (see registerMenuEntry()) — a genuine
    // Vue.reactive() store, same reasoning as `slots` above (DropdownMenu.vue reads
    // getMenuEntries() from inside a computed, and a brand new menuId's first entry must be
    // picked up just like a brand new slot name's first entry is).
    var menuEntries = Vue.reactive({});
    // menuId -> [entryId, ...] ids removed via removeMenuEntry() — kept separate from
    // `menuEntries` itself (rather than deleting from it) because a removal must also
    // suppress a caller's own BUILT-IN entry (passed to DropdownMenu's `entries` prop, never
    // registered here at all) and must keep suppressing an entry re-registered under the same
    // id afterwards — see registerMenuEntry()'s and removeMenuEntry()'s own docblocks.
    var menuRemovals = Vue.reactive({});

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
            // Module artifacts re-execute on every ajax response that re-emits
            // their <script> tag — yii only dedups STATIC_DEPENDS bundles, so a
            // Vue component's own bundle (loaded via a regular asset bundle,
            // not STATIC_DEPENDS) runs its register() call again on every pjax
            // navigation, modal open, stream load-more, etc. That is normal
            // and expected, not an error — log it at debug level only.
            log.debug('Vue component "' + name + '" is already registered — skipping re-registration');
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

        // See componentsGeneration's own declaration comment above.
        componentsGeneration.count++;
    };

    var isRegistered = function (name) {
        // Read for dependency tracking only — see componentsGeneration's declaration
        // comment above. `components` itself is a plain object and is never read here.
        void componentsGeneration.count;

        return !!components[name];
    };

    /**
     * Registers `componentName` (by its registered NAME, not the component object itself —
     * it does NOT need to be registered yet, see below) to render inside every
     * `<ExtensionSlot name="slotName">` for the given `slotName`.
     *
     * - `options.sortOrder` (default 100) controls render order within the slot, lowest
     *   first; entries sharing a sortOrder render in registration order.
     * - The component does not need to be registered (via `register()`) at the time this
     *   is called — module artifacts can load, and therefore register slot entries, in any
     *   order relative to the component's own artifact. `ExtensionSlot` only renders entries
     *   whose component is currently registered (see `isRegistered()`/`getSlotComponents()`
     *   below and ExtensionSlot.vue), and picks up the rest reactively once they do register.
     * - Registering the same (slotName, componentName) pair twice is a debug-level no-op,
     *   keeping the first registration's sortOrder — the same "artifact scripts legitimately
     *   re-execute" case documented on `register()` above applies here too.
     */
    var registerSlotComponent = function (slotName, componentName, options) {
        if (typeof slotName !== 'string' || !slotName) {
            log.error('Invalid extension slot name "' + slotName + '" — must be a non-empty string');
            return;
        }

        if (!NAME_PATTERN.test(componentName)) {
            log.error('Invalid Vue component name "' + componentName + '" for extension slot "' + slotName + '" — PascalCase required');
            return;
        }

        var entries = slots[slotName] || (slots[slotName] = []);
        var alreadyRegistered = entries.some(function (entry) {
            return entry.component === componentName;
        });
        if (alreadyRegistered) {
            log.debug('Component "' + componentName + '" is already registered for extension slot "' + slotName + '" — skipping duplicate registration');
            return;
        }

        if (!components[componentName]) {
            log.debug('Component "' + componentName + '" registered for extension slot "' + slotName + '" is not a registered Vue component (yet) — it will appear once it registers');
        }

        var sortOrder = (options && typeof options.sortOrder === 'number') ? options.sortOrder : 100;
        entries.push({ component: componentName, sortOrder: sortOrder, order: slotRegistrationSeq++ });
    };

    /**
     * Returns the entries registered for `slotName` (see `registerSlotComponent()`), sorted
     * by sortOrder then registration order — `{component, sortOrder}` pairs, in render order.
     * Called from ExtensionSlot.vue's computed, so a reactive effect (e.g. a mounted
     * island's render) that calls this re-runs on every future `registerSlotComponent()`
     * call for this slot, including the very first one for a slot name with none yet.
     */
    var getSlotComponents = function (slotName) {
        var entries = slots[slotName] || [];

        return entries
            .slice()
            .sort(function (a, b) {
                return a.sortOrder - b.sortOrder || a.order - b.order;
            })
            .map(function (entry) {
                return { component: entry.component, sortOrder: entry.sortOrder };
            });
    };

    /**
     * Registers (or overrides) one entry of a data-driven menu — the array-of-entries
     * counterpart to `registerSlotComponent()`'s free-form slots, modeled on the server-side
     * `humhub\modules\ui\menu\widgets\Menu` API (`addEntry()`/`removeEntry()`, entries with an
     * `id` and a `sortOrder`) module devs already know. `DropdownMenu.vue`'s `menuId`/`entries`
     * props read this registry (see `getMenuEntries()` below) to resolve what a given menu
     * actually renders. See docs/develop/ui-js-vuejs-extensions.md, "Menu entries".
     *
     * `entry` shape:
     *  - `id` (required) — unique per `menuId`. Registering the same (menuId, id) pair again
     *    REPLACES the existing entry in place (same position, for sort-tie purposes) — this is
     *    the supported override mechanism, unlike `registerSlotComponent()`'s "first
     *    registration wins" rule. A module intentionally replacing another module's (or core's
     *    own) entry registers under that same id.
     *  - `label` (string, or `(context) => string`) — required unless `component` is given.
     *  - `icon` (string, optional) — an icon name in the same namespace
     *    `humhub\modules\ui\icon\widgets\Icon::get()` uses (e.g. `'pencil'`), rendered as
     *    `<i class="fa fa-<icon>">` — the plain Font Awesome class the rest of the app's
     *    hand-authored (non-`Button`-widget) markup already uses for icons (see
     *    `CommentEntry.vue`'s edited-marker icon).
     *  - `sortOrder` (number, default `1000`) — ascending, like PHP menu entries.
     *  - `condition` (`(context) => boolean`, optional) — omit to always show.
     *  - `onClick` (`(context) => void`, optional) — ignored when `component` is set.
     *  - `component` (string, optional) — a name registered via `register()`; when set, this
     *    entry renders that component instead of the built-in `<a class="dropdown-item">`,
     *    passed a single `context` prop (not spread, unlike `ExtensionSlot`'s `v-bind`) —
     *    `label`/`icon`/`onClick` are ignored. Required unless `label` is given.
     *
     * Validation failures (missing `menuId`, missing/non-unique-shaped `entry.id`, or neither
     * `label` nor `component` given) log at error level and register nothing — the same
     * "malformed call is a bug in the caller, not tolerable input" stance `register()` and
     * `registerSlotComponent()` take on their own required arguments.
     */
    var registerMenuEntry = function (menuId, entry) {
        if (typeof menuId !== 'string' || !menuId) {
            log.error('Invalid menu id "' + menuId + '" — must be a non-empty string');
            return;
        }

        if (!entry || typeof entry !== 'object') {
            log.error('Invalid menu entry for menu "' + menuId + '" — an entry object is required');
            return;
        }

        if (typeof entry.id !== 'string' || !entry.id) {
            log.error('Invalid menu entry id for menu "' + menuId + '" — entry.id must be a non-empty string');
            return;
        }

        var hasLabel = typeof entry.label === 'string' || typeof entry.label === 'function';
        var hasComponent = typeof entry.component === 'string' && entry.component !== '';
        if (!hasLabel && !hasComponent) {
            log.error('Invalid menu entry "' + entry.id + '" for menu "' + menuId + '" — either label or component is required');
            return;
        }

        var resolved = {
            id: entry.id,
            label: entry.label,
            icon: entry.icon || null,
            sortOrder: typeof entry.sortOrder === 'number' ? entry.sortOrder : 1000,
            condition: typeof entry.condition === 'function' ? entry.condition : null,
            onClick: typeof entry.onClick === 'function' ? entry.onClick : null,
            component: hasComponent ? entry.component : null,
        };

        // Not `menuEntries[menuId] || (menuEntries[menuId] = [])`: that assignment
        // expression evaluates to the RAW array literal on its right-hand side, not
        // the reactive proxy `menuEntries`' own `get` trap would hand back on a
        // subsequent read — Vue only wraps a freshly-assigned plain array reactively
        // the next time it is READ off the reactive object, not at assignment time.
        // The first `.push()` for a brand new `menuId` would otherwise land on that
        // raw array, invisible to any synchronous watcher/computed already tracking
        // `menuEntries[menuId]`.
        if (!menuEntries[menuId]) {
            menuEntries[menuId] = [];
        }
        var entries = menuEntries[menuId];
        var existingIndex = entries.findIndex(function (e) { return e.id === entry.id; });
        if (existingIndex === -1) {
            entries.push(resolved);
        } else {
            // In-place replace (not remove + push) — keeps this id's position among entries
            // sharing a sortOrder stable across an override, instead of bumping it to the back.
            entries.splice(existingIndex, 1, resolved);
        }
    };

    /**
     * Records that `entryId` of menu `menuId` must not render, regardless of whether it came
     * from a `registerMenuEntry()` call or is one of the consuming component's own BUILT-IN
     * entries (passed via `DropdownMenu`'s `entries` prop, never registered here at all) —
     * `DropdownMenu`'s resolution pipeline cross-references both against this same removal set
     * by id. See `getMenuEntries()` and docs/develop/ui-js-vuejs-extensions.md, "Menu entries".
     *
     * Removals are permanent and win over later registrations of the same id: there is no
     * "un-remove". A module that decides an id should never appear again does not have to race
     * load order against a module that might (re-)register it afterwards — whichever order the
     * two calls happen in, the removal wins. Modules that need a *toggleable* presence should
     * use `condition` on their own entry instead of remove/re-register.
     */
    var removeMenuEntry = function (menuId, entryId) {
        if (typeof menuId !== 'string' || !menuId) {
            log.error('Invalid menu id "' + menuId + '" — must be a non-empty string');
            return;
        }

        if (typeof entryId !== 'string' || !entryId) {
            log.error('Invalid menu entry id to remove from menu "' + menuId + '" — must be a non-empty string');
            return;
        }

        // Same raw-array-escapes-the-proxy hazard as `registerMenuEntry()` above —
        // see its own comment on the equivalent line.
        if (!menuRemovals[menuId]) {
            menuRemovals[menuId] = [];
        }
        var removed = menuRemovals[menuId];
        if (removed.indexOf(entryId) === -1) {
            removed.push(entryId);
        }
    };

    /**
     * Returns `{entries, removed}` for `menuId` — `entries` are the RAW entries registered via
     * `registerMenuEntry()` (registration order, not merged with any caller's built-ins and not
     * sorted — that is `DropdownMenu`'s own resolution job), `removed` is the list of ids
     * `removeMenuEntry()` recorded. Both are plain arrays read from the reactive `menuEntries`/
     * `menuRemovals` stores, so a computed reading this (directly, or through `DropdownMenu`)
     * re-evaluates on every future registration/removal for this `menuId`, including the very
     * first one — same reactivity guarantee as `getSlotComponents()`.
     */
    var getMenuEntries = function (menuId) {
        var entries = menuEntries[menuId] || [];
        var removed = menuRemovals[menuId] || [];

        return {
            entries: entries.map(function (entry) {
                return {
                    id: entry.id,
                    label: entry.label,
                    icon: entry.icon,
                    sortOrder: entry.sortOrder,
                    condition: entry.condition,
                    onClick: entry.onClick,
                    component: entry.component,
                };
            }),
            removed: removed.slice(),
        };
    };

    /**
     * TEST-ONLY seam: wipes every `registerMenuEntry()`/`removeMenuEntry()` registration for
     * every `menuId`, restoring the registry to its pristine, nothing-ever-registered state.
     * Removals are otherwise permanent by design (see `removeMenuEntry()`'s own docblock) —
     * fine for production, where a `menuId` is never reused across unrelated lifetimes, but a
     * problem for a test suite that reuses a PRODUCTION `menuId` across many `it()`s (e.g.
     * `comment.controls`, shared with `CommentControls.vue`'s own built-in Edit/Delete
     * entries — see `commentSection.test.js`). Without this, such a suite has to rely on
     * registration/removal ORDER across tests (a later removal call permanently suppressing an
     * earlier test's entry) instead of each test starting clean. Not part of the public
     * `module.export()` surface below on purpose — call only from a test's `beforeEach`/
     * `afterEach`, never from application code.
     */
    var resetMenuRegistry = function () {
        Object.keys(menuEntries).forEach(function (menuId) {
            delete menuEntries[menuId];
        });
        Object.keys(menuRemovals).forEach(function (menuId) {
            delete menuRemovals[menuId];
        });
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

            // Hands a Vue-rendered subtree to the legacy ui.additions enhancer
            // pipeline (richtext output, gallery previews, tooltips, widget
            // auto-init via [data-ui-init], ...) — the same interop every
            // server-rendered fragment gets on pjax nav, modal open and stream
            // reloads. Applied on both `mounted` and `updated`:
            //  - Re-applying is safe for the additions that matter here: widget
            //    instantiation is cached per node (Component._getInstance() in
            //    humhub.action.js returns the existing instance instead of
            //    re-creating it). Bootstrap tooltips are unaffected either
            //    way: they are not part of the applyTo() pipeline but created
            //    lazily by a document-level mouseover listener in
            //    humhub.ui.additions.js, guarded by Tooltip.getInstance().
            //  - Re-applying on `updated` is also what makes genuinely changed
            //    content (e.g. an edited comment's re-rendered richtext output)
            //    pick the enhancers up again — that is the whole point of the
            //    hook, not just a safety margin.
            //  - showMore (humhub.ui.showMore.js) used to stack a duplicate,
            //    un-namespaced click handler on every re-apply to the same
            //    "Read more" button; fixed there (namespaced + unbound before
            //    rebound) to be re-apply-safe like the additions above,
            //    since comment entries are exactly the kind of Vue-rendered,
            //    repeatedly-`updated` content this hook targets.
            //  - A couple of additions (select2, highlightCode) are still not
            //    demonstrably idempotent on repeat calls, but aren't expected
            //    on island-rendered content, and already carry the same
            //    repeat-apply exposure today wherever legacy code reloads DOM
            //    via ui.additions (e.g. Widget.prototype.replace()).
            app.directive('additions', {
                mounted: function (el) { additions.applyTo($(el)); },
                updated: function (el) { additions.applyTo($(el)); },
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

    // Named getConfig (not `config`) so it cannot collide with module.config
    // itself once exported below — module.export() does $.extend(instance,
    // exports), and module IS the instance the core already set .config on
    // (createModule(): instance.config = require('config').module(instance),
    // consumed e.g. by instance.text() via instance.config['text']). An
    // export literally named `config` would silently clobber that property
    // for every consumer of this module.
    var getConfig = function (moduleId) {
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

    // ui.modal is registered AFTER this module within CoreApiAsset
    // (humhub.vue.js runs before humhub.ui.modal.js — see
    // CoreApiAsset::$js), so a module-scope `require('ui.modal')` captured
    // here at definition time would only ever see an empty placeholder.
    // Resolve it lazily, fresh on every call — the same call-site pattern
    // humhub.action.js itself uses (`require('ui.modal').confirm(...)`
    // inline in its click handlers) — since by the time a component
    // actually invokes confirm()/load(), the page has long finished loading
    // and ui.modal is fully registered.
    var modal = {
        confirm: function (options) {
            // ConfirmModal.prototype.open() is already Promise-based when
            // given a plain options object (as opposed to a DOM event): it
            // resolves(true) on confirm and resolves(false) on cancel/close,
            // and never rejects — no promisification needed.
            return require('ui.modal').confirm(options);
        },
        load: function (url) {
            // module.global is the singleton Modal bound to #globalModal
            // (see ui.modal.js init()); its .load() shows the loading state,
            // fetches the url and swaps in the response — the same path the
            // delegated a[data-bs-target="#globalModal"] click handler uses.
            return require('ui.modal').global.load(url);
        },
    };

    // Thin passthrough to the core `event` bus (a jQuery-backed pub/sub, see
    // humhub.core.js) so islands can subscribe to legacy events — e.g. live
    // update notifications — without importing jQuery directly. Components
    // MUST unsubscribe their own handler in `unmounted()`: this bus lives
    // for the page lifetime, not the component's.
    var events = {
        on: function (type, handler) { event.on(type, handler); },
        off: function (type, handler) { event.off(type, handler); },
        trigger: function (type, data) { event.trigger(type, data); },
    };

    module.export({
        register: register,
        isRegistered: isRegistered,
        registerSlotComponent: registerSlotComponent,
        getSlotComponents: getSlotComponents,
        registerMenuEntry: registerMenuEntry,
        removeMenuEntry: removeMenuEntry,
        getMenuEntries: getMenuEntries,
        // TEST-ONLY — see its own docblock above. Exported so test files can reach it the
        // same way they reach every other registry function, not because it belongs to the
        // documented public API surface (docs/develop/ui-js-vuejs-extensions.md's "Menu
        // entries" section deliberately does not mention it).
        resetMenuRegistry: resetMenuRegistry,
        mountElement: mountElement,
        unmountElement: unmountElement,
        getApp: getApp,
        client: client,
        i18n: i18n,
        log: log,
        getConfig: getConfig,
        url: function (route, params) {
            return urlModule.to(route, params);
        },
        modal: modal,
        events: events,
    });
});
