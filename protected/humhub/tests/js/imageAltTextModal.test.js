import { afterEach, describe, expect, it } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import ImageAltTextModal from '../../vue/ImageAltTextModal.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

let wrapper;
let host;
let events;

// Mounted into a mount point of its own, as the editor does, which collects the result events
const open = async (props = { src: 'https://x.test/a.png' }) => {
    host = document.createElement('div');
    document.body.appendChild(host);
    events = [];
    host.addEventListener('altText:save', (evt) => events.push(['save', evt.detail]));
    host.addEventListener('altText:cancel', () => events.push(['cancel']));
    wrapper = mount(ImageAltTextModal, { props, attachTo: host });
    await flushPromises();
};

const dialog = () => document.body.querySelector('.modal[role="dialog"]');
const input = () => dialog().querySelector('input');
const footerButton = (selector) => dialog().querySelector('.modal-footer ' + selector);
const type = async (value) => {
    input().value = value;
    input().dispatchEvent(new Event('input'));
    await flushPromises();
};

afterEach(() => {
    wrapper?.unmount();
    host?.remove();
    wrapper = undefined;
    document.body.classList.remove('modal-open');
});

describe('ImageAltTextModal', () => {
    it('opens with the image, a labelled field and its hint, and focuses the field', async () => {
        await open();

        expect(dialog()).not.toBeNull();
        expect(document.querySelector('.modal-backdrop')).not.toBeNull();
        expect(dialog().querySelector('.modal-title').textContent).toBe('Describe the image');
        expect(dialog().querySelector('img.image-alt-text-preview').getAttribute('src')).toBe('https://x.test/a.png');
        expect(dialog().querySelector(`label[for="${input().id}"]`).textContent).toBe('Alternative text');
        expect(input().getAttribute('aria-describedby')).toContain(dialog().querySelector('.form-text').id);
        expect(document.activeElement).toBe(input());
    });

    it('saves the trimmed text, by the Save button or by Enter', async () => {
        await open();
        await type(' A red bicycle ');
        footerButton('.btn-primary').click();
        await flushPromises();

        expect(dialog()).toBeNull();
        expect(events).toEqual([['save', { alt: 'A red bicycle' }]]);

        wrapper.unmount();
        host.remove();
        await open();
        await type('Sunset');
        dialog().querySelector('form').dispatchEvent(new Event('submit', { cancelable: true }));
        await flushPromises();

        expect(events).toEqual([['save', { alt: 'Sunset' }]]);
    });

    it('cancels when skipped or dismissed with Escape', async () => {
        await open();
        footerButton('.btn-light').click();
        await flushPromises();
        expect(events).toEqual([['cancel']]);

        wrapper.unmount();
        host.remove();
        await open();
        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
        await flushPromises();
        expect(dialog()).toBeNull();
        expect(events).toEqual([['cancel']]);
    });

    it('when required, refuses an empty text and cannot be dismissed', async () => {
        await open({ src: 'https://x.test/a.png', required: true });

        expect(dialog().querySelector('.btn-close')).toBeNull();
        expect(footerButton('.btn-light').textContent).toBe('Remove image');
        expect(dialog().querySelector('.form-text').textContent).toBe('Describe the image for people who cannot see it.');
        expect(input().getAttribute('aria-required')).toBe('true');

        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
        await flushPromises();
        expect(dialog()).not.toBeNull();

        footerButton('.btn-primary').click();
        await flushPromises();
        expect(dialog()).not.toBeNull();
        expect(input().classList.contains('is-invalid')).toBe(true);
        expect(dialog().querySelector('.invalid-feedback').textContent).toBe('Please describe the image.');
        expect(document.activeElement).toBe(input());
        expect(events).toEqual([]);

        await type('A red bicycle');
        expect(dialog().querySelector('.invalid-feedback')).toBeNull();
        footerButton('.btn-primary').click();
        await flushPromises();
        expect(dialog()).toBeNull();
        expect(events).toEqual([['save', { alt: 'A red bicycle' }]]);
    });

    it('when required, "Remove image" cancels', async () => {
        await open({ src: null, required: true });
        footerButton('.btn-light').click();
        await flushPromises();

        expect(dialog()).toBeNull();
        expect(events).toEqual([['cancel']]);
    });

    it('shows no preview without an image source', async () => {
        await open({ src: null });

        expect(dialog().querySelector('img')).toBeNull();
    });
});
