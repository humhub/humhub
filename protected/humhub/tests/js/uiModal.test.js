import { afterEach, describe, expect, it } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import { h } from 'vue';
import UiModal from '../../vue/UiModal.vue';

// Every mounted wrapper is tracked here so afterEach can unmount it - UiModal
// teleports its dialog/backdrop onto the REAL document.body regardless of the
// wrapper's own mount point, so a forgotten unmount() would leak markup (and,
// for the open ones, the ESC keydown listener + body.modal-open class) into
// the next test.
let wrapper;

const mountModal = (options) => {
    wrapper = mount(UiModal, options);
    return wrapper;
};

const dialog = () => document.body.querySelector('.modal[role="dialog"]');
const backdrop = () => document.body.querySelector('.modal-backdrop');

afterEach(() => {
    wrapper?.unmount();
    wrapper = undefined;
    document.body.classList.remove('modal-open');
});

describe('UiModal', () => {
    it('renders nothing when closed, and teleports the dialog + backdrop to body when open', async () => {
        mountModal({ props: { show: false, title: 'Title' } });

        expect(dialog()).toBeNull();
        expect(backdrop()).toBeNull();

        await wrapper.setProps({ show: true });

        expect(dialog()).not.toBeNull();
        expect(backdrop()).not.toBeNull();
        // The dialog is NOT a descendant of the wrapper's own root - it was teleported.
        expect(wrapper.find('.modal').exists()).toBe(false);
    });

    it('renders the fallback header from the title prop plus a standard close button', async () => {
        mountModal({ props: { show: true, title: 'Users who like this' } });
        await flushPromises();

        const title = dialog().querySelector('.modal-title');
        expect(title.textContent).toBe('Users who like this');
        expect(dialog().querySelector('.modal-header .btn-close')).not.toBeNull();
    });

    it('renders no modal-footer element at all when the footer slot is not provided', async () => {
        mountModal({ props: { show: true } });
        await flushPromises();

        expect(dialog().querySelector('.modal-footer')).toBeNull();
    });

    it('renders modal-footer only when the footer slot is provided', async () => {
        mountModal({
            props: { show: true },
            slots: { footer: '<button class="btn btn-primary">OK</button>' },
        });
        await flushPromises();

        const footer = dialog().querySelector('.modal-footer');
        expect(footer).not.toBeNull();
        expect(footer.textContent).toBe('OK');
    });

    it('emits update:show(false) and closes when the close button is clicked', async () => {
        mountModal({ props: { show: true, title: 'T' } });
        await flushPromises();

        await dialog().querySelector('.btn-close').dispatchEvent(new MouseEvent('click', { bubbles: true }));

        expect(wrapper.emitted('update:show')).toEqual([[false]]);
    });

    it('closes on a backdrop click (clicking the .modal root itself)', async () => {
        mountModal({ props: { show: true, title: 'T' } });
        await flushPromises();

        dialog().dispatchEvent(new MouseEvent('click', { bubbles: true }));

        expect(wrapper.emitted('update:show')).toEqual([[false]]);
    });

    it('does not close on a backdrop click when clicking inside the dialog content', async () => {
        mountModal({ props: { show: true, title: 'T' } });
        await flushPromises();

        dialog().querySelector('.modal-body').dispatchEvent(new MouseEvent('click', { bubbles: true }));

        expect(wrapper.emitted('update:show')).toBeUndefined();
    });

    it('does not close on a backdrop click when backdropClose is false', async () => {
        mountModal({ props: { show: true, title: 'T', backdropClose: false } });
        await flushPromises();

        dialog().dispatchEvent(new MouseEvent('click', { bubbles: true }));

        expect(wrapper.emitted('update:show')).toBeUndefined();
    });

    it('closes on Escape', async () => {
        mountModal({ props: { show: true, title: 'T' } });
        await flushPromises();

        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));

        expect(wrapper.emitted('update:show')).toEqual([[false]]);
    });

    it('does not close on Escape when keyboard is false', async () => {
        mountModal({ props: { show: true, title: 'T', keyboard: false } });
        await flushPromises();

        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));

        expect(wrapper.emitted('update:show')).toBeUndefined();
    });

    it('stops listening for Escape once closed', async () => {
        mountModal({ props: { show: true, title: 'T' } });
        await flushPromises();

        await wrapper.setProps({ show: false });
        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));

        // setProps() sets the prop directly (no emit) - a stray ESC listener still
        // attached after closing would be the only possible source of an
        // update:show emit here, so none at all confirms it was removed.
        expect(wrapper.emitted('update:show')).toBeUndefined();
    });

    it('adds body.modal-open while shown and removes it once closed', async () => {
        mountModal({ props: { show: false, title: 'T' } });
        expect(document.body.classList.contains('modal-open')).toBe(false);

        await wrapper.setProps({ show: true });
        expect(document.body.classList.contains('modal-open')).toBe(true);

        await wrapper.setProps({ show: false });
        expect(document.body.classList.contains('modal-open')).toBe(false);
    });

    it('adds body.modal-open immediately for a modal mounted already open', () => {
        mountModal({ props: { show: true, title: 'T' } });
        expect(document.body.classList.contains('modal-open')).toBe(true);
    });

    it('emits opened once the dialog finishes mounting and focuses the dialog', async () => {
        mountModal({ props: { show: true, title: 'T' } });
        await flushPromises();

        expect(wrapper.emitted('opened')).toHaveLength(1);
        expect(document.activeElement).toBe(dialog());
    });

    it('emits closed and restores focus to the previously focused element', async () => {
        const trigger = document.createElement('button');
        document.body.appendChild(trigger);
        trigger.focus();
        expect(document.activeElement).toBe(trigger);

        mountModal({ props: { show: true, title: 'T' } });
        await flushPromises();
        expect(document.activeElement).toBe(dialog());

        await wrapper.setProps({ show: false });

        expect(wrapper.emitted('closed')).toHaveLength(1);
        expect(document.activeElement).toBe(trigger);

        trigger.remove();
    });

    it('wires aria-modal, role and aria-labelledby to the rendered title id', async () => {
        mountModal({ props: { show: true, title: 'Users who like this' } });
        await flushPromises();

        const el = dialog();
        expect(el.getAttribute('role')).toBe('dialog');
        expect(el.getAttribute('aria-modal')).toBe('true');

        const labelledBy = el.getAttribute('aria-labelledby');
        expect(labelledBy).toBeTruthy();
        expect(document.getElementById(labelledBy)).toBe(el.querySelector('.modal-title'));
    });

    it('gives two instances distinct title ids', async () => {
        const a = mount(UiModal, { props: { show: true, title: 'A' } });
        const b = mount(UiModal, { props: { show: true, title: 'B' } });
        await flushPromises();

        const ids = [...document.body.querySelectorAll('.modal-title')].map((el) => el.id);
        expect(new Set(ids).size).toBe(ids.length);

        a.unmount();
        b.unmount();
    });

    it('applies modal-sm for size=small and modal-lg for size=large, neither for normal', async () => {
        mountModal({ props: { show: true, size: 'small' } });
        await flushPromises();
        expect(dialog().querySelector('.modal-dialog').classList.contains('modal-sm')).toBe(true);

        await wrapper.setProps({ size: 'large' });
        const dlg = dialog().querySelector('.modal-dialog');
        expect(dlg.classList.contains('modal-lg')).toBe(true);
        expect(dlg.classList.contains('modal-sm')).toBe(false);

        await wrapper.setProps({ size: 'normal' });
        const dlgNormal = dialog().querySelector('.modal-dialog');
        expect(dlgNormal.classList.contains('modal-sm')).toBe(false);
        expect(dlgNormal.classList.contains('modal-lg')).toBe(false);
    });

    it('lets a custom header slot render its own title wired to the exposed titleId', async () => {
        mountModal({
            props: { show: true },
            slots: {
                header: (slotProps) => h('h5', { id: slotProps.titleId }, 'Custom'),
            },
        });
        await flushPromises();

        expect(dialog().querySelector('.modal-header').textContent).toBe('Custom');
        expect(dialog().querySelector('.btn-close')).toBeNull();

        const heading = dialog().querySelector('.modal-header h5');
        expect(heading.id).toBe(dialog().getAttribute('aria-labelledby'));
    });

    it('cleans up the ESC listener and modal-open class if destroyed while still open', async () => {
        mountModal({ props: { show: true, title: 'T' } });
        await flushPromises();

        wrapper.unmount();
        wrapper = undefined;

        expect(document.body.classList.contains('modal-open')).toBe(false);
        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
        // No listener left to react - nothing to assert beyond "did not throw".
    });
});
