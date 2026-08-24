import { beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import MembershipButton from '../../modules/space/vue/MembershipButton.vue';
import HumHubForm from '../../vue/HumHubForm.vue';
import TextareaField from '../../vue/TextareaField.vue';
import UiModal from '../../vue/UiModal.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

// `<ui-modal>`/`<humhub-form>`/`<textarea-field>` are resolved through the global Vue
// component registry in production (SpaceVueAsset depends on the core bundle, which always
// carries CoreVueAsset); `global.components` stands in for that registry here, the same way
// likeButton.test.js does for its own cross-module tags.
const mountOptions = () => ({ global: { components: { UiModal, HumHubForm, TextareaField } } });

// MembershipSerializer::state()
const state = (overrides = {}) => ({
    state: 'none',
    canJoin: true,
    needsApproval: false,
    canLeave: false,
    isOwner: false,
    isFollowing: false,
    ...overrides,
});

const mountButton = (props = {}) => mount(MembershipButton, {
    ...mountOptions(),
    props: { spaceId: 5, spaceName: 'Product Team', spaceUrl: '/s/product-team', ...props },
});

// UiModal teleports into document.body, so the request dialog is never inside the wrapper.
const dialog = () => document.body.querySelector('.modal[role="dialog"]');
const clickSend = () => {
    const buttons = dialog().querySelectorAll('.modal-footer button');
    buttons[buttons.length - 1].dispatchEvent(new MouseEvent('click', { bubbles: true }));
};

// The server-rendered FollowButton pair of a space, which the island toggles.
const createFollowButtons = (spaceId) => {
    const container = document.createElement('div');
    const create = (className, hidden) => {
        const element = document.createElement('a');
        element.className = hidden ? `${className} d-none` : className;
        element.setAttribute('data-content-container-id', String(spaceId));
        container.appendChild(element);

        return element;
    };

    const unfollow = create('unfollowButton', true);
    const follow = create('followButton', false);
    document.body.appendChild(container);

    return { follow, unfollow };
};

describe('MembershipButton', () => {
    beforeEach(() => {
        document.body.replaceChildren();
        globalThis.humhub.modules.url.config.template = '/__route__';
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(state()));
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(state({ state: 'member', canJoin: false, canLeave: true })));
        // DELETE goes through the vue bridge's del() → client.ajax()
        globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve(state()));
        globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(true));
        globalThis.humhubStubs.logCalls.error.length = 0;
    });

    it('renders the join button from the inlined state without fetching', () => {
        const wrapper = mountButton({ initial: state() });

        const button = wrapper.find('a');
        expect(button.text()).toBe('Join');
        expect(button.classes()).toContain('btn-accent');
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('renders nothing when the space cannot be joined', () => {
        const wrapper = mountButton({ initial: state({ canJoin: false }) });

        expect(wrapper.find('a').exists()).toBe(false);
    });

    it('fetches the state when none was inlined', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(state({ state: 'applicant' })));
        const wrapper = mountButton();

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/space/5/membership');
        await flushPromises();

        expect(wrapper.text()).toContain('Pending');
    });

    it('joins a free space and renders the state the endpoint answered', async () => {
        const wrapper = mountButton({ initial: state(), showMemberState: true });

        await wrapper.find('a').trigger('click');
        await flushPromises();

        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/space/5/membership', undefined);
        expect(wrapper.text()).toContain('Member');
    });

    it('does not send a second request while one is in flight', async () => {
        let resolvePost;
        globalThis.humhubStubs.client.post = vi.fn(() => new Promise((resolve) => {
            resolvePost = resolve;
        }));
        const wrapper = mountButton({ initial: state() });

        await wrapper.find('a').trigger('click');
        await wrapper.find('a').trigger('click');
        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledTimes(1);

        resolvePost(state({ state: 'member', canJoin: false }));
        await flushPromises();
    });

    it('reloads the page after joining only where the caller asked for it', async () => {
        const reloadPage = vi.fn();
        const wrapper = mountButton({ initial: state(), reloadOnJoin: true });
        wrapper.vm.reloadPage = reloadPage;

        await wrapper.find('a').trigger('click');
        await flushPromises();

        expect(reloadPage).toHaveBeenCalled();
    });

    describe('invite', () => {
        it('accepts an invite with the same POST that joins', async () => {
            const wrapper = mountButton({ initial: state({ state: 'invited', canJoin: false }) });

            expect(wrapper.find('.btn-group').exists()).toBe(true);
            expect(wrapper.find('.dropdown-toggle').exists()).toBe(true);

            await wrapper.find('.btn-group > a').trigger('click');
            await flushPromises();

            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/space/5/membership', undefined);
        });

        it('declines an invite through DELETE, without a confirmation', async () => {
            const wrapper = mountButton({ initial: state({ state: 'invited', canJoin: false }) });

            await wrapper.find('.dropdown-menu a').trigger('click');
            await flushPromises();

            const [url, cfg] = globalThis.humhubStubs.client.ajax.mock.calls[0];
            expect(url).toBe('/api/v2/space/5/membership');
            expect(cfg.method).toBe('DELETE');
            expect(globalThis.humhubStubs.modal.confirm).not.toHaveBeenCalled();
            expect(wrapper.text()).toContain('Join');
        });
    });

    describe('confirmations', () => {
        it('withdraws a pending application after confirming', async () => {
            const wrapper = mountButton({ initial: state({ state: 'applicant', canJoin: false, canLeave: true }) });

            await wrapper.find('a').trigger('click');
            await flushPromises();

            const options = globalThis.humhubStubs.modal.confirm.mock.calls[0][0];
            expect(options.body).toContain('withdraw your request');
            // The space name is interpolated as (escaped) markup, like the legacy dialog.
            expect(options.body).toContain('<strong>Product Team</strong>');
            expect(globalThis.humhubStubs.client.ajax).toHaveBeenCalled();
        });

        it('leaves a space after confirming, and sends nothing when the dialog is cancelled', async () => {
            globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(false));
            const wrapper = mountButton({
                initial: state({ state: 'member', canJoin: false, canLeave: true }),
                showMemberState: true,
            });

            await wrapper.find('a').trigger('click');
            await flushPromises();

            const options = globalThis.humhubStubs.modal.confirm.mock.calls[0][0];
            expect(options.header).toContain('Leave');
            expect(options.confirmText).toBe('Leave');
            expect(globalThis.humhubStubs.client.ajax).not.toHaveBeenCalled();
            expect(wrapper.text()).toContain('Member');
        });

        it('escapes the space name it puts into the dialog', async () => {
            const wrapper = mountButton({
                initial: state({ state: 'applicant', canJoin: false }),
                spaceName: '<img src=x onerror=alert(1)>',
            });

            await wrapper.find('a').trigger('click');
            await flushPromises();

            const options = globalThis.humhubStubs.modal.confirm.mock.calls[0][0];
            expect(options.body).toContain('&lt;img src=x onerror=alert(1)&gt;');
            expect(options.body).not.toContain('<img');
        });
    });

    describe('member state', () => {
        it('is hidden unless the caller shows it', () => {
            const wrapper = mountButton({ initial: state({ state: 'member', canJoin: false, canLeave: true }) });

            expect(wrapper.find('a').exists()).toBe(false);
        });

        it('links to the space and reads "Owner" for an owner who cannot leave', () => {
            const wrapper = mountButton({
                initial: state({ state: 'member', canJoin: false, canLeave: false, isOwner: true }),
                showMemberState: true,
                userIconHtml: '<i class="fa fa-user"></i>',
            });

            const link = wrapper.find('a');
            expect(link.attributes('href')).toBe('/s/product-team');
            expect(link.text()).toBe('Owner');
            expect(link.find('i.fa-user').exists()).toBe(true);
        });
    });

    describe('request membership', () => {
        const requestState = () => state({ needsApproval: true });

        it('opens the modal instead of posting, and applies with the message', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(state({
                state: 'applicant',
                canJoin: false,
                needsApproval: true,
            })));
            const wrapper = mountButton({ initial: requestState() });

            await wrapper.find('a').trigger('click');
            await flushPromises();
            expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();
            expect(dialog()).not.toBeNull();
            // The message field is focused for typing, like the legacy modal.
            expect(document.activeElement).toBe(dialog().querySelector('textarea'));

            await wrapper.findComponent(TextareaField).find('textarea').setValue('Please let me in.');
            clickSend();
            await flushPromises();

            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith(
                '/api/v2/space/5/membership',
                { data: { message: 'Please let me in.' } },
            );
            // The acknowledgement replaces the form, and the button behind it is "Pending".
            expect(dialog().querySelector('.modal-body').textContent).toContain('successfully submitted');
            expect(dialog().querySelector('textarea')).toBeNull();
            expect(wrapper.text()).toContain('Pending');

            wrapper.unmount();
        });

        it('renders a 422 on the message field and keeps the form open', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({
                status: 422,
                errors: { message: ['Your Message cannot be blank.'] },
            }));
            const wrapper = mountButton({ initial: requestState() });

            await wrapper.find('a').trigger('click');
            await flushPromises();
            clickSend();
            await flushPromises();

            expect(dialog().querySelector('.invalid-feedback').textContent).toContain('cannot be blank');
            expect(dialog().querySelector('textarea')).not.toBeNull();
            expect(globalThis.humhubStubs.logCalls.error.length).toBe(0);

            wrapper.unmount();
        });
    });

    describe('follow buttons', () => {
        it('hides both once the user is a member', async () => {
            const buttons = createFollowButtons(5);
            const wrapper = mountButton({ initial: state() });

            await wrapper.find('a').trigger('click');
            await flushPromises();

            expect(buttons.follow.classList.contains('d-none')).toBe(true);
            expect(buttons.unfollow.classList.contains('d-none')).toBe(true);
        });

        it('shows the one that matches the follow state again after leaving', async () => {
            const buttons = createFollowButtons(5);
            globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve(state({ isFollowing: true })));
            const wrapper = mountButton({
                initial: state({ state: 'member', canJoin: false, canLeave: true }),
                showMemberState: true,
            });

            await wrapper.find('a').trigger('click');
            await flushPromises();

            expect(buttons.unfollow.classList.contains('d-none')).toBe(false);
            expect(buttons.follow.classList.contains('d-none')).toBe(true);
        });

        it('leaves the buttons of another space alone', async () => {
            const other = createFollowButtons(9);
            const wrapper = mountButton({ initial: state() });

            await wrapper.find('a').trigger('click');
            await flushPromises();

            expect(other.follow.classList.contains('d-none')).toBe(false);
        });
    });

    it('falls back to a plain non-member state when the state cannot be fetched', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.reject({ status: 500 }));
        const wrapper = mountButton();
        await flushPromises();

        expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
        // `canJoin` is unknown in that case, so nothing is offered - joining is guarded
        // server side anyway.
        expect(wrapper.find('a').exists()).toBe(false);
    });
});
