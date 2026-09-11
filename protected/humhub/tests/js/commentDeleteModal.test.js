import { beforeEach, describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import CommentDeleteModal from '../../modules/comment/vue/components/CommentDeleteModal.vue';
import UiModal from '../../vue/UiModal.vue';
import HumHubForm from '../../vue/HumHubForm.vue';
import TextareaField from '../../vue/TextareaField.vue';
import CheckboxField from '../../vue/CheckboxField.vue';

await import('../../resources/js/humhub/humhub.vue.js');

// UiModal/TextareaField/CheckboxField resolve through the global Vue component
// registry in production (CoreVueAsset) - @vue/test-utils' `global.components`
// stands in for that registry here, same pattern as the other island suites.
const mountOptions = () => ({ global: { components: { UiModal, HumHubForm, TextareaField, CheckboxField } } });

// UiModal teleports to document.body and vue-test-utils never auto-unmounts.
const dialog = () => document.body.querySelector('.modal[role="dialog"]');

describe('CommentDeleteModal', () => {
    beforeEach(() => {
        document.body.querySelectorAll('.modal, .modal-backdrop').forEach((el) => el.remove());
    });

    it('renders nothing while closed', () => {
        mount(CommentDeleteModal, { ...mountOptions(), props: { show: false } });

        expect(dialog()).toBeNull();
    });

    describe('plain mode (own comment)', () => {
        it('shows the classic confirm question without any moderation fields', () => {
            const wrapper = mount(CommentDeleteModal, { ...mountOptions(), props: { show: true } });

            expect(dialog().querySelector('.modal-title').innerHTML)
                .toContain('<strong>Confirm</strong> comment deleting');
            expect(dialog().textContent).toContain('Do you really want to delete this comment?');
            expect(dialog().querySelector('textarea')).toBeNull();
            expect(dialog().querySelector('input[type="checkbox"]')).toBeNull();
            // Confirm is never gated in plain mode
            expect(dialog().querySelector('.btn-danger').disabled).toBe(false);

            wrapper.unmount();
        });

        it('emits confirm without moderation fields', async () => {
            const wrapper = mount(CommentDeleteModal, { ...mountOptions(), props: { show: true } });

            dialog().querySelector('.btn-danger').dispatchEvent(new MouseEvent('click', { bubbles: true }));
            await wrapper.vm.$nextTick();

            expect(wrapper.emitted('confirm')).toHaveLength(1);
            expect(wrapper.emitted('confirm')[0][0]).toBeNull();

            wrapper.unmount();
        });
    });

    describe('admin mode (moderating a foreign comment)', () => {
        it('defaults notify to checked with an enabled, empty reason and a gated Confirm', () => {
            const wrapper = mount(CommentDeleteModal, {
                ...mountOptions(),
                props: { show: true, adminMode: true },
            });

            expect(dialog().querySelector('.modal-title').innerHTML).toContain('<strong>Delete</strong> comment?');
            expect(dialog().querySelector('input[type="checkbox"]').checked).toBe(true);
            expect(dialog().querySelector('textarea').disabled).toBe(false);
            expect(dialog().querySelector('.btn-danger').disabled).toBe(true);
            // Reuses the legacy AdminDeleteCommentForm attribute labels ...
            expect(dialog().textContent).toContain('Reason');
            expect(dialog().textContent).toContain('Send a notification to author');
            // ... and its namespaced field names/ids (no bare global `message`/`notify`
            // ids, which would collide with same-named fields elsewhere in the document
            // — this dialog is teleported into document.body).
            const textarea = dialog().querySelector('textarea');
            expect(textarea.getAttribute('name')).toBe('AdminDeleteCommentForm[message]');
            expect(textarea.id).toBe('admindeletecommentform-message');
            expect(dialog().querySelector('input[type="checkbox"]').id)
                .toBe('admindeletecommentform-notify');
            expect(dialog().querySelector('label[for="admindeletecommentform-message"]')).not.toBeNull();

            wrapper.unmount();
        });

        it('emits the REST moderation fields once a reason is entered', async () => {
            const wrapper = mount(CommentDeleteModal, {
                ...mountOptions(),
                props: { show: true, adminMode: true },
            });

            const textarea = dialog().querySelector('textarea');
            textarea.value = '  Against the rules  ';
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            await wrapper.vm.$nextTick();

            expect(dialog().querySelector('.btn-danger').disabled).toBe(false);
            dialog().querySelector('.btn-danger').dispatchEvent(new MouseEvent('click', { bubbles: true }));
            await wrapper.vm.$nextTick();

            // Trimmed - a whitespace-only reason must not count as one either (below)
            expect(wrapper.emitted('confirm')[0][0]).toEqual({ notify: 1, message: 'Against the rules' });

            wrapper.unmount();
        });

        it('keeps Confirm gated for a whitespace-only reason', async () => {
            const wrapper = mount(CommentDeleteModal, {
                ...mountOptions(),
                props: { show: true, adminMode: true },
            });

            const textarea = dialog().querySelector('textarea');
            textarea.value = '   ';
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            await wrapper.vm.$nextTick();

            expect(dialog().querySelector('.btn-danger').disabled).toBe(true);

            wrapper.unmount();
        });

        it('disables the reason and drops the moderation fields when notify is unchecked', async () => {
            const wrapper = mount(CommentDeleteModal, {
                ...mountOptions(),
                props: { show: true, adminMode: true },
            });

            const checkbox = dialog().querySelector('input[type="checkbox"]');
            checkbox.checked = false;
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            await wrapper.vm.$nextTick();

            expect(dialog().querySelector('textarea').disabled).toBe(true);
            expect(dialog().querySelector('.btn-danger').disabled).toBe(false);

            dialog().querySelector('.btn-danger').dispatchEvent(new MouseEvent('click', { bubbles: true }));
            await wrapper.vm.$nextTick();

            expect(wrapper.emitted('confirm')[0][0]).toBeNull();

            wrapper.unmount();
        });

        it('resets notify/reason every time it reopens, so a canceled delete never leaks its draft', async () => {
            const wrapper = mount(CommentDeleteModal, {
                ...mountOptions(),
                props: { show: true, adminMode: true },
            });

            const textarea = dialog().querySelector('textarea');
            textarea.value = 'half-typed reason';
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            const checkbox = dialog().querySelector('input[type="checkbox"]');
            checkbox.checked = false;
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            await wrapper.vm.$nextTick();

            // Cancel closes via update:show (the caller owns the flag)
            dialog().querySelector('.btn-light').dispatchEvent(new MouseEvent('click', { bubbles: true }));
            await wrapper.vm.$nextTick();
            expect(wrapper.emitted('update:show').at(-1)).toEqual([false]);

            await wrapper.setProps({ show: false });
            await wrapper.setProps({ show: true });

            expect(dialog().querySelector('textarea').value).toBe('');
            expect(dialog().querySelector('input[type="checkbox"]').checked).toBe(true);
            expect(wrapper.emitted('confirm')).toBeUndefined();

            wrapper.unmount();
        });
    });
});
