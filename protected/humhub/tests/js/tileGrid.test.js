import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { h, nextTick, toRaw } from 'vue';
import { mount } from '@vue/test-utils';
import TileGrid from '../../vue/TileGrid.vue';

// TileGrid reads `i18n` from `@humhub/vue` for its labels - needs the real humhub.vue.js module
// registered so the @humhub/vue shim has something to delegate to (see pathBar.test.js).
await import('../../resources/js/humhub/humhub.vue.js');

const items = [
    { id: 'f:1', title: 'Brand' },
    { id: 'a:2', title: 'Guide.pdf' },
    { id: 'a:3', title: 'Deck.pdf' },
];

const slots = {
    thumb: ({ item }) => h('i', { class: 'thumb' }, item.id),
    name: ({ item }) => h('a', { href: '#', class: 'name-link' }, item.title),
    meta: () => h('span', '2 items'),
};

const grid = (props = {}, extraSlots = {}) => mount(TileGrid, {
    props: { items, ...props },
    slots: { ...slots, ...extraSlots },
});

describe('TileGrid', () => {
    describe('tiles', () => {
        it('renders one tile per item with its slots', () => {
            const wrapper = grid();
            const tiles = wrapper.findAll('.c-tile-grid__tile');

            expect(tiles).toHaveLength(3);
            expect(tiles[0].attributes('data-key')).toBe('f:1');
            expect(tiles[0].find('.c-tile-grid__thumb .thumb').exists()).toBe(true);
            expect(tiles[0].find('.c-tile-grid__name .name-link').text()).toBe('Brand');
            expect(tiles[0].find('.c-tile-grid__meta').text()).toBe('2 items');
        });

        it('shows skeletons while the first items load, and the empty slot without items', () => {
            expect(grid({ items: [], loading: true, skeletonCount: 4 }).findAll('.c-tile-grid__skeleton')).toHaveLength(4);

            const empty = grid({ items: [] }, { empty: () => h('p', { class: 'nothing' }, 'Empty') });
            expect(empty.find('.nothing').exists()).toBe(true);
            expect(empty.find('.c-tile-grid__skeleton').exists()).toBe(false);
        });

        it('marks the grid busy while loading', () => {
            expect(grid({ items: [], loading: true }).attributes('aria-busy')).toBe('true');
            expect(grid().attributes('aria-busy')).toBeUndefined();
        });
    });

    describe('selection', () => {
        it('offers no checkbox unless selectable', () => {
            expect(grid().find('.c-tile-grid__check').exists()).toBe(false);
        });

        it('reflects the selection and switches the grid into selecting mode', () => {
            const wrapper = grid({ selectable: true, selection: ['a:2'] });
            const [folder, guide] = wrapper.findAll('.c-tile-grid__tile');

            expect(wrapper.classes()).toContain('is-selecting');
            expect(guide.classes()).toContain('is-selected');
            expect(guide.find('input').element.checked).toBe(true);
            expect(folder.find('input').element.checked).toBe(false);
            expect(guide.find('input').attributes('aria-label')).toBe('Select Guide.pdf');
        });

        it('emits a toggle, as a range with Shift', async () => {
            const wrapper = grid({ selectable: true });
            const inputs = wrapper.findAll('.c-tile-grid__check input');

            await inputs[0].trigger('click');
            await inputs[2].trigger('click', { shiftKey: true });

            const toggles = wrapper.emitted('toggle-select');
            expect(toggles).toHaveLength(2);
            expect(toRaw(toggles[0][0])).toBe(items[0]);
            expect(toggles[0][1]).toEqual({ range: false });
            expect(toRaw(toggles[1][0])).toBe(items[2]);
            expect(toggles[1][1]).toEqual({ range: true });
        });

        it('leaves the checkbox as the selection says when the owner does not change it', async () => {
            const wrapper = grid({ selectable: true });
            const input = wrapper.find('.c-tile-grid__check input');

            await input.trigger('click');
            await nextTick();

            expect(wrapper.emitted('toggle-select')).toHaveLength(1);
            expect(input.element.checked).toBe(false);
        });
    });

    describe('context menu', () => {
        it('emits it on a right-click, and leaves Ctrl+right-click to the browser', async () => {
            const wrapper = grid();
            const tile = wrapper.findAll('.c-tile-grid__tile')[1];

            await tile.trigger('contextmenu');
            await tile.trigger('contextmenu', { ctrlKey: true });

            expect(wrapper.emitted('context-menu')).toHaveLength(1);
            expect(toRaw(wrapper.emitted('context-menu')[0][0])).toBe(items[1]);
        });

        it('renders the actions slot per tile', () => {
            const wrapper = grid({}, { actions: ({ item }) => h('button', { class: 'more' }, item.id) });

            expect(wrapper.findAll('.c-tile-grid__actions .more')).toHaveLength(3);
        });
    });

    describe('drag and drop', () => {
        const drag = { dataTransfer: { types: ['text/plain'] } };

        it('makes tiles draggable only when asked', () => {
            expect(grid().find('.c-tile-grid__tile').attributes('draggable')).toBeUndefined();
            expect(grid({ draggable: true }).find('.c-tile-grid__tile').attributes('draggable')).toBe('true');
        });

        it('reports drag start and end', async () => {
            const wrapper = grid({ draggable: true });
            const tile = wrapper.findAll('.c-tile-grid__tile')[1];

            await tile.trigger('dragstart', drag);
            await tile.trigger('dragend', drag);

            expect(toRaw(wrapper.emitted('drag-start')[0][0])).toBe(items[1]);
            expect(toRaw(wrapper.emitted('drag-end')[0][0])).toBe(items[1]);
        });

        it('reports no drag of a tile that is not draggable, even one bubbling up from its link', async () => {
            const still = grid();
            const link = still.find('.name-link');
            await link.trigger('dragstart', drag);
            await link.trigger('dragend', drag);
            expect(still.emitted('drag-start')).toBeUndefined();
            expect(still.emitted('drag-end')).toBeUndefined();

            const uploading = grid({
                draggable: true,
                items: [{ id: 'u:1', title: 'new.pdf', uploading: true, progress: 30 }],
            });
            const uploadLink = uploading.find('.name-link');
            await uploadLink.trigger('dragstart', drag);
            await uploadLink.trigger('dragend', drag);
            expect(uploading.emitted('drag-start')).toBeUndefined();
            expect(uploading.emitted('drag-end')).toBeUndefined();
        });

        it('ends a drag it started, even if the tile stopped being draggable meanwhile', async () => {
            const wrapper = grid({ draggable: true });
            const tile = wrapper.findAll('.c-tile-grid__tile')[1];

            await tile.trigger('dragstart', drag);
            await wrapper.setProps({ draggable: false });
            await tile.trigger('dragend', drag);

            expect(wrapper.emitted('drag-end')).toHaveLength(1);
            expect(toRaw(wrapper.emitted('drag-end')[0][0])).toBe(items[1]);
        });

        it('drags the whole tile when the drag starts on its name link', async () => {
            const wrapper = grid({ draggable: true });
            const tile = wrapper.findAll('.c-tile-grid__tile')[1];
            const setDragImage = vi.fn();

            await tile.find('.name-link').trigger('dragstart', { dataTransfer: { types: [], setDragImage } });

            expect(setDragImage).toHaveBeenCalledTimes(1);
            expect(setDragImage.mock.calls[0][0]).toBe(tile.element);
            expect(toRaw(wrapper.emitted('drag-start')[0][0])).toBe(items[1]);
        });

        it('takes a drop only where canDrop allows it', async () => {
            const wrapper = grid({ canDrop: (item) => item.id === 'f:1' });
            const [folder, file] = wrapper.findAll('.c-tile-grid__tile');

            await file.trigger('dragover', drag);
            await file.trigger('drop', drag);
            await folder.trigger('dragover', drag);
            await folder.trigger('drop', drag);

            expect(wrapper.emitted('drag-over')).toHaveLength(1);
            expect(toRaw(wrapper.emitted('drag-over')[0][0])).toBe(items[0]);
            expect(wrapper.emitted('drag-over')[0][1]).toBeInstanceOf(Event);
            expect(wrapper.emitted('drop-on')).toHaveLength(1);
            expect(toRaw(wrapper.emitted('drop-on')[0][0])).toBe(items[0]);
        });

        it('emits drag-leave only when the pointer leaves the tile', async () => {
            const wrapper = grid({ canDrop: () => true });
            const tile = wrapper.findAll('.c-tile-grid__tile')[0];
            const link = tile.find('.name-link').element;

            await tile.trigger('dragleave', { relatedTarget: link });
            expect(wrapper.emitted('drag-leave')).toBeUndefined();

            await tile.trigger('dragleave', { relatedTarget: document.body });
            expect(wrapper.emitted('drag-leave')).toHaveLength(1);
            expect(toRaw(wrapper.emitted('drag-leave')[0][0])).toBe(items[0]);
        });

        it('highlights the drop target', () => {
            const wrapper = grid({ dropTargetKey: 'f:1' });

            expect(wrapper.findAll('.c-tile-grid__tile')[0].classes()).toContain('is-drop-target');
        });
    });

    describe('uploads', () => {
        it('frames an uploading tile with its progress and takes away its controls', () => {
            const wrapper = grid({
                selectable: true,
                draggable: true,
                items: [{ id: 'u:1', title: 'new.pdf', uploading: true, progress: 30 }],
            }, { actions: () => h('button', { class: 'more' }) });
            const tile = wrapper.find('.c-tile-grid__tile');

            expect(tile.classes()).toContain('is-uploading');
            expect(tile.find('.c-progress-frame').attributes('aria-valuenow')).toBe('30');
            expect(tile.find('.c-tile-grid__check').exists()).toBe(false);
            expect(tile.find('.more').exists()).toBe(false);
            expect(tile.attributes('draggable')).toBeUndefined();
        });
    });

    describe('levels', () => {
        it('slides forward by default and back when told', () => {
            expect(grid().vm.swapName).toBe('c-tile-grid-swap-forward');
            expect(grid({ direction: 'back' }).vm.swapName).toBe('c-tile-grid-swap-back');
        });
    });

    describe('more', () => {
        let observers;

        beforeEach(() => {
            observers = [];
            globalThis.IntersectionObserver = class {
                constructor(callback) { this.callback = callback; this.targets = []; observers.push(this); }
                observe(target) { this.targets.push(target); }
                unobserve() {}
                disconnect() {}
            };
        });

        afterEach(() => {
            delete globalThis.IntersectionObserver;
        });

        it('offers more and loads it when pressed or scrolled into view', async () => {
            const wrapper = grid({ hasMore: true });
            const more = wrapper.find('.c-tile-grid__more button');

            await more.trigger('click');
            observers[0].callback([{ isIntersecting: true }]);

            expect(wrapper.emitted('load-more')).toHaveLength(2);
        });

        it('does not load more while a page is loading', () => {
            const wrapper = grid({ hasMore: true, loadingMore: true });

            observers[0].callback([{ isIntersecting: true }]);
            expect(wrapper.emitted('load-more')).toBeUndefined();
            expect(wrapper.find('.c-tile-grid__more button').attributes('disabled')).toBeDefined();
        });
    });
});
