import { beforeEach, describe, expect, it, vi } from 'vitest';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

// DOM fixture helper: build elements without HTML-string parsing (copied
// from vue.test.js).
const createTag = (tag, attributes = {}, parent = document.body) => {
    const el = document.createElement(tag);
    Object.entries(attributes).forEach(([name, value]) => el.setAttribute(name, value));
    parent.appendChild(el);
    return el;
};

describe('humhub.vue bridge additions', () => {
    beforeEach(() => {
        document.body.replaceChildren();
        globalThis.humhubStubs.logCalls.error.length = 0;
        // Reset the modal stub to its defaults; individual tests override.
        globalThis.humhubStubs.modal.confirm = () => Promise.resolve(true);
        globalThis.humhubStubs.modal.global.load = () => Promise.resolve();
        // Reset the event bus between tests so handlers don't leak across.
        globalThis.humhubStubs.event._handlers.clear();
    });

    describe('v-additions directive', () => {
        it('applies ui.additions to the bound element on mount', async () => {
            const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');

            vueModule.register('TestAdditionsAlpha', {
                render() {
                    return Vue.withDirectives(Vue.h('div', { class: 'target' }), [[Vue.resolveDirective('additions')]]);
                },
            });

            const el = createTag('test-additions-alpha');
            await vueModule.mountElement(el);

            expect(applyTo).toHaveBeenCalled();
            const [$applied] = applyTo.mock.calls[applyTo.mock.calls.length - 1];
            expect($applied[0]).toBe(el.querySelector('.target'));

            applyTo.mockRestore();
        });

        it('re-applies ui.additions when the bound element updates', async () => {
            let bump;
            vueModule.register('TestAdditionsBeta', {
                data() {
                    return { n: 0 };
                },
                created() {
                    bump = () => {
                        this.n++;
                    };
                },
                render() {
                    return Vue.withDirectives(
                        Vue.h('div', { class: 'target' }, String(this.n)),
                        [[Vue.resolveDirective('additions')]],
                    );
                },
            });

            const el = createTag('test-additions-beta');
            await vueModule.mountElement(el);

            const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');
            bump();
            await Vue.nextTick();

            expect(applyTo).toHaveBeenCalled();
            const [$applied] = applyTo.mock.calls[applyTo.mock.calls.length - 1];
            expect($applied[0]).toBe(el.querySelector('.target'));
            expect(el.querySelector('.target').textContent).toBe('1');

            applyTo.mockRestore();
        });
    });

    describe('modal export', () => {
        it('confirm() delegates to ui.modal.confirm and resolves true', async () => {
            const confirm = vi.fn(() => Promise.resolve(true));
            globalThis.humhubStubs.modal.confirm = confirm;

            const options = { header: 'Delete comment?', body: 'Are you sure?' };
            await expect(vueModule.modal.confirm(options)).resolves.toBe(true);
            expect(confirm).toHaveBeenCalledWith(options);
        });

        it('confirm() resolves false when the stub reports cancellation', async () => {
            globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(false));

            await expect(vueModule.modal.confirm({})).resolves.toBe(false);
        });

        it('load() delegates to the global modal with the given url', async () => {
            const load = vi.fn(() => Promise.resolve());
            globalThis.humhubStubs.modal.global.load = load;

            await vueModule.modal.load('/comment/comment/edit?id=3');

            expect(load).toHaveBeenCalledWith('/comment/comment/edit?id=3');
        });
    });

    describe('events export', () => {
        it('delivers a triggered event to an "on" subscriber and stops after "off"', () => {
            const handler = vi.fn();
            vueModule.events.on('test:bridge:thing', handler);

            vueModule.events.trigger('test:bridge:thing', { value: 1 });
            expect(handler).toHaveBeenCalledTimes(1);
            expect(handler.mock.calls[0][1]).toEqual({ value: 1 });

            vueModule.events.off('test:bridge:thing', handler);
            vueModule.events.trigger('test:bridge:thing', { value: 2 });
            expect(handler).toHaveBeenCalledTimes(1);
        });

        it('lets an island subscribe on mount and unsubscribe on unmount', async () => {
            const received = [];

            vueModule.register('TestEventsGamma', {
                mounted() {
                    this._onThing = function (evt, data) {
                        received.push(data);
                    };
                    vueModule.events.on('test:bridge:live', this._onThing);
                },
                unmounted() {
                    vueModule.events.off('test:bridge:live', this._onThing);
                },
                render: () => Vue.h('i', 'x'),
            });

            const el = createTag('test-events-gamma');
            await vueModule.mountElement(el);

            vueModule.events.trigger('test:bridge:live', 'first');
            expect(received).toEqual(['first']);

            vueModule.unmountElement(el);
            vueModule.events.trigger('test:bridge:live', 'second');
            expect(received).toEqual(['first']); // unmount unsubscribed the handler
        });
    });
});
