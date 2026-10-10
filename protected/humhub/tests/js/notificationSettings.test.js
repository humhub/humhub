import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import NotificationSettings, { SPACES_DEBOUNCE_MS } from '../../modules/notification/vue/NotificationSettings.vue';
import HumHubForm from '../../vue/HumHubForm.vue';
import CheckboxField from '../../vue/CheckboxField.vue';
import PickerFilterControl from '../../vue/PickerFilterControl.vue';
import SpaceImage from '../../modules/space/vue/SpaceImage.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const mountOptions = () => ({
    global: { components: { HumHubForm, CheckboxField, PickerFilterControl, SpaceImage } },
    attachTo: document.body,
});

const categoryData = (id, channels, overrides = {}) => ({
    id,
    title: `${id} title`,
    description: `${id} description`,
    icon: `ti-${id}`,
    module: false,
    fixed: id === 'direct',
    channels,
    ...overrides,
});

const space = (id) => ({
    id,
    guid: `guid-${id}`,
    name: `Space ${id}`,
    url: `/s/space-${id}`,
    color: '#000000',
    imageUrl: null,
    contentContainerId: 10 + id,
});

// The network's defaults: everything on, but social by mobile and the chat on the web (it has none).
const defaults = () => ({
    direct: { web: true, email: true, mobile: true },
    social: { web: true, email: true, mobile: false },
    content: { web: true, email: true, mobile: true },
    chat: { email: true, mobile: true },
});

const payload = (overrides = {}) => ({
    scope: 'user',
    channels: [
        { id: 'web', title: 'Web' },
        { id: 'email', title: 'E-Mail' },
        { id: 'mobile', title: 'Mobile' },
    ],
    categories: [
        categoryData('direct', { web: true, email: true, mobile: true }),
        categoryData('social', { web: true, email: true, mobile: false }),
        categoryData('content', { web: true, email: true, mobile: true }),
        categoryData('chat', { email: true, mobile: true }, { module: true, icon: 'ti-message' }),
    ],
    defaults: defaults(),
    spaces: { selected: [space(1)], enabled: true },
    ...overrides,
});

const props = (overrides = {}) => ({
    initial: payload(),
    settingsUrl: '/api/v2/notification/settings',
    resetUrl: '/api/v2/notification/settings/reset',
    resetAllUrl: null,
    scope: 'user',
    ...overrides,
});

let wrapper;
let ajaxCalls;
let postCalls;
let statuses;
let answer;

const sent = () => ajaxCalls.map((call) => JSON.parse(call.cfg.data));

beforeEach(() => {
    ajaxCalls = [];
    postCalls = [];
    statuses = [];
    answer = () => Promise.resolve({});
    globalThis.humhubStubs.client.ajax = (url, cfg) => {
        ajaxCalls.push({ url, cfg });
        return answer(url, cfg);
    };
    globalThis.humhubStubs.client.post = (url) => {
        postCalls.push(url);
        return Promise.resolve(payload());
    };
    globalThis.humhubStubs.client.get = () => Promise.resolve({ results: [] });
    globalThis.humhubStubs.modal.confirm = () => Promise.resolve(true);
    globalThis.humhub.modules.vue.setStatusHandler((entry) => statuses.push(entry));
});

afterEach(() => {
    wrapper?.unmount();
    wrapper = undefined;
    vi.useRealTimers();
    globalThis.humhub.modules.vue.setStatusHandler(null);
});

const row = (id) => wrapper.find(`[data-category="${id}"]`);
const checkbox = (categoryId, channelId) => wrapper.find(`input[name="NotificationSettings[categories.${categoryId}.${channelId}]"]`);
const selectedProfile = () => wrapper.find('[role="radio"][aria-checked="true"]').attributes('data-profile');

const expand = async (id) => {
    await row(id).find('button.c-notification-settings__category-head').trigger('click');
};

describe('NotificationSettings', () => {
    it('renders a row per category with a badge per channel that applies to it', () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        expect(wrapper.findAll('[data-category]').map((node) => node.attributes('data-category'))).toEqual(['direct', 'social', 'content', 'chat']);
        const badges = row('social').findAll('.c-notification-settings__badge');
        expect(badges.map((badge) => badge.attributes('data-channel'))).toEqual(['web', 'email', 'mobile']);
        expect(badges.map((badge) => badge.classes().includes('is-on'))).toEqual([true, true, false]);
        expect(row('social').text()).toContain('social description');
        expect(row('social').find('.ti-social').exists()).toBe(true);
    });

    it('shows the module categories under their own heading', () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        const headings = wrapper.findAll('.c-notification-settings__divider');
        expect(headings).toHaveLength(1);
        expect(headings[0].text()).toBe('From modules');
        expect(headings[0].element.nextElementSibling.getAttribute('data-category')).toBe('chat');
    });

    it('does not offer the web channel for a category without it', async () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        expect(row('chat').findAll('.c-notification-settings__badge').map((badge) => badge.attributes('data-channel'))).toEqual(['email', 'mobile']);
        await expand('chat');
        expect(checkbox('chat', 'web').exists()).toBe(false);
        expect(checkbox('chat', 'email').exists()).toBe(true);
        expect(checkbox('chat', 'mobile').exists()).toBe(true);
    });

    it('does not expand a fixed category, which shows a lock', async () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        const head = row('direct').find('.c-notification-settings__category-head');
        expect(head.element.tagName).toBe('DIV');
        expect(head.attributes('aria-expanded')).toBeUndefined();
        expect(row('direct').find('.ti-lock').exists()).toBe(true);
        await head.trigger('click');
        expect(row('direct').find('.c-notification-settings__category-body').exists()).toBe(false);
        expect(wrapper.findAll('input[type="checkbox"]')).toHaveLength(0);
    });

    it('expands a category with a disclosure button, the spaces with the content category', async () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        const head = row('social').find('button.c-notification-settings__category-head');
        expect(head.attributes('aria-expanded')).toBe('false');
        await head.trigger('click');
        expect(head.attributes('aria-expanded')).toBe('true');
        expect(wrapper.find(`#${head.attributes('aria-controls')}`).exists()).toBe(true);
        expect(checkbox('social', 'mobile').element.checked).toBe(false);
        expect(row('social').find('.c-notification-settings__spaces').exists()).toBe(false);

        await expand('content');
        expect(row('content').find('.c-notification-settings__spaces label').text()).toBe('From these Spaces');
        expect(row('content').find('.c-picker__chip-label').text()).toBe('Space 1');
    });

    it('detects the Recommended profile when the switches equal the defaults, else Custom', () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });
        expect(selectedProfile()).toBe('recommended');
        wrapper.unmount();

        const custom = payload();
        custom.categories[2].channels.mobile = false;
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props({ initial: custom }) });
        expect(selectedProfile()).toBe('custom');
        wrapper.unmount();

        const everything = payload();
        everything.categories[1].channels.mobile = true;
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props({ initial: everything }) });
        expect(selectedProfile()).toBe('everything');
    });

    it('applies a profile with one request', async () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        await wrapper.find('[data-profile="important"]').trigger('click');
        await flushPromises();

        expect(selectedProfile()).toBe('important');
        expect(ajaxCalls).toHaveLength(1);
        expect(ajaxCalls[0].url).toBe('/api/v2/notification/settings');
        expect(ajaxCalls[0].cfg.method).toBe('PATCH');
        expect(sent()[0]).toEqual({
            categories: {
                social: { web: true, email: false, mobile: false },
                content: { web: true, email: false, mobile: false },
                chat: { email: false, mobile: false },
            },
        });
        expect(row('social').findAll('.c-notification-settings__badge.is-on').map((badge) => badge.attributes('data-channel'))).toEqual(['web']);
        expect(wrapper.find('.c-notification-settings__status').text()).toBe('Saved');
        expect(statuses).toHaveLength(0);
    });

    it('applies Recommended by following the defaults again', async () => {
        const custom = payload();
        custom.categories[1].channels.email = false;
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props({ initial: custom }) });

        await wrapper.find('[data-profile="recommended"]').trigger('click');
        await flushPromises();

        expect(sent()).toEqual([{
            categories: {
                social: { web: null, email: null, mobile: null },
                content: { web: null, email: null, mobile: null },
                chat: { email: null, mobile: null },
            },
        }]);
        expect(selectedProfile()).toBe('recommended');
    });

    it('does nothing when Custom is chosen', async () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        await wrapper.find('[data-profile="custom"]').trigger('click');
        await flushPromises();

        expect(ajaxCalls).toHaveLength(0);
        expect(selectedProfile()).toBe('recommended');
    });

    it('saves every checkbox right away, only what changed', async () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });
        await expand('social');

        await checkbox('social', 'email').setValue(false);
        await flushPromises();

        expect(sent()).toEqual([{ categories: { social: { email: false } } }]);
        expect(selectedProfile()).toBe('custom');
        expect(wrapper.find('.c-notification-settings__status').text()).toBe('Saved');
    });

    it('sends the requests one after another, merging what changed meanwhile', async () => {
        let release;
        answer = () => new Promise((resolve) => {
            release = resolve;
        });
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });
        await expand('social');

        await checkbox('social', 'email').setValue(false);
        await checkbox('social', 'mobile').setValue(true);
        await checkbox('social', 'email').setValue(true);
        expect(ajaxCalls).toHaveLength(1);
        expect(wrapper.find('.c-notification-settings__status').text()).toBe('Saving…');

        release({});
        await flushPromises();
        expect(ajaxCalls).toHaveLength(2);
        expect(sent()).toEqual([
            { categories: { social: { email: false } } },
            { categories: { social: { mobile: true, email: true } } },
        ]);
        release({});
        await flushPromises();
        expect(wrapper.find('.c-notification-settings__status').text()).toBe('Saved');
    });

    it('saves the spaces after a pause', async () => {
        vi.useFakeTimers();
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });
        await expand('content');
        const picker = wrapper.findComponent(PickerFilterControl);
        // "Add a space…" stays next to the chosen spaces.
        expect(picker.props('keepPlaceholder')).toBe(true);

        picker.vm.$emit('update:modelValue', ['1', '2']);
        picker.vm.$emit('update:modelValue', ['2']);
        vi.advanceTimersByTime(SPACES_DEBOUNCE_MS - 1);
        expect(ajaxCalls).toHaveLength(0);

        vi.advanceTimersByTime(1);
        await flushPromises();
        expect(sent()).toEqual([{ spaces: [2] }]);
    });

    it('puts the switch back and shows the error when saving fails', async () => {
        answer = () => Promise.reject({ status: 422, errors: { 'categories.social.email': ['Not today.'] } });
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });
        await expand('social');

        await checkbox('social', 'email').setValue(false);
        await flushPromises();

        expect(checkbox('social', 'email').element.checked).toBe(true);
        expect(row('social').find('.invalid-feedback').text()).toBe('Not today.');
        expect(wrapper.find('.c-notification-settings__status').text()).toBe('Not saved');
        expect(selectedProfile()).toBe('recommended');
    });

    it('puts a profile back when saving it fails', async () => {
        answer = () => Promise.reject({ status: 500 });
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        await wrapper.find('[data-profile="everything"]').trigger('click');
        await flushPromises();

        expect(selectedProfile()).toBe('recommended');
        expect(wrapper.find('.c-notification-settings__status').text()).toBe('Not saved');
    });

    it('resets after a confirmation and replaces the state with the answer', async () => {
        const reset = payload();
        reset.categories[1].channels.email = false;
        reset.spaces.selected = [];
        globalThis.humhubStubs.client.post = (url) => {
            postCalls.push(url);
            return Promise.resolve(reset);
        };
        const confirms = [];
        globalThis.humhubStubs.modal.confirm = (options) => {
            confirms.push(options);
            return Promise.resolve(true);
        };
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        await wrapper.find('.c-notification-settings__reset').trigger('click');
        await flushPromises();

        expect(confirms).toEqual([{ body: 'Do you want to reset your notification settings to the defaults?' }]);
        expect(postCalls).toEqual(['/api/v2/notification/settings/reset']);
        expect(row('social').find('[data-channel="email"]').classes()).not.toContain('is-on');
        expect(wrapper.vm.spaceIds).toEqual([]);
        expect(wrapper.find('.c-notification-settings__reset-all').exists()).toBe(false);
    });

    it('does not reset without a confirmation', async () => {
        globalThis.humhubStubs.modal.confirm = () => Promise.resolve(false);
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        await wrapper.find('.c-notification-settings__reset').trigger('click');
        await flushPromises();

        expect(postCalls).toHaveLength(0);
    });

    it('hides the profiles and offers resetting all users in the global scope', async () => {
        wrapper = mount(NotificationSettings, {
            ...mountOptions(),
            props: props({
                initial: payload({ scope: 'global', defaults: null }),
                scope: 'global',
                resetUrl: null,
                resetAllUrl: '/api/v2/notification/settings/reset-all',
            }),
        });

        expect(wrapper.find('[role="radiogroup"]').exists()).toBe(false);
        expect(wrapper.find('.c-notification-settings__reset').exists()).toBe(false);
        await wrapper.find('.c-notification-settings__reset-all').trigger('click');
        await flushPromises();

        expect(postCalls).toEqual(['/api/v2/notification/settings/reset-all']);
    });
});
