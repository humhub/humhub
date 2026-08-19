import { beforeEach, describe, expect, it, vi } from 'vitest';

await import('../../modules/comment/resources/js/humhub.comment.js');

const commentModule = globalThis.humhub.modules.comment;

// DOM fixture helper: build elements without HTML-string parsing (mirrors bridge.test.js).
const createTag = (tag, attributes = {}, parent = document.body) => {
    const el = document.createElement(tag);
    Object.entries(attributes).forEach(([name, value]) => el.setAttribute(name, value));
    parent.appendChild(el);
    return el;
};

describe('comment bridge (humhub.comment.js)', () => {
    beforeEach(() => {
        document.body.replaceChildren();
    });

    describe('toggleComment', () => {
        it('dispatches a bubbling humhub:comment:toggle on the resolved $target', () => {
            const island = createTag('comment-section', { id: 'comment_C42P' });
            const received = [];
            document.addEventListener('humhub:comment:toggle', (evt) => received.push(evt));

            commentModule.toggleComment({ $target: jQuery(island) });

            expect(received).toHaveLength(1);
            expect(received[0].target).toBe(island);
            expect(received[0].bubbles).toBe(true);
        });

        it('is a no-op when $target resolves to an empty jQuery set', () => {
            expect(() => commentModule.toggleComment({ $target: jQuery() })).not.toThrow();
        });
    });

    describe('scrollActive / scrollInactive', () => {
        it('toggles the scrollActive class on the closest richtext-create-input-group', () => {
            const wrapper = createTag('div', { class: 'richtext-create-input-group' });
            const trigger = document.createElement('span');
            wrapper.appendChild(trigger);

            commentModule.scrollActive({ $trigger: jQuery(trigger) });
            expect(wrapper.classList.contains('scrollActive')).toBe(true);

            commentModule.scrollInactive({ $trigger: jQuery(trigger) });
            expect(wrapper.classList.contains('scrollActive')).toBe(false);
        });
    });

    describe('humhub:comment:countChanged bridge (bound once at module.init)', () => {
        const dispatchCountChanged = (island, total) => {
            island.dispatchEvent(new CustomEvent('humhub:comment:countChanged', {
                bubbles: true,
                detail: { contentId: 1, total },
            }));
        };

        it('updates the comment-count badge inside the same .stream-entry-addons', () => {
            const addons = createTag('div', { class: 'stream-entry-addons' });
            const controls = createTag('div', { class: 'wall-entry-controls' }, addons);
            const badge = createTag('span', { class: 'comment-count', 'data-count': '2' }, controls);
            badge.textContent = ' (2)';
            const island = createTag('comment-section', {}, addons);

            dispatchCountChanged(island, 3);

            expect(badge.textContent).toBe(' (3)');
            expect(badge.getAttribute('data-count')).toBe('3');
            expect(jQuery(badge).data('count')).toBe(3);
            expect(badge.style.display).not.toBe('none');
        });

        it('hides the badge once the total drops to zero', () => {
            const addons = createTag('div', { class: 'stream-entry-addons' });
            const controls = createTag('div', { class: 'wall-entry-controls' }, addons);
            const badge = createTag('span', { class: 'comment-count', 'data-count': '1' }, controls);
            const island = createTag('comment-section', {}, addons);

            dispatchCountChanged(island, 0);

            expect(badge.style.display).toBe('none');
        });

        it('does nothing when the island has no .stream-entry-addons ancestor', () => {
            const island = createTag('comment-section');

            expect(() => dispatchCountChanged(island, 3)).not.toThrow();
        });

        it('does nothing when the entry has no comment-count badge', () => {
            const addons = createTag('div', { class: 'stream-entry-addons' });
            const island = createTag('comment-section', {}, addons);

            expect(() => dispatchCountChanged(island, 3)).not.toThrow();
        });

        it('does not double-bind the document listener across repeated module.init() calls', () => {
            // The harness already called module.init() once at registration time (mirroring
            // the real core), so the guard must already be in place - a second call must not
            // attach another listener.
            const spy = vi.spyOn(document, 'addEventListener');

            commentModule.init();
            commentModule.init();

            expect(spy).not.toHaveBeenCalled();
            spy.mockRestore();
        });
    });
});
