import { beforeEach, describe, expect, it } from 'vitest';
import ExtensionSlot from '../../vue/ExtensionSlot.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

// DOM fixture helper: build elements without HTML-string parsing (copied from vue.test.js).
const createTag = (tag, attributes = {}, parent = document.body) => {
    const el = document.createElement(tag);
    Object.entries(attributes).forEach(([name, value]) => el.setAttribute(name, value));
    parent.appendChild(el);
    return el;
};

describe('ExtensionSlot', () => {
    beforeEach(() => {
        document.body.replaceChildren();
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.logCalls.warn.length = 0;
        globalThis.humhubStubs.logCalls.debug.length = 0;
    });

    it('renders nothing for a slot with no registrations', async () => {
        vueModule.register('TestExtSlotHostEmpty', {
            components: { ExtensionSlot },
            render() {
                return Vue.h(ExtensionSlot, { name: 'test.ext.empty' });
            },
        });

        const el = createTag('test-ext-slot-host-empty');
        await vueModule.mountElement(el);

        expect(el.querySelectorAll('*').length).toBe(0);
        expect(globalThis.humhubStubs.logCalls.warn.length).toBe(0);
    });

    it('renders registered slot components sorted by sortOrder, passing context down as props', async () => {
        vueModule.register('TestExtSlotItemFirst', {
            props: { label: String },
            render() {
                return Vue.h('span', { class: 'ext-first' }, this.label);
            },
        });
        vueModule.register('TestExtSlotItemSecond', {
            props: { label: String },
            render() {
                return Vue.h('span', { class: 'ext-second' }, this.label);
            },
        });

        vueModule.registerSlotComponent('test.ext.render', 'TestExtSlotItemSecond', { sortOrder: 50 });
        vueModule.registerSlotComponent('test.ext.render', 'TestExtSlotItemFirst', { sortOrder: 10 });

        vueModule.register('TestExtSlotHostRender', {
            components: { ExtensionSlot },
            render() {
                return Vue.h(ExtensionSlot, { name: 'test.ext.render', context: { label: 'hi' } });
            },
        });

        const el = createTag('test-ext-slot-host-render');
        await vueModule.mountElement(el);

        const spans = el.querySelectorAll('span');
        expect(spans.length).toBe(2);
        expect(spans[0].className).toBe('ext-first');
        expect(spans[1].className).toBe('ext-second');
        expect(spans[0].textContent).toBe('hi');
        expect(spans[1].textContent).toBe('hi');
    });

    it('filters out an entry whose component is not (yet) registered, without a Vue warning', async () => {
        vueModule.registerSlotComponent('test.ext.missing', 'TestExtSlotNeverRegistered');

        vueModule.register('TestExtSlotHostMissing', {
            components: { ExtensionSlot },
            render() {
                return Vue.h(ExtensionSlot, { name: 'test.ext.missing' });
            },
        });

        const el = createTag('test-ext-slot-host-missing');
        await vueModule.mountElement(el);

        expect(el.querySelectorAll('*').length).toBe(0);
        expect(globalThis.humhubStubs.logCalls.warn.length).toBe(0);
        expect(globalThis.humhubStubs.logCalls.error.length).toBe(0);
    });

    it('picks up a late SLOT registration in an already-mounted island, without remounting it', async () => {
        let hostMountCount = 0;
        vueModule.register('TestExtSlotItemLateSlot', {
            render: () => Vue.h('mark', 'late-slot'),
        });
        vueModule.register('TestExtSlotHostLateSlot', {
            components: { ExtensionSlot },
            mounted() {
                hostMountCount++;
            },
            render() {
                return Vue.h(ExtensionSlot, { name: 'test.ext.lateslot' });
            },
        });

        const el = createTag('test-ext-slot-host-late-slot');
        await vueModule.mountElement(el);

        expect(el.querySelector('mark')).toBeNull();
        expect(hostMountCount).toBe(1);

        vueModule.registerSlotComponent('test.ext.lateslot', 'TestExtSlotItemLateSlot');
        await Vue.nextTick();

        expect(el.querySelector('mark')).not.toBeNull();
        expect(el.querySelector('mark').textContent).toBe('late-slot');
        // Reactive update, not a remount.
        expect(hostMountCount).toBe(1);
    });

    it('picks up a late COMPONENT registration once it registers, for a slot entry registered earlier', async () => {
        vueModule.registerSlotComponent('test.ext.latecomponent', 'TestExtSlotItemLateComponent');

        vueModule.register('TestExtSlotHostLateComponent', {
            components: { ExtensionSlot },
            render() {
                return Vue.h(ExtensionSlot, { name: 'test.ext.latecomponent' });
            },
        });

        const el = createTag('test-ext-slot-host-late-component');
        await vueModule.mountElement(el);

        expect(el.querySelector('i')).toBeNull();

        vueModule.register('TestExtSlotItemLateComponent', { render: () => Vue.h('i', 'now-here') });
        await Vue.nextTick();

        expect(el.querySelector('i')).not.toBeNull();
        expect(el.querySelector('i').textContent).toBe('now-here');
    });
});
