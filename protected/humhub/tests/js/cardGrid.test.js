import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import { h } from 'vue';
import { mount } from '@vue/test-utils';
import CardGrid from '../../vue/CardGrid.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const mountGrid = (props = {}, slots = {}) => mount(CardGrid, {
    props: { items: [], ...props },
    slots: { card: ({ item }) => h('span', { class: 'thing' }, item.name), ...slots },
});

describe('CardGrid', () => {
    beforeEach(() => {
        document.body.replaceChildren();
    });

    it('renders one cell per item, with the extra card class', () => {
        const wrapper = mountGrid({ items: [{ id: 'a', name: 'A' }, { id: 'b', name: 'B' }], cardClass: 'is-module' });

        const cells = wrapper.findAll('.c-card-grid__cell');
        expect(cells).toHaveLength(2);
        expect(cells[0].classes()).toContain('is-module');
        expect(cells[0].attributes('data-id')).toBe('a');
        expect(wrapper.findAll('.thing').map((node) => node.text())).toEqual(['A', 'B']);
    });

    it('staggers the cells by their index within the page they were loaded with', () => {
        const items = ['a', 'b', 'c', 'd', 'e'].map((id) => ({ id, name: id }));
        const wrapper = mountGrid({ items, pageStarts: [0, 3] }, {
            card: ({ item, index }) => h('span', { class: 'thing' }, `${item.name}${index}`),
        });

        expect(wrapper.findAll('.c-card-grid__cell').map((cell) => cell.element.style.getPropertyValue('--card-stagger-index')))
            .toEqual(['0', '1', '2', '0', '1']);
        expect(wrapper.findAll('.thing').map((node) => node.text())).toEqual(['a0', 'b1', 'c2', 'd0', 'e1']);
    });

    it('shows skeletons while the first page loads', () => {
        const wrapper = mountGrid({ loading: true, skeletonCount: 3 });

        const skeletons = wrapper.findAll('.c-card-skeleton');
        expect(skeletons).toHaveLength(3);
        expect(skeletons[0].attributes('aria-hidden')).toBe('true');
        expect(skeletons[0].find('.c-card-skeleton__image').exists()).toBe(true);
        expect(skeletons[0].findAll('.c-card-skeleton__line')).toHaveLength(2);
        expect(skeletons[0].find('.c-card-skeleton__footer .c-card-skeleton__action').exists()).toBe(true);
        expect(skeletons[0].find('.c-card-skeleton__footer .c-card-skeleton__icon').exists()).toBe(true);
        expect(wrapper.find('.c-card-grid__cells').attributes('aria-busy')).toBe('true');
    });

    it('renders the skeleton slot per skeleton cell with its index', () => {
        const wrapper = mountGrid({ loading: true, skeletonCount: 3 }, {
            skeleton: ({ index }) => h('span', { class: 'own-skeleton' }, String(index)),
        });

        expect(wrapper.findAll('.c-card-skeleton')).toHaveLength(0);
        const cells = wrapper.findAll('.c-card-grid__cell--skeleton');
        expect(cells).toHaveLength(3);
        expect(cells.map((cell) => cell.find('.own-skeleton').text())).toEqual(['0', '1', '2']);
    });

    it('keeps the cards and marks the grid while a filter change loads', () => {
        const wrapper = mountGrid({ loading: true, items: [{ id: 'a', name: 'A' }] });

        expect(wrapper.findAll('.c-card-skeleton')).toHaveLength(0);
        expect(wrapper.classes()).toContain('is-loading');
    });

    it('renders the empty state, overridable by slot', () => {
        expect(mountGrid().find('.c-card-grid__message--empty').text()).toContain('No results found!');
        expect(mountGrid({}, { empty: () => 'Nothing here' }).find('.c-card-grid__message--empty').text()).toBe('Nothing here');
    });

    it('renders an error with a retry button', async () => {
        const wrapper = mountGrid({ error: 'Down' });

        expect(wrapper.find('[role="alert"]').text()).toBe('Down');
        await wrapper.find('.c-card-grid__message--error button').trigger('click');
        expect(wrapper.emitted('retry')).toHaveLength(1);
    });

    it('offers more results while there are further pages', async () => {
        const wrapper = mountGrid({ items: [{ id: 'a', name: 'A' }], hasMore: true });

        await wrapper.find('.cards-more button').trigger('click');
        expect(wrapper.emitted('load-more')).toHaveLength(1);
        await wrapper.setProps({ loading: true });
        expect(wrapper.find('.cards-more button').exists()).toBe(false);
        expect(wrapper.find('.cards-more .spinner-border').exists()).toBe(true);
    });

    // The error sits below the already-rendered grid here (`items` is non-empty) - it must still
    // emit `retry`, not `load-more`: the owner (CardDirectory) decides what to re-fetch, and a
    // failed page is re-run the same way a failed first page is.
    it('emits retry, not load-more, for an error below an already-populated grid', async () => {
        const wrapper = mountGrid({ items: [{ id: 'a', name: 'A' }], error: 'Down' });

        expect(wrapper.find('.cards-error [role="alert"]').text()).toBe('Down');
        await wrapper.find('.cards-error button').trigger('click');
        expect(wrapper.emitted('retry')).toHaveLength(1);
        expect(wrapper.emitted('load-more')).toBeUndefined();
    });
});

// A stubbed global IntersectionObserver: records every observe()/unobserve()/disconnect() call
// and keeps the callback CardGrid registered, so a test can fire a synthetic intersection entry
// without a real browser layout engine.
describe('CardGrid auto-paging', () => {
    let realIntersectionObserver;
    let instances;

    class FakeIntersectionObserver {
        constructor(callback) {
            this.callback = callback;
            this.observeCalls = [];
            this.unobserveCalls = [];
            this.disconnectCalls = 0;
            instances.push(this);
        }

        observe(target) {
            this.observeCalls.push(target);
        }

        unobserve(target) {
            this.unobserveCalls.push(target);
        }

        disconnect() {
            this.disconnectCalls += 1;
        }
    }

    beforeEach(() => {
        document.body.replaceChildren();
        instances = [];
        realIntersectionObserver = globalThis.IntersectionObserver;
        globalThis.IntersectionObserver = FakeIntersectionObserver;
    });

    afterEach(() => {
        globalThis.IntersectionObserver = realIntersectionObserver;
    });

    it('re-arms the observer once loading finishes, ignores an intersection while loading, and disconnects on unmount', async () => {
        const wrapper = mountGrid({ items: [{ id: 'a', name: 'A' }], hasMore: true });
        const observer = instances[0];
        const sentinel = wrapper.find('.cards-more').element;
        const fire = () => observer.callback([{ isIntersecting: true, target: sentinel }]);

        expect(observer.observeCalls).toEqual([sentinel]);

        fire();
        expect(wrapper.emitted('load-more')).toHaveLength(1);

        // A second page starts loading - the sentinel never left the viewport, but a further
        // intersection must not emit another load-more while it is in flight.
        await wrapper.setProps({ loading: true });
        fire();
        expect(wrapper.emitted('load-more')).toHaveLength(1);

        // Loading finishes: since the sentinel is still visible, nothing would ever refire on
        // its own without re-arming it - assert the same observer/target were re-armed.
        await wrapper.setProps({ loading: false });
        expect(observer.unobserveCalls).toEqual([sentinel]);
        expect(observer.observeCalls).toEqual([sentinel, sentinel]);

        wrapper.unmount();
        expect(observer.disconnectCalls).toBe(1);
    });

    // `items: []` keeps the below-grid area on the `hasMore` branch (the dedicated
    // error-with-items block only replaces it once `items.length` is truthy - see the
    // "emits retry, not load-more" test above), so the sentinel stays mounted and this
    // exercises the callback's own `!this.error` guard in isolation.
    it('ignores an intersection while an error is set', () => {
        const wrapper = mountGrid({ items: [], hasMore: true, error: 'Down' });
        const observer = instances[0];
        const sentinel = wrapper.find('.cards-more').element;

        observer.callback([{ isIntersecting: true, target: sentinel }]);

        expect(wrapper.emitted('load-more')).toBeUndefined();
    });
});
