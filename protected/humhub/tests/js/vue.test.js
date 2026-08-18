import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

// DOM fixture helper: build elements without HTML-string parsing
const createTag = (tag, attributes = {}, parent = document.body) => {
    const el = document.createElement(tag);
    Object.entries(attributes).forEach(([name, value]) => el.setAttribute(name, value));
    parent.appendChild(el);
    return el;
};

describe('humhub.vue', () => {
    beforeEach(() => {
        document.body.replaceChildren();
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.logCalls.warn.length = 0;
        globalThis.humhubStubs.logCalls.debug.length = 0;
    });

    it('mounts a registered component on its kebab-case tag and coerces props', async () => {
        const el = createTag('test-badge-alpha', { label: 'Hello', count: '3', active: 'false' });

        vueModule.register('TestBadgeAlpha', {
            props: { label: String, count: Number, active: Boolean },
            render() {
                // count + 1 proves Number coercion ('3' + 1 would be '31')
                return Vue.h('strong', `${this.label}:${this.count + 1}:${this.active}`);
            },
        });

        await vi.waitFor(() => expect(el.querySelector('strong')).not.toBeNull());
        expect(el.querySelector('strong').textContent).toBe('Hello:4:false');
    });

    it('mounts via data-vue-component with JSON data-props', async () => {
        vueModule.register('TestBadgeBeta', {
            props: { items: Array },
            render() {
                return Vue.h('em', this.items.join(','));
            },
        });

        const el = createTag('div', {
            'data-vue-component': 'TestBadgeBeta',
            'data-props': JSON.stringify({ items: ['a', 'b'] }),
        });

        await vueModule.mountElement(el);
        expect(el.querySelector('em').textContent).toBe('a,b');
    });

    it('lets individual attributes override JSON props', async () => {
        vueModule.register('TestBadgeOverride', {
            props: { label: String },
            render() {
                return Vue.h('i', this.label);
            },
        });

        const el = createTag('test-badge-override', {
            props: JSON.stringify({ label: 'json' }),
            label: 'attr',
        });

        await vueModule.mountElement(el);
        expect(el.querySelector('i').textContent).toBe('attr');
    });

    it('ignores duplicate component names at debug level and keeps the first registration', async () => {
        vueModule.register('TestBadgeGamma', { render: () => Vue.h('i') });
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.logCalls.debug.length = 0;
        vueModule.register('TestBadgeGamma', { render: () => Vue.h('b') });
        expect(globalThis.humhubStubs.logCalls.error.length).toBe(0);
        expect(globalThis.humhubStubs.logCalls.debug.length).toBe(1);

        const el = createTag('test-badge-gamma');
        await vueModule.mountElement(el);
        expect(el.querySelector('i')).not.toBeNull();
        expect(el.querySelector('b')).toBeNull();
    });

    it('rejects a second component whose derived tag collides with an existing one', async () => {
        // PdfViewerOmega and PDFViewerOmega both derive the tag <pdf-viewer-omega>
        vueModule.register('PdfViewerOmega', { render: () => Vue.h('u', 'first') });
        globalThis.humhubStubs.logCalls.error.length = 0;
        vueModule.register('PDFViewerOmega', { render: () => Vue.h('s', 'second') });
        expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);

        const el = createTag('pdf-viewer-omega');
        await vueModule.mountElement(el);
        expect(el.querySelector('u')).not.toBeNull();
        expect(el.querySelector('s')).toBeNull();
    });

    it('rejects names whose derived tag contains no dash', () => {
        vueModule.register('Badge', { render: () => Vue.h('i') });
        expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
    });

    it('accepts single-word PascalCase names that still produce a dashed tag, like HButton', async () => {
        vueModule.register('HButton', { render: () => Vue.h('b', 'h') });

        const el = createTag('h-button');
        await vueModule.mountElement(el);
        expect(el.querySelector('b').textContent).toBe('h');
    });

    it('logs safely before the core attaches module.log', () => {
        const attachedLog = vueModule.log;
        const consoleError = vi.spyOn(console, 'error').mockImplementation(() => {});
        vueModule.log = undefined; // simulate the pre-ready window
        vueModule.register('Badge', { render: () => Vue.h('i') }); // invalid name — must not throw
        expect(consoleError).toHaveBeenCalled();
        vueModule.log = attachedLog;
        consoleError.mockRestore();
    });

    it('makes all registered components available inside other islands', async () => {
        vueModule.register('TestChildDelta', { render: () => Vue.h('mark', 'child') });
        vueModule.register('TestParentDelta', {
            render: () => Vue.h(Vue.resolveComponent('TestChildDelta')),
        });

        const el = createTag('test-parent-delta');
        await vueModule.mountElement(el);
        expect(el.querySelector('mark').textContent).toBe('child');
    });

    it('exposes late-registered components inside already-mounted islands', async () => {
        vueModule.register('TestLateParentNu', { render: () => Vue.h('span', 'parent') });

        const el = createTag('test-late-parent-nu');
        await vueModule.mountElement(el);

        const lateChild = { render: () => Vue.h('mark', 'late') };
        vueModule.register('TestLateChildKappa', lateChild);

        expect(vueModule.getApp(el).component('TestLateChildKappa')).toBe(lateChild);
    });

    it('preloads declared i18n categories before mounting', async () => {
        const preload = vi.spyOn(globalThis.humhubStubs.i18n, 'preload');
        vueModule.register('TestI18nEpsilon', {
            i18nCategories: ['TestModule.base'],
            render: () => Vue.h('i', 'x'),
        });

        const el = createTag('test-i18n-epsilon');
        await vueModule.mountElement(el);

        expect(preload).toHaveBeenCalledWith(['TestModule.base']);
        preload.mockRestore();
    });

    it('shares a single in-flight mount when mountElement is called concurrently', async () => {
        let mountCount = 0;
        vueModule.register('TestConcurrentMountLambda', {
            mounted() {
                mountCount += 1;
            },
            render: () => Vue.h('i', 'x'),
        });

        const el = createTag('test-concurrent-mount-lambda');
        const [first, second] = await Promise.all([vueModule.mountElement(el), vueModule.mountElement(el)]);

        expect(first).not.toBeNull();
        expect(first).toBe(second);
        expect(mountCount).toBe(1);
    });

    it('cancels a mount whose element is unmounted while i18n preload is still pending', async () => {
        let mounted = false;
        let resolvePreload;
        const preload = vi.spyOn(globalThis.humhubStubs.i18n, 'preload').mockImplementation(
            () => new Promise((resolve) => { resolvePreload = resolve; }),
        );

        vueModule.register('TestCancelMu', {
            i18nCategories: ['TestModule.cancel'],
            mounted() {
                mounted = true;
            },
            render: () => Vue.h('i', 'x'),
        });

        const layout = createTag('div', { id: 'layout-content' });
        const el = createTag('test-cancel-mu', {}, layout);

        const mountPromise = vueModule.mountElement(el);
        vueModule.unload();
        resolvePreload();

        const app = await mountPromise;

        expect(app).toBeNull();
        expect(vueModule.getApp(el)).toBeNull();
        expect(el.children.length).toBe(0);
        expect(mounted).toBe(false);

        preload.mockRestore();
    });

    it('does not leak the pending entry on a plain cancellation, leaving a later remount unblocked', async () => {
        let mountCount = 0;
        let resolvePreload;
        const preload = vi.spyOn(globalThis.humhubStubs.i18n, 'preload').mockImplementation(
            () => new Promise((resolve) => { resolvePreload = resolve; }),
        );

        vueModule.register('TestDrainPi', {
            i18nCategories: ['TestModule.drain'],
            mounted() {
                mountCount += 1;
            },
            render: () => Vue.h('i', 'x'),
        });

        const layout = createTag('div', { id: 'layout-content' });
        const el = createTag('test-drain-pi', {}, layout);

        const firstMount = vueModule.mountElement(el);
        vueModule.unload(); // cancels — never remounted before this settles
        resolvePreload();

        expect(await firstMount).toBeNull();

        preload.mockRestore(); // back to the default fast-resolving preload stub

        // A stale `pending` entry would still be there and would be returned
        // as-is instead of starting a fresh mount.
        const secondMount = await vueModule.mountElement(el);

        expect(secondMount).not.toBeNull();
        expect(vueModule.getApp(el)).toBe(secondMount);
        expect(el.children.length).toBe(1);
        expect(mountCount).toBe(1);
    });

    it('mounts the second island when a reservation is superseded before the first mount settles', async () => {
        let mountCount = 0;
        let resolvePreload;
        // A single shared deferred: both mountElement() calls below preload
        // against the same pending promise, so resolving it once settles both.
        const sharedPreload = new Promise((resolve) => { resolvePreload = resolve; });
        const preload = vi.spyOn(globalThis.humhubStubs.i18n, 'preload').mockImplementation(() => sharedPreload);

        vueModule.register('TestSupersedeOmicron', {
            i18nCategories: ['TestModule.supersede'],
            mounted() {
                mountCount += 1;
            },
            render: () => Vue.h('i', 'x'),
        });

        const el = createTag('test-supersede-omicron');

        const first = vueModule.mountElement(el);
        vueModule.unmountElement(el);
        const second = vueModule.mountElement(el);
        resolvePreload();

        const [firstApp, secondApp] = await Promise.all([first, second]);

        expect(firstApp).toBeNull();
        expect(secondApp).not.toBeNull();
        expect(mountCount).toBe(1);
        expect(vueModule.getApp(el)).toBe(secondApp);
        expect(el.children.length).toBe(1);

        preload.mockRestore();
    });

    it('leaves unregistered component mount points untouched', async () => {
        const el = createTag('div', { 'data-vue-component': 'TestMissingTheta' });
        const placeholder = document.createElement('span');
        placeholder.textContent = 'placeholder';
        el.appendChild(placeholder);

        const app = await vueModule.mountElement(el);
        expect(app).toBeNull();
        expect(el.firstElementChild).toBe(placeholder);
        expect(el.textContent).toBe('placeholder');
    });

    it('unmounts islands inside #layout-content on unload()', async () => {
        let unmounted = false;
        vueModule.register('TestUnloadZeta', {
            unmounted() {
                unmounted = true;
            },
            render: () => Vue.h('i', 'x'),
        });

        const layout = createTag('div', { id: 'layout-content' });
        const el = createTag('test-unload-zeta', {}, layout);
        await vueModule.mountElement(el);
        expect(vueModule.getApp(el)).not.toBeNull();

        vueModule.unload();
        expect(unmounted).toBe(true);
        expect(vueModule.getApp(el)).toBeNull();
    });

    it('unmounts islands whose root node is removed from the DOM', async () => {
        let unmounted = false;
        vueModule.register('TestObserverEta', {
            unmounted() {
                unmounted = true;
            },
            render: () => Vue.h('i', 'x'),
        });

        const wrapper = createTag('div');
        const el = createTag('test-observer-eta', {}, wrapper);
        await vueModule.mountElement(el);

        wrapper.remove();
        await vi.waitFor(() => expect(unmounted).toBe(true));
        expect(vueModule.getApp(el)).toBeNull();
    });

    it('mounts components in injected fragments via ui.additions', async () => {
        vueModule.register('TestInjectIota', { render: () => Vue.h('i', 'io') });

        const fragment = createTag('div');
        createTag('test-inject-iota', {}, fragment);
        globalThis.humhubStubs.additions.applyTo(jQuery(fragment));

        await vi.waitFor(() => expect(jQuery(fragment).find('i').length).toBe(1));
    });

    it('does not mount ahead of the ready sweep, mirroring the real initial-page-load gate', async () => {
        globalThis.humhub.initialized = false;
        try {
            const el = createTag('test-ready-gate-xi');

            vueModule.register('TestReadyGateXi', { render: () => Vue.h('i', 'x') });

            expect(el.children.length).toBe(0);
            expect(vueModule.getApp(el)).toBeNull();

            globalThis.humhub.initialized = true;
            globalThis.humhubStubs.additions.applyTo(jQuery(document.body));

            await vi.waitFor(() => expect(el.querySelector('i')).not.toBeNull());
        } finally {
            globalThis.humhub.initialized = true;
        }
    });

    describe('url()', () => {
        // vueModule.config is the real per-module jsConfig object (as set by
        // CoreJsConfig via registerJsConfig) — getConfig() is a separate,
        // differently-named accessor precisely so it does not collide with
        // and overwrite this property (see humhub.vue.js).
        afterEach(() => {
            delete vueModule.config.urlTemplate;
        });

        it('fills a pretty-URL template and appends params', () => {
            vueModule.config.urlTemplate = '/__route__';
            expect(vueModule.url('/like/like/like', { recordId: 7 })).toBe('/like/like/like?recordId=7');
        });

        it('encodes the route and appends params to a query-string template', () => {
            vueModule.config.urlTemplate = '/index.php?r=__route__';
            expect(vueModule.url('like/like/unlike', { recordId: 7 })).toBe('/index.php?r=like%2Flike%2Funlike&recordId=7');
        });

        it('omits the trailing separator when there are no params', () => {
            vueModule.config.urlTemplate = '/__route__';
            expect(vueModule.url('/like/like/like')).toBe('/like/like/like');
        });

        it('falls back to a root-relative URL and logs once when no template is configured', () => {
            delete vueModule.config.urlTemplate;
            globalThis.humhubStubs.logCalls.error.length = 0;

            expect(vueModule.url('/a/b', { x: 1 })).toBe('/a/b?x=1');
            expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
        });
    });
});
