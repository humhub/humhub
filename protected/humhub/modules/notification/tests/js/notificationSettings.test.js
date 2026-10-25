import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import NotificationSettings, { SPACES_DEBOUNCE_MS } from '../../vue/NotificationSettings.vue';
import HumHubForm from '../../../../vue/HumHubForm.vue';
import CheckboxField from '../../../../vue/CheckboxField.vue';
import PickerFilterControl from '../../../../vue/PickerFilterControl.vue';
import SpaceImage from '../../../../modules/space/vue/SpaceImage.vue';

await import('../../../../resources/js/humhub/humhub.url.js');
await import('../../../../resources/js/humhub/humhub.vue.js');

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
const selectedProfile = () => wrapper.findAll('input[data-profile]').find((input) => input.element.checked).attributes('data-profile');

const expand = async (id) => {
    await row(id).find('button.accordion-button').trigger('click');
};

describe('NotificationSettings', () => {
    it('renders a row per category with a badge per channel that applies to it', () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        expect(wrapper.findAll('[data-category]').map((node) => node.attributes('data-category'))).toEqual(['direct', 'social', 'content', 'chat']);
        const badges = row('social').findAll('.notification-settings-badges .badge');
        expect(badges.map((badge) => badge.attributes('data-channel'))).toEqual(['web', 'email', 'mobile']);
        expect(badges.map((badge) => badge.classes().includes('badge-accent'))).toEqual([true, true, false]);
        expect(row('social').text()).toContain('social description');
        expect(row('social').find('.ti-social').exists()).toBe(true);
    });

    it('shows the module categories under their own heading', () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        const headings = wrapper.findAll('.notification-settings-divider');
        expect(headings).toHaveLength(1);
        expect(headings[0].text()).toBe('From modules');
        // The module categories form an accordion of their own after the heading.
        const modules = headings[0].element.nextElementSibling;
        expect(modules.classList.contains('accordion')).toBe(true);
        expect([...modules.querySelectorAll('[data-category]')].map((item) => item.getAttribute('data-category'))).toEqual(['chat']);
    });

    it('does not offer the web channel for a category without it', async () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        expect(row('chat').findAll('.notification-settings-badges .badge').map((badge) => badge.attributes('data-channel'))).toEqual(['email', 'mobile']);
        await expand('chat');
        expect(checkbox('chat', 'web').exists()).toBe(false);
        expect(checkbox('chat', 'email').exists()).toBe(true);
        expect(checkbox('chat', 'mobile').exists()).toBe(true);
    });

    it('does not expand a fixed category, which shows a lock', async () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        const head = row('direct').find('.accordion-button');
        expect(head.element.tagName).toBe('DIV');
        expect(head.attributes('aria-expanded')).toBeUndefined();
        expect(row('direct').find('.ti-lock').exists()).toBe(true);
        await head.trigger('click');
        expect(row('direct').find('.notification-settings-category-body').exists()).toBe(false);
        expect(wrapper.findAll('input[type="checkbox"]')).toHaveLength(0);
    });

    it('expands a category with a disclosure button, the spaces with the content category', async () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        const head = row('social').find('button.accordion-button');
        expect(head.attributes('aria-expanded')).toBe('false');
        await head.trigger('click');
        expect(head.attributes('aria-expanded')).toBe('true');
        expect(wrapper.find(`#${head.attributes('aria-controls')}`).exists()).toBe(true);
        expect(checkbox('social', 'mobile').element.checked).toBe(false);
        expect(row('social').find('.notification-settings-spaces').exists()).toBe(false);

        await expand('content');
        expect(row('content').find('.notification-settings-spaces label').text()).toBe('From these Spaces');
        expect(row('content').find('.picker-chip-label').text()).toBe('Space 1');
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
        expect(row('social').findAll('.notification-settings-badges .badge-accent').map((badge) => badge.attributes('data-channel'))).toEqual(['web']);
        expect(wrapper.find('.notification-settings-status').text()).toBe('Saved');
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
        expect(wrapper.find('.notification-settings-status').text()).toBe('Saved');
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
        expect(wrapper.find('.notification-settings-status').text()).toBe('Saving…');

        release({});
        await flushPromises();
        expect(ajaxCalls).toHaveLength(2);
        expect(sent()).toEqual([
            { categories: { social: { email: false } } },
            { categories: { social: { mobile: true, email: true } } },
        ]);
        release({});
        await flushPromises();
        expect(wrapper.find('.notification-settings-status').text()).toBe('Saved');
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
        expect(wrapper.find('.notification-settings-status').text()).toBe('Not saved');
        expect(selectedProfile()).toBe('recommended');
    });

    it('puts a profile back when saving it fails', async () => {
        answer = () => Promise.reject({ status: 500 });
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        await wrapper.find('[data-profile="everything"]').trigger('click');
        await flushPromises();

        expect(selectedProfile()).toBe('recommended');
        expect(wrapper.find('.notification-settings-status').text()).toBe('Not saved');
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

        await wrapper.find('.notification-settings-reset').trigger('click');
        await flushPromises();

        expect(confirms).toEqual([{ body: 'Do you want to reset your notification settings to the defaults?' }]);
        expect(postCalls).toEqual(['/api/v2/notification/settings/reset']);
        expect(row('social').find('[data-channel="email"]').classes()).not.toContain('badge-accent');
        expect(wrapper.vm.spaceIds).toEqual([]);
        expect(wrapper.find('.notification-settings-reset-all').exists()).toBe(false);
    });

    it('does not reset without a confirmation', async () => {
        globalThis.humhubStubs.modal.confirm = () => Promise.resolve(false);
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        await wrapper.find('.notification-settings-reset').trigger('click');
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
        expect(wrapper.find('.notification-settings-reset').exists()).toBe(false);
        await wrapper.find('.notification-settings-reset-all').trigger('click');
        await flushPromises();

        expect(postCalls).toEqual(['/api/v2/notification/settings/reset-all']);
    });

    describe('privacy mode', () => {
        const globalProps = (privacy) => props({
            initial: payload({ scope: 'global', defaults: null, privacy }),
            scope: 'global',
            resetUrl: null,
        });
        const selectedPrivacy = () => wrapper.findAll('input[data-privacy]').find((input) => input.element.checked).attributes('data-privacy');

        it('is not shown on the account page', () => {
            wrapper = mount(NotificationSettings, { ...mountOptions(), props: props({ initial: payload({ privacy: null }) }) });

            expect(wrapper.find('.notification-settings-privacy').exists()).toBe(false);
        });

        it('offers the three levels with an example each in the global scope', () => {
            wrapper = mount(NotificationSettings, { ...mountOptions(), props: globalProps({ mode: 'redacted', fixed: false }) });

            const section = wrapper.find('.notification-settings-privacy');
            expect(section.find('.notification-settings-subtitle').text()).toBe('Privacy of e-mails and push messages');
            const intro = section.find('.notification-settings-lead').text();
            expect(intro).toContain('notification e-mails');
            expect(intro).toContain('push messages');
            expect(intro).toContain('E-Mail Summaries');

            const options = wrapper.findAll('[data-privacy]');
            expect(options.map((option) => option.attributes('data-privacy'))).toEqual(['full', 'redacted', 'minimal']);
            expect(options.map((option) => option.element.closest('.form-check-card').querySelector('.form-check-card-title').textContent)).toEqual(['Full details', 'Hide content', 'Minimal']);
            expect(options.every((option) => option.element.closest('.form-check-card').querySelector('.notification-settings-privacy-example').textContent !== '')).toBe(true);
            expect(selectedPrivacy()).toBe('redacted');
            expect(wrapper.find('.notification-settings-privacy-fixed').exists()).toBe(false);
        });

        it('saves a level right away', async () => {
            answer = () => Promise.resolve(payload({ scope: 'global', defaults: null, privacy: { mode: 'minimal', fixed: false } }));
            wrapper = mount(NotificationSettings, { ...mountOptions(), props: globalProps({ mode: 'full', fixed: false }) });

            await wrapper.find('[data-privacy="minimal"]').trigger('click');
            await flushPromises();

            expect(sent()).toEqual([{ privacy: 'minimal' }]);
            expect(ajaxCalls[0].url).toBe('/api/v2/notification/settings');
            expect(selectedPrivacy()).toBe('minimal');
            expect(wrapper.find('.notification-settings-status').text()).toBe('Saved');
        });

        it('is a native radio group, so the arrow keys move the choice', () => {
            wrapper = mount(NotificationSettings, { ...mountOptions(), props: globalProps({ mode: 'full', fixed: false }) });

            const radios = wrapper.findAll('input[data-privacy]');
            expect(radios.every((radio) => radio.attributes('type') === 'radio')).toBe(true);
            expect(new Set(radios.map((radio) => radio.attributes('name'))).size).toBe(1);
        });

        it('puts the level back when saving fails', async () => {
            answer = () => Promise.reject({ status: 500 });
            wrapper = mount(NotificationSettings, { ...mountOptions(), props: globalProps({ mode: 'full', fixed: false }) });

            await wrapper.find('[data-privacy="minimal"]').trigger('click');
            await flushPromises();

            expect(selectedPrivacy()).toBe('full');
            expect(wrapper.find('.notification-settings-status').text()).toBe('Not saved');
        });

        it('is read-only when the configuration file sets it', async () => {
            wrapper = mount(NotificationSettings, { ...mountOptions(), props: globalProps({ mode: 'minimal', fixed: true }) });

            expect(wrapper.find('.notification-settings-privacy-fixed').exists()).toBe(true);
            expect(wrapper.findAll('[data-privacy]').every((option) => option.attributes('disabled') !== undefined)).toBe(true);
            wrapper.vm.selectPrivacy('full');
            await flushPromises();

            expect(ajaxCalls).toHaveLength(0);
            expect(selectedPrivacy()).toBe('minimal');
        });
    });
});
