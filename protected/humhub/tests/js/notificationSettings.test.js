import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import NotificationSettings from '../../modules/notification/vue/NotificationSettings.vue';
import HumHubForm from '../../vue/HumHubForm.vue';
import SelectField from '../../vue/SelectField.vue';
import CheckboxField from '../../vue/CheckboxField.vue';
import SubmitButton from '../../vue/SubmitButton.vue';
import SpaceImage from '../../modules/space/vue/SpaceImage.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const mountOptions = () => ({
    global: { components: { HumHubForm, SelectField, CheckboxField, SubmitButton, SpaceImage } },
    attachTo: document.body,
});

const modes = (values) => values.map((value) => ({ value, label: value }));

const group = (id, overrides = {}) => ({
    id,
    title: `${id} title`,
    description: `${id} description`,
    enabled: true,
    fixed: id === 'direct',
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

const payload = (overrides = {}) => ({
    channels: [
        { id: 'web', title: 'Web', fixed: true, mode: 'adaptive', modes: modes(['adaptive']), groups: [] },
        {
            id: 'email',
            title: 'E-Mail',
            fixed: false,
            mode: 'adaptive',
            modes: modes(['adaptive', 'off']),
            groups: [group('direct'), group('social'), group('content')],
        },
        {
            id: 'mobile',
            title: 'Mobile',
            fixed: false,
            mode: 'adaptive',
            modes: modes(['adaptive', 'off']),
            groups: [group('direct'), group('social'), group('content')],
        },
    ],
    spaces: { selected: [space(1)], enabled: true },
    summary: { interval: 2, intervals: [{ value: 0, label: 'Never' }, { value: 2, label: 'Daily' }] },
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

beforeEach(() => {
    ajaxCalls = [];
    postCalls = [];
    statuses = [];
    globalThis.humhubStubs.client.ajax = (url, cfg) => {
        ajaxCalls.push({ url, cfg });
        return Promise.resolve(payload());
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
    globalThis.humhub.modules.vue.setStatusHandler(null);
});

const section = (id) => wrapper.find(`[data-channel="${id}"]`);

describe('NotificationSettings', () => {
    it('renders a section per channel', () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        expect(wrapper.findAll('[data-channel]').map((node) => node.attributes('data-channel'))).toEqual(['web', 'email', 'mobile']);
        expect(section('email').text()).toContain('E-Mail');
        expect(section('email').find('select[name="NotificationSettings[email.mode]"]').exists()).toBe(true);
        expect(section('mobile').findAll('input[type="checkbox"]')).toHaveLength(3);
        expect(wrapper.find('.c-notification-settings__spaces').exists()).toBe(true);
        expect(wrapper.find('select[name="NotificationSettings[summary.interval]"]').exists()).toBe(true);
    });

    it('shows the web channel as always on, without controls', () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        expect(section('web').text()).toContain('Always on');
        expect(section('web').find('select').exists()).toBe(false);
        expect(section('web').find('input').exists()).toBe(false);
    });

    it('renders the direct group checked and disabled', () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        const direct = section('email').find('input[name="NotificationSettings[email.group.direct]"]');
        expect(direct.element.checked).toBe(true);
        expect(direct.element.disabled).toBe(true);
        expect(section('email').text()).toContain('direct description');
        expect(section('email').find('input[name="NotificationSettings[email.group.social]"]').element.disabled).toBe(false);
    });

    it('disables the group switches while the mode is off', async () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        await section('email').find('select').setValue('off');

        expect(section('email').find('input[name="NotificationSettings[email.group.social]"]').element.disabled).toBe(true);
        expect(section('mobile').find('input[name="NotificationSettings[mobile.group.social]"]').element.disabled).toBe(false);
    });

    it('submits the expected payload shape with PATCH', async () => {
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        await section('email').find('select').setValue('off');
        await section('mobile').find('input[name="NotificationSettings[mobile.group.social]"]').setValue(false);
        await wrapper.find('select[name="NotificationSettings[summary.interval]"]').setValue('0');
        await wrapper.find('form').trigger('submit');
        await flushPromises();

        expect(ajaxCalls).toHaveLength(1);
        expect(ajaxCalls[0].url).toBe('/api/v2/notification/settings');
        expect(ajaxCalls[0].cfg.method).toBe('PATCH');
        expect(ajaxCalls[0].cfg.contentType).toBe('application/json');
        expect(JSON.parse(ajaxCalls[0].cfg.data)).toEqual({
            channels: [
                { id: 'email', mode: 'off', groups: [{ id: 'social', enabled: true }, { id: 'content', enabled: true }] },
                { id: 'mobile', mode: 'adaptive', groups: [{ id: 'social', enabled: false }, { id: 'content', enabled: true }] },
            ],
            spaces: [1],
            summary: { interval: 0 },
        });
        expect(statuses).toHaveLength(1);
        expect(statuses[0]).toMatchObject({ level: 'success', message: 'Saved' });
    });

    it('puts a 422 on the field', async () => {
        globalThis.humhubStubs.client.ajax = () => Promise.reject({ status: 422, errors: { 'email.mode': ['This mode is not available for the channel.'] } });
        wrapper = mount(NotificationSettings, { ...mountOptions(), props: props() });

        await wrapper.find('form').trigger('submit');
        await flushPromises();

        const field = section('email').find('.field-notificationsettings-email-mode');
        expect(field.find('select').classes()).toContain('is-invalid');
        expect(field.find('.invalid-feedback').text()).toBe('This mode is not available for the channel.');
        expect(statuses).toHaveLength(0);
    });

    it('resets after a confirmation and replaces the state with the answer', async () => {
        const answer = payload();
        answer.channels[1].mode = 'off';
        answer.spaces.selected = [];
        globalThis.humhubStubs.client.post = (url) => {
            postCalls.push(url);
            return Promise.resolve(answer);
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
        expect(section('email').find('select').element.value).toBe('off');
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

    it('offers resetting all users in the global scope only', async () => {
        wrapper = mount(NotificationSettings, {
            ...mountOptions(),
            props: props({ scope: 'global', resetUrl: null, resetAllUrl: '/api/v2/notification/settings/reset-all' }),
        });

        expect(wrapper.find('.c-notification-settings__reset').exists()).toBe(false);
        await wrapper.find('.c-notification-settings__reset-all').trigger('click');
        await flushPromises();

        expect(postCalls).toEqual(['/api/v2/notification/settings/reset-all']);
    });
});
