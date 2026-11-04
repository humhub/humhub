import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import RichTextOutput from '../../vue/RichTextOutput.vue';

// RichTextOutput reaches the platform (its oembed loader) through `@humhub/vue` - needs the
// real humhub.vue.js module registered so the shim (support/humhubVueShim.mjs) has something
// to delegate to, mirroring humhubForm.test.js's own setup for the same reason.
await import('../../resources/js/humhub/humhub.vue.js');

// v-additions is registered per Vue *app* by humhub.vue.js's island mounter
// (`app.directive('additions', { mounted, updated })`), not globally on the
// Vue runtime — a bare @vue/test-utils mount() of an internal component
// (never itself an island root) needs its own stand-in. This mirrors that
// registration verbatim: both hooks call `additions.applyTo($(el))` via the
// same `ui.additions` module humhub.vue.js requires, which
// protected/humhub/tests/js/support/setup.mjs resolves to the spyable
// `humhubStubs.additions` stub — so spying on it here observes exactly what
// production wiring would call.
const additionsDirective = {
    mounted(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
    updated(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
};

const mountWithAdditions = (component, props) => mount(component, {
    props,
    global: { directives: { additions: additionsDirective } },
});

describe('RichTextOutput', () => {
    // Every mount goes through the platform's oembed loader (the `oembed` bridge in
    // humhub.vue.js -> `humhubStubs.oembed.load`); a spy per test observes which urls a message
    // requests and, via mockImplementation, controls when the previews arrive.
    let load;

    beforeEach(() => {
        load = vi.spyOn(globalThis.humhubStubs.oembed, 'load');
        globalThis.humhubStubs.logCalls.warn.length = 0;
    });

    afterEach(() => {
        load.mockRestore();
    });

    // A promise the test settles by hand - the shape of a loader response still in flight.
    const deferred = () => {
        const result = {};
        result.promise = new Promise((resolve, reject) => {
            result.resolve = resolve;
            result.reject = reject;
        });
        return result;
    };

    it('renders nothing when message is null (e.g. a blocked comment)', () => {
        const wrapper = mountWithAdditions(RichTextOutput, { message: null });

        expect(wrapper.find('div').exists()).toBe(false);
    });

    it('renders nothing when message is an empty string', () => {
        const wrapper = mountWithAdditions(RichTextOutput, { message: '' });

        expect(wrapper.find('div').exists()).toBe(false);
    });

    describe('envelope (the element RichText::output() renders around a message)', () => {
        it('renders the markdown text as a direct child div the richtext display widget boots on', () => {
            const wrapper = mountWithAdditions(RichTextOutput, { message: 'hello world' });

            // Root is RichTextOutput's own element (receives attribute fallthrough from the
            // caller - class/data-ui-markdown/etc, see the "attribute fallthrough" describe
            // block below); the envelope is a direct CHILD, not the root itself, matching the
            // server-rendered DOM shape one level up - see commentSection.test.js's own
            // "flattened RichTextOutput wrapper" regression test.
            expect(wrapper.element.children.length).toBe(1);
            const envelope = wrapper.element.children[0];

            expect(envelope.textContent).toBe('hello world');
            expect(envelope.getAttribute('data-ui-widget')).toBe('ui.richtext.prosemirror.RichText');
            expect(envelope.hasAttribute('data-ui-init')).toBe(true);
            expect(envelope.hasAttribute('data-ui-richtext')).toBe(true);
            // Nothing else: the preset is the widget's default, no per-message options exist.
            expect(envelope.attributes.length).toBe(3);
        });

        it('renders a message without oembed links at once, without asking the loader', () => {
            const wrapper = mountWithAdditions(RichTextOutput, { message: 'plain [link](https://example.com)' });

            expect(load).not.toHaveBeenCalled();
            expect(wrapper.element.children[0].textContent).toBe('plain [link](https://example.com)');
        });
    });

    describe('XSS: message is text, never raw HTML', () => {
        it('renders a literal <script> tag as inert escaped text, never as a real element', () => {
            const wrapper = mountWithAdditions(RichTextOutput, {
                message: '<script>window.__pwned = true;</script>',
            });

            expect(wrapper.find('script').exists()).toBe(false);
            expect(globalThis.__pwned).toBeUndefined();
            expect(wrapper.element.children[0].textContent).toBe('<script>window.__pwned = true;</script>');
        });

        it('never uses v-html for the message', () => {
            const wrapper = mountWithAdditions(RichTextOutput, {
                message: '<img src=x onerror="window.__pwned = true">',
            });

            expect(wrapper.find('img').exists()).toBe(false);
            expect(globalThis.__pwned).toBeUndefined();
        });
    });

    // The richtext display widget renders an `[url](oembed:url)` link as the preview
    // `humhub.oembed.js` `get(url)` knows at render time, or as a plain link if it knows none -
    // so the previews have to be loaded (and cached there) BEFORE the envelope mounts and
    // v-additions boots the widget on it.
    describe('oembed previews are loaded before the message renders', () => {
        const video = 'https://www.youtube.com/watch?v=abc&t=30s';
        const message = 'Look: [' + video + '](oembed:' + video + ')';

        it('asks the loader for the oembed urls of the message, honoring the viewer\'s consent and without status warnings', async () => {
            const pending = deferred();
            load.mockReturnValue(pending.promise);

            const wrapper = mountWithAdditions(RichTextOutput, { message });

            expect(load).toHaveBeenCalledTimes(1);
            expect(load).toHaveBeenCalledWith([video], { consent: true, silent: true });
            // Nothing rendered yet - the widget would have rendered a plain link.
            expect(wrapper.find('div').exists()).toBe(false);

            pending.resolve({ [video]: '<div class="oembed_snippet"></div>' });
            await flushPromises();

            expect(wrapper.element.children.length).toBe(1);
            expect(wrapper.element.children[0].textContent).toBe(message);
        });

        it('boots ui.additions only once the previews are there', async () => {
            const pending = deferred();
            load.mockReturnValue(pending.promise);
            const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');

            const wrapper = mountWithAdditions(RichTextOutput, { message });
            expect(applyTo).not.toHaveBeenCalled();

            pending.resolve({});
            await flushPromises();

            expect(applyTo).toHaveBeenCalledTimes(1);
            expect(applyTo.mock.calls[0][0][0]).toBe(wrapper.element);

            applyTo.mockRestore();
        });

        it('requests every distinct url once, at most as many as a server-rendered richtext embeds', () => {
            const links = [];
            for (let i = 0; i < 12; i++) {
                links.push('[v](oembed:https://example.com/v' + i + ')');
            }
            // The first url twice: deduplicated, not counted twice against the cap.
            links.unshift('[v](oembed:https://example.com/v0)');

            mountWithAdditions(RichTextOutput, { message: links.join(' ') });

            const urls = load.mock.calls[0][0];
            expect(urls.length).toBe(10);
            expect(new Set(urls).size).toBe(10);
            expect(urls[0]).toBe('https://example.com/v0');
            expect(urls).not.toContain('https://example.com/v10');
        });

        it('ignores links of other richtext extensions', () => {
            mountWithAdditions(RichTextOutput, {
                message: '[Jane](mention:guid "http://x/u/jane") [file](file-guid:abc) [a](https://example.com)',
            });

            expect(load).not.toHaveBeenCalled();
        });

        it('still renders the message (as plain links) when the loader fails, and logs it', async () => {
            load.mockReturnValue(Promise.reject(new Error('offline')));

            const wrapper = mountWithAdditions(RichTextOutput, { message });
            await flushPromises();

            expect(wrapper.element.children[0].textContent).toBe(message);
            expect(globalThis.humhubStubs.logCalls.warn).toHaveLength(1);
            expect(globalThis.humhubStubs.logCalls.warn[0][0]).toContain('oembed');
        });

        it('takes a changed message with new oembed links down until their previews are loaded', async () => {
            const wrapper = mountWithAdditions(RichTextOutput, { message: 'before' });
            expect(wrapper.element.children[0].textContent).toBe('before');

            const pending = deferred();
            load.mockReturnValue(pending.promise);
            await wrapper.setProps({ message });

            expect(load).toHaveBeenCalledWith([video], { consent: true, silent: true });
            expect(wrapper.find('div').exists()).toBe(false);

            pending.resolve({});
            await flushPromises();

            expect(wrapper.element.children[0].textContent).toBe(message);
        });

        it('a load that finishes after the message changed again is stale and does not interfere', async () => {
            const stale = deferred();
            load.mockReturnValue(stale.promise);

            const wrapper = mountWithAdditions(RichTextOutput, { message });
            // Edited to a message without oembed links while the previews are still loading:
            // renders at once.
            await wrapper.setProps({ message: 'after' });
            expect(wrapper.element.children[0].textContent).toBe('after');

            const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');
            stale.resolve({});
            await flushPromises();

            // No re-render, no second widget boot caused by the stale load.
            expect(wrapper.element.children[0].textContent).toBe('after');
            expect(applyTo).not.toHaveBeenCalled();

            applyTo.mockRestore();
        });
    });

    describe('attribute fallthrough (caller-owned layout/styling)', () => {
        it('lands class/data-ui-markdown/data-ui-show-more on the root, one level above the envelope', () => {
            const wrapper = mount(RichTextOutput, {
                props: { message: 'hi' },
                attrs: { class: 'comment-message', 'data-ui-markdown': '', 'data-ui-show-more': '', 'data-read-more-text': 'Read more...' },
                global: { directives: { additions: additionsDirective } },
            });

            expect(wrapper.classes()).toContain('comment-message');
            expect(wrapper.attributes('data-ui-markdown')).toBe('');
            expect(wrapper.attributes('data-read-more-text')).toBe('Read more...');
            // Not duplicated onto the inner envelope.
            expect(wrapper.element.children[0].hasAttribute('data-ui-markdown')).toBe(false);
        });
    });

    it('applies ui.additions to the rendered envelope on mount', () => {
        const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');

        const wrapper = mountWithAdditions(RichTextOutput, { message: 'hi' });

        expect(applyTo).toHaveBeenCalledTimes(1);
        expect(applyTo.mock.calls[0][0][0]).toBe(wrapper.element);

        applyTo.mockRestore();
    });

    it('does not apply ui.additions when there is nothing rendered', () => {
        const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');

        mountWithAdditions(RichTextOutput, { message: null });

        expect(applyTo).not.toHaveBeenCalled();

        applyTo.mockRestore();
    });

    it('re-applies ui.additions after the message prop changes (updated hook)', async () => {
        const wrapper = mountWithAdditions(RichTextOutput, { message: 'before' });

        const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');
        await wrapper.setProps({ message: 'after' });

        expect(applyTo).toHaveBeenCalledTimes(1);
        expect(wrapper.text()).toBe('after');

        applyTo.mockRestore();
    });

    // The legacy richtext DISPLAY widget caches its instance in jQuery `.data()` on the
    // envelope DOM node itself and initializes once per node, so an in-place text swap on the
    // SAME node (Vue's default for a same-position, same-tag child with no key) would leave
    // that cache in place and the widget would never re-render the new markdown - the user
    // would see raw markdown text. The `:key` on the envelope (see the component's own
    // docblock) forces Vue to destroy the old node and mount a genuinely new one instead.
    it('recreates the envelope element itself (not just its text) when the message changes', async () => {
        const wrapper = mountWithAdditions(RichTextOutput, { message: 'first markdown' });

        const oldEnvelope = wrapper.element.children[0];

        await wrapper.setProps({ message: 'second markdown' });

        const newEnvelope = wrapper.element.children[0];

        expect(newEnvelope).not.toBe(oldEnvelope);
        // The old node was actually removed from the tree, not just superseded in our local
        // reference to it.
        expect(oldEnvelope.parentNode).toBeNull();
        expect(newEnvelope.textContent).toBe('second markdown');
    });
});
