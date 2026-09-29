import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import TopicFilterControl from '../../modules/topic/vue/TopicFilterControl.vue';
import { SEARCH_DEBOUNCE_MS } from '../../vue/PickerFilterControl.vue';
import FilterBar from '../../vue/FilterBar.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');
// Registers the component and the `topic` filter type, as in production.
await import('../../modules/topic/vue/index.js');

const vueModule = globalThis.humhub.modules.vue;

// Items of `GET /api/v2/topic/picker` (TopicSerializer::short()).
const topic = (id, name, color, container = null) => ({ id, name, color, container });
const space5 = { id: 5, guid: 'guid-5', name: 'Team Alpha' };
const space7 = { id: 7, guid: 'guid-7', name: 'Sales Team' };
const topics = [
    topic(2, 'News', '#ff0000', null),
    topic(3, 'Newsletter', '#00ff00', space5),
    topic(8, 'Numbers', null, space7),
];

const filter = { key: 'topicId', type: 'topic', label: 'Topic', placeholder: 'Any topic', multiple: true };

const mounted = [];
const mountControl = (props = {}) => {
    const wrapper = mount(TopicFilterControl, {
        props: { filter, modelValue: [], inputId: 'filter-topicId', ...props },
        attachTo: document.body,
    });
    mounted.push(wrapper);
    return wrapper;
};
const input = (wrapper) => wrapper.find('input[role="combobox"]');
const optionTexts = (wrapper) => wrapper.findAll('[role="option"]').map((option) => option.find('.c-topic-filter__name').text());
const chipLabels = (wrapper) => wrapper.findAll('.c-picker__chip-label').map((label) => label.text());
const type = async (wrapper, text) => {
    input(wrapper).element.value = text;
    await input(wrapper).trigger('input');
};
const typeAndWait = async (wrapper, text) => {
    await type(wrapper, text);
    vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
    await flushPromises();
};
const choose = async (wrapper, text) => {
    await typeAndWait(wrapper, text);
    await wrapper.find('[role="option"]').trigger('click');
};
const emitted = (wrapper) => (wrapper.emitted('update:modelValue') || []).map((args) => args[0]);
const params = (url) => Object.fromEntries(new URL(url, 'http://localhost').searchParams);
const requests = () => globalThis.humhubStubs.client.get.mock.calls.map(([url]) => params(url));

// An answer per request, resolved when the test says so.
const deferredClient = () => {
    const pending = [];
    globalThis.humhubStubs.client.get = vi.fn((url) => new Promise((resolve, reject) => pending.push({ url, resolve, reject })));
    return pending;
};

describe('TopicFilterControl', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.client.get = vi.fn((url) => {
            const { q, ids } = params(url);
            const results = ids
                ? topics.filter((entry) => ids.split(',').includes(String(entry.id)))
                : topics.filter((entry) => entry.name.toLowerCase().includes(String(q).toLowerCase()));
            return Promise.resolve({ results, total: results.length });
        });
    });

    afterEach(() => {
        mounted.splice(0).forEach((wrapper) => wrapper.unmount());
        vi.useRealTimers();
    });

    it('is registered as the `topic` filter type', () => {
        expect(vueModule.getFilterType('topic')).toBe('TopicFilterControl');
        expect(vueModule.isRegistered('TopicFilterControl')).toBe(true);
    });

    it('is a search field led by the topic icon', () => {
        const wrapper = mountControl();

        expect(wrapper.find('.ti-star').exists()).toBe(true);
        expect(wrapper.classes()).toContain('c-topic-filter');
        expect(input(wrapper).attributes('id')).toBe('filter-topicId');
        expect(input(wrapper).attributes('placeholder')).toBe('Any topic');
        expect(input(wrapper).attributes('aria-label')).toBe('Topic');
    });

    it('searches the topic picker without a container', async () => {
        const wrapper = mountControl();

        await typeAndWait(wrapper, 'new');

        const url = globalThis.humhubStubs.client.get.mock.calls[0][0];
        expect(new URL(url, 'http://localhost').pathname).toBe('/api/v2/topic/picker');
        expect(requests()).toEqual([{ q: 'new', pageSize: '8' }]);
        expect(optionTexts(wrapper)).toEqual(['News', 'Newsletter']);
    });

    it('searches in the container of the definition\'s props', async () => {
        const wrapper = mountControl({ filter: { ...filter, props: { containerId: 5 } } });

        await typeAndWait(wrapper, 'new');

        expect(requests()).toEqual([{ q: 'new', pageSize: '8', containerId: '5' }]);
    });

    it('shows a colour dot per suggestion, with a fallback for a topic without a colour', async () => {
        const wrapper = mountControl();

        await typeAndWait(wrapper, 'n');

        const dots = wrapper.findAll('[role="option"] .c-topic-filter__dot');
        expect(dots).toHaveLength(3);
        expect(dots[0].attributes('style')).toContain('background-color: rgb(255, 0, 0)');
        expect(dots[2].attributes('style')).toBeUndefined();
    });

    it('names the container of a topic only when it is not the filter\'s own', async () => {
        const wrapper = mountControl({ filter: { ...filter, props: { containerId: 5 } } });

        await typeAndWait(wrapper, 'n');

        const containers = wrapper.findAll('[role="option"]').map((option) => {
            const container = option.find('.c-topic-filter__container');
            return container.exists() ? container.text() : null;
        });
        // In the picker's detail, behind a comma only a screen reader hears.
        const detail = wrapper.findAll('[role="option"]')[2].find('.c-picker-filter__detail');
        expect(detail.classes()).toContain('c-topic-filter__detail');
        expect(detail.find('.visually-hidden').text()).toBe(',');
        expect(wrapper.findAll('[role="option"]')[2].text()).toBe('Numbers, Sales Team');

        expect(containers, 'global: none; own container: none; a foreign one: its name').toEqual([null, null, 'Sales Team']);

        const global = mountControl();
        await typeAndWait(global, 'newsl');
        expect(global.find('[role="option"] .c-topic-filter__container').text(), 'without a container every container is foreign').toBe('Team Alpha');
    });

    it('emits the chosen topic\'s id and shows it as a chip with dot and name', async () => {
        const wrapper = mountControl();

        await choose(wrapper, 'newsl');

        expect(emitted(wrapper)).toEqual([['3']]);
        await wrapper.setProps({ modelValue: ['3'] });

        const chip = wrapper.find('.c-picker__chip');
        expect(chip.find('.c-picker__chip-label').text()).toBe('Newsletter');
        expect(chip.find('.c-topic-filter__dot').attributes('style')).toContain('background-color: rgb(0, 255, 0)');
        expect(chip.find('.c-topic-filter__container').exists()).toBe(false);
        expect(chip.find('.c-picker__chip-remove').attributes('aria-label')).toBe('Remove Newsletter');
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
    });

    it('names a foreign container in the chip\'s title only', async () => {
        const wrapper = mountControl({ filter: { ...filter, props: { containerId: 5 } }, modelValue: ['3', '8', '2'] });
        await flushPromises();

        const titles = wrapper.findAll('.c-picker__chip').map((chip) => chip.attributes('title') ?? null);
        expect(titles, 'own container: none; foreign: its name; global: none').toEqual([null, 'Numbers (Sales Team)', null]);
        expect(wrapper.find('.c-picker__chip .c-topic-filter__container').exists()).toBe(false);
    });

    it('appends a second choice and removes one with its X', async () => {
        const wrapper = mountControl();
        await choose(wrapper, 'newsl');
        await wrapper.setProps({ modelValue: ['3'] });

        await choose(wrapper, 'numb');
        expect(emitted(wrapper).at(-1)).toEqual(['3', '8']);
        await wrapper.setProps({ modelValue: ['3', '8'] });
        expect(chipLabels(wrapper)).toEqual(['Newsletter', 'Numbers']);

        await wrapper.findAll('.c-picker__chip-remove')[0].trigger('click');
        expect(emitted(wrapper).at(-1)).toEqual(['8']);
        await wrapper.setProps({ modelValue: ['8'] });
        expect(chipLabels(wrapper)).toEqual(['Numbers']);
    });

    it('resolves the topics of a value it does not know with one request, in the filter\'s container', async () => {
        const pending = deferredClient();
        const wrapper = mountControl({ filter: { ...filter, props: { containerId: 5 } }, modelValue: ['8', '4', '2'] });

        expect(pending).toHaveLength(1);
        expect(new URL(pending[0].url, 'http://localhost').pathname).toBe('/api/v2/topic/picker');
        expect(params(pending[0].url)).toEqual({ ids: '8,4,2', containerId: '5', pageSize: '3' });

        pending[0].resolve({ results: [topics[2], topics[0]] });
        await flushPromises();

        expect(emitted(wrapper)).toEqual([['8', '2']]);
        await wrapper.setProps({ modelValue: ['8', '2'] });
        await flushPromises();
        expect(chipLabels(wrapper)).toEqual(['Numbers', 'News']);
    });

    it('resolves without a container when the filter has none', async () => {
        mountControl({ modelValue: ['2'] });
        await flushPromises();

        expect(requests()).toEqual([{ ids: '2', pageSize: '1' }]);
    });

    it('drops an answer to an older text', async () => {
        const pending = deferredClient();
        const wrapper = mountControl();

        await typeAndWait(wrapper, 'n');
        await typeAndWait(wrapper, 'nu');
        expect(pending).toHaveLength(2);

        pending[1].resolve({ results: [topics[2]] });
        await flushPromises();
        pending[0].resolve({ results: topics });
        await flushPromises();

        expect(optionTexts(wrapper)).toEqual(['Numbers']);
    });

    it('stays usable when a request fails', async () => {
        const pending = deferredClient();
        const wrapper = mountControl({ modelValue: ['8'] });

        pending[0].reject({ status: 500 });
        await flushPromises();

        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(1);
        expect(chipLabels(wrapper)).toEqual(['8']);
        await wrapper.find('.c-picker__chip-remove').trigger('click');
        expect(emitted(wrapper)).toEqual([[]]);

        await wrapper.setProps({ modelValue: [] });
        await typeAndWait(wrapper, 'n');
        pending[1].reject({ status: 422 });
        await flushPromises();

        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(2);
        expect(input(wrapper).attributes('disabled')).toBeUndefined();
        expect(optionTexts(wrapper)).toEqual([]);
    });

    it('is the control of a `topic` definition in a filter bar, which applies the chosen ids', async () => {
        window.history.replaceState(null, '', '/files');
        const wrapper = mount(FilterBar, {
            props: {
                filters: [{ key: 'topicId', type: 'topic', label: 'Topic', multiple: true, props: { containerId: 5 } }],
                modelValue: {},
            },
            global: { components: { TopicFilterControl } },
            attachTo: document.body,
        });
        mounted.push(wrapper);

        const field = () => wrapper.find('.c-filter-bar__item--topic input[role="combobox"]');
        expect(field().attributes('id')).toBe('filter-topicId');

        for (const text of ['newsl', 'numb']) {
            field().element.value = text;
            await field().trigger('input');
            vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
            await flushPromises();
            await wrapper.find('[role="option"]').trigger('click');
            await flushPromises();
        }

        expect(requests().map((request) => request.containerId)).toEqual(['5', '5']);
        expect(emitted(wrapper).at(-1)).toEqual({ topicId: ['3', '8'] });
        expect(new URLSearchParams(window.location.search).get('topicId')).toBe('3,8');
        expect(chipLabels(wrapper)).toEqual(['Newsletter', 'Numbers']);
    });
});
