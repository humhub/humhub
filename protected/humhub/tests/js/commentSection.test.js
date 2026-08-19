import { beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import CommentSection from '../../modules/comment/vue/CommentSection.vue';
import CommentForm from '../../modules/comment/vue/components/CommentForm.vue';
import LikeButton from '../../modules/like/vue/LikeButton.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

// v-additions is registered per Vue *app* by humhub.vue.js's island mounter,
// not globally on the Vue runtime - mirrors commentInterop.test.js's stand-in
// verbatim so spying on it observes exactly what production wiring calls.
const additionsDirective = {
    mounted(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
    updated(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
};

const mountOptions = () => ({
    global: {
        directives: { additions: additionsDirective },
        components: { LikeButton },
    },
});

// Synthetic __VUEFORM__ shell: a minimal stand-in for the real
// comment/widgets/views/form.php contract (see LegacyFormWrapper's own
// docblock and commentInterop.test.js) - only what CommentForm itself
// touches (a <form> to attach the submit listener to, a `.humhub-ui-richtext`
// node so LegacyFormWrapper.focus()/getValue() have something to look up).
const buildShell = () => `
    <div id="comment_create_form___VUEFORM__" class="comment_create content_create">
        <form id="w___VUEFORM__" action="/comment/comment/post" method="post">
            <div class="humhub-ui-richtext" id="newCommentForm___VUEFORM__"></div>
            <button type="submit" class="btn-comment-submit">Send</button>
        </form>
    </div>
`;

const makeAuthor = (overrides = {}) => ({
    guid: 'user-guid-1',
    displayName: 'Alice',
    url: '/user/alice',
    imageUrl: '/uploads/alice.jpg',
    ...overrides,
});

const makeComment = (overrides = {}) => ({
    id: 1,
    contentId: 42,
    parentCommentId: null,
    recordId: 100,
    createdAt: '2026-08-01T10:00:00+00:00',
    isEdited: false,
    author: makeAuthor(),
    blocked: false,
    messageOutput: '<div class="richtext-output">Hello world</div>',
    attachmentsHtml: null,
    likes: { count: 0, liked: false },
    canEdit: false,
    canDelete: false,
    canAdminDelete: false,
    permalink: '/comment/perma/1',
    children: { total: 0, items: [], hasMore: false },
    ...overrides,
});

const emptyWindow = (overrides = {}) => ({
    comments: [],
    prevCount: 0,
    nextCount: 0,
    total: 0,
    ...overrides,
});

describe('CommentSection', () => {
    beforeEach(() => {
        globalThis.humhub.modules.url.config.template = '/__route__';
        globalThis.humhub.config.module('user').isGuest = false;
        globalThis.humhub.config.module('user').loginUrl = '/user/auth/login';
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(emptyWindow()));
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({}));
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.logCalls.warn.length = 0;
    });

    describe('hydration', () => {
        it('renders from the initial payload without fetching', () => {
            const initial = { comments: [makeComment()], prevCount: 0, nextCount: 0, total: 1 };

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial },
            });

            expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
            expect(wrapper.find('.single-comment').exists()).toBe(true);
            expect(wrapper.text()).toContain('Alice');
        });

        it('self-fetches the default window when initial is null', async () => {
            const response = { comments: [makeComment()], prevCount: 0, nextCount: 0, total: 1 };
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(response));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, pageSize: 10 },
            });

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/comment/comment/list?contentId=42&pageSize=10');
            await vi.waitFor(() => expect(wrapper.find('.single-comment').exists()).toBe(true));
        });

        it('settles into an empty rendered state and logs when the self-fetch fails', async () => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.reject(new Error('forbidden')));

            const wrapper = mount(CommentSection, { ...mountOptions(), props: { contentId: 42 } });

            await vi.waitFor(() => expect(wrapper.vm.loaded).toBe(true));
            expect(globalThis.humhubStubs.logCalls.error.length).toBeGreaterThan(0);
            expect(wrapper.find('.single-comment').exists()).toBe(false);
        });
    });

    describe('class/id parity with the legacy markup', () => {
        it('renders the section root with the legacy comment-container classes', () => {
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow() },
            });

            const root = wrapper.find('.comment-container');
            expect(root.classes()).toEqual(expect.arrayContaining(['bg-light', 'p-2', 'mt-3', 'comment-container']));
        });

        it('gives the list container the IdHelper-format comments_area id and no guest-mode class for a member', () => {
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow() },
            });

            const list = wrapper.find('.comment');
            expect(list.attributes('id')).toBe('comments_area_C42P');
            expect(list.classes()).not.toContain('guest-mode');
        });

        it('adds guest-mode to the list container for a guest viewer', () => {
            globalThis.humhub.config.module('user').isGuest = true;

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow() },
            });

            expect(wrapper.find('.comment').classes()).toContain('guest-mode');
        });
    });

    describe('guest and permission handling', () => {
        it('hides the form for guests even when canComment is true', () => {
            globalThis.humhub.config.module('user').isGuest = true;

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow(), canComment: true, formShellHtml: buildShell() },
            });

            expect(wrapper.findComponent(CommentForm).exists()).toBe(false);
        });

        it('hides the form when canComment is false', () => {
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow(), canComment: false, formShellHtml: buildShell() },
            });

            expect(wrapper.findComponent(CommentForm).exists()).toBe(false);
        });

        it('renders the form for a logged-in user who can comment', () => {
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow(), canComment: true, formShellHtml: buildShell() },
            });

            expect(wrapper.findComponent(CommentForm).exists()).toBe(true);
        });
    });

    describe('show more (root list)', () => {
        it('loads and prepends previous comments with the right url, and updates the remaining count', async () => {
            const initial = {
                comments: [makeComment({ id: 5 }), makeComment({ id: 6 })],
                prevCount: 3,
                nextCount: 0,
                total: 5,
            };
            const response = { comments: [makeComment({ id: 3 }), makeComment({ id: 4 })], prevCount: 1, nextCount: 0, total: 5 };
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(response));

            const wrapper = mount(CommentSection, { ...mountOptions(), props: { contentId: 42, initial, pageSize: 2 } });

            await wrapper.find('.showMore a').trigger('click');

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith(
                '/comment/comment/list?contentId=42&commentId=5&direction=previous&pageSize=2',
            );

            await vi.waitFor(() => {
                const ids = wrapper.findAll('.single-comment').map((entry) => entry.attributes('id'));
                expect(ids).toEqual(['comment_3', 'comment_4', 'comment_5', 'comment_6']);
            });
            // Real remaining count from the response replaces the initial one.
            expect(wrapper.findAll('.showMore').length).toBe(1);
        });

        it('loads and appends next comments with the right url, and updates the remaining count', async () => {
            const initial = {
                comments: [makeComment({ id: 5 }), makeComment({ id: 6 })],
                prevCount: 0,
                nextCount: 3,
                total: 5,
            };
            const response = { comments: [makeComment({ id: 7 })], prevCount: 0, nextCount: 0, total: 5 };
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(response));

            const wrapper = mount(CommentSection, { ...mountOptions(), props: { contentId: 42, initial, pageSize: 2 } });

            await wrapper.find('.showMore a').trigger('click');

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith(
                '/comment/comment/list?contentId=42&commentId=6&direction=next&pageSize=2',
            );

            await vi.waitFor(() => {
                const ids = wrapper.findAll('.single-comment').map((entry) => entry.attributes('id'));
                expect(ids).toEqual(['comment_5', 'comment_6', 'comment_7']);
            });
            expect(wrapper.findAll('.showMore').length).toBe(0);
        });

        it('guards against concurrent show-more clicks while a request is in flight', async () => {
            let resolveGet;
            globalThis.humhubStubs.client.get = vi.fn(() => new Promise((resolve) => { resolveGet = resolve; }));
            const initial = { comments: [makeComment({ id: 5 })], prevCount: 2, nextCount: 0, total: 3 };

            const wrapper = mount(CommentSection, { ...mountOptions(), props: { contentId: 42, initial } });

            await wrapper.find('.showMore a').trigger('click');
            await wrapper.find('.showMore a').trigger('click');
            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);

            resolveGet({ comments: [], prevCount: 0, nextCount: 0, total: 3 });
            await vi.waitFor(() => expect(wrapper.findAll('.showMore').length).toBe(0));
        });

        it('does not render a show-more link when the count is nonzero but no items are loaded yet', () => {
            // Defensive edge case: render condition must match the click
            // guard (`items.length === 0` no-ops the handler) so a stray
            // prevCount/nextCount without a loaded window never renders a
            // dead link.
            const initial = { comments: [], prevCount: 3, nextCount: 2, total: 5 };

            const wrapper = mount(CommentSection, { ...mountOptions(), props: { contentId: 42, initial } });

            expect(wrapper.find('.showMore').exists()).toBe(false);
        });
    });

    describe('replies (one level)', () => {
        it('renders the preview and loads more replies with parentCommentId + cursor', async () => {
            const root = makeComment({
                id: 1,
                children: {
                    total: 2,
                    items: [makeComment({ id: 10, parentCommentId: 1, children: null })],
                    hasMore: true,
                },
            });
            // total: 2 matches the 2 replies that ever exist across both
            // responses (10, then 11) - childHasMore is derived the same way
            // the server derives it for previews (`total > count(items)`),
            // so an inconsistent total here would make the assertion below
            // fail regardless of nextCount.
            const response = { comments: [makeComment({ id: 11, parentCommentId: 1, children: null })], prevCount: 0, nextCount: 0, total: 2 };
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(response));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [root], prevCount: 0, nextCount: 0, total: 1 }, pageSize: 5 },
            });

            expect(wrapper.find('.nested-comments-root .single-comment').exists()).toBe(true);
            expect(wrapper.find('.nested-comments-root .showMore').exists()).toBe(true);

            await wrapper.find('.nested-comments-root .showMore a').trigger('click');

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith(
                '/comment/comment/list?contentId=42&parentCommentId=1&commentId=10&direction=next&pageSize=5',
            );

            await vi.waitFor(() => {
                const ids = wrapper.findAll('.nested-comments-root .single-comment').map((entry) => entry.attributes('id'));
                expect(ids).toEqual(['comment_10', 'comment_11']);
            });
            expect(wrapper.find('.nested-comments-root .showMore').exists()).toBe(false);
        });

        it('does not show a reply toggle on an already-nested reply', () => {
            const root = makeComment({
                id: 1,
                children: { total: 1, items: [makeComment({ id: 10, parentCommentId: 1, children: null })], hasMore: false },
            });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [root], prevCount: 0, nextCount: 0, total: 1 }, canComment: true, formShellHtml: buildShell() },
            });

            const rootControls = wrapper.find('.single-comment').find('.wall-entry-controls');
            expect(rootControls.text()).toContain('Reply');

            const nestedControls = wrapper.find('.nested-comments-root .single-comment .wall-entry-controls');
            expect(nestedControls.text()).not.toContain('Reply');
        });
    });

    describe('blocked authors', () => {
        it('masks the entry with no message/author leak, and reveals it in place', async () => {
            const blocked = makeComment({
                id: 9,
                blocked: true,
                author: null,
                messageOutput: null,
                attachmentsHtml: null,
                likes: null,
            });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [blocked], prevCount: 0, nextCount: 0, total: 1 } },
            });

            expect(wrapper.find('.comment-blocked-user').exists()).toBe(true);
            expect(wrapper.find('.single-comment').exists()).toBe(false);
            expect(wrapper.text()).not.toContain('Hello world');
            expect(wrapper.text()).not.toContain('Alice');

            const revealed = makeComment({ id: 9, blocked: false });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(revealed));

            await wrapper.find('.comment-blocked-user a').trigger('click');

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/comment/comment/info?id=9&showBlocked=1');

            await vi.waitFor(() => expect(wrapper.find('.single-comment').exists()).toBe(true));
            expect(wrapper.find('.comment-blocked-user').exists()).toBe(false);
            expect(wrapper.text()).toContain('Hello world');
        });
    });

    describe('permalink anchor', () => {
        it('highlights the anchored comment', () => {
            const comments = [makeComment({ id: 1 }), makeComment({ id: 2 })];

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments, prevCount: 0, nextCount: 0, total: 2 }, anchorCommentId: 2 },
            });

            expect(wrapper.find('#comment_1').classes()).not.toContain('comment-current');
            expect(wrapper.find('#comment_2').classes()).toContain('comment-current');
        });
    });

    describe('toggle + count bridge', () => {
        it('un-collapses and focuses the form on humhub:comment:toggle', async () => {
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: emptyWindow(),
                    canComment: true,
                    formShellHtml: buildShell(),
                    collapsed: true,
                },
            });

            expect(wrapper.find('.comment-container').classes()).toContain('d-none');

            const focusSpy = vi.spyOn(wrapper.findComponent(CommentForm).vm, 'focus');

            wrapper.element.parentElement.dispatchEvent(new CustomEvent('humhub:comment:toggle'));
            await wrapper.vm.$nextTick();
            await wrapper.vm.$nextTick();

            expect(wrapper.find('.comment-container').classes()).not.toContain('d-none');
            expect(focusSpy).toHaveBeenCalledTimes(1);
        });

        it('stops listening for toggle after unmount', () => {
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow(), collapsed: true },
            });
            const mountEl = wrapper.element.parentElement;

            wrapper.unmount();
            expect(() => mountEl.dispatchEvent(new CustomEvent('humhub:comment:toggle'))).not.toThrow();
        });

        it('dispatches humhub:comment:countChanged on the mount element when total changes', async () => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ comments: [], prevCount: 0, nextCount: 0, total: 5 }));

            const wrapper = mount(CommentSection, { ...mountOptions(), props: { contentId: 42 } });

            const handler = vi.fn();
            wrapper.element.parentElement.addEventListener('humhub:comment:countChanged', handler);

            await vi.waitFor(() => expect(wrapper.vm.total).toBe(5));

            expect(handler).toHaveBeenCalledTimes(1);
            expect(handler.mock.calls[0][0].detail).toEqual({ contentId: 42, total: 5 });
        });

        it('does not dispatch countChanged for the initial hydration value', async () => {
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [], prevCount: 0, nextCount: 0, total: 3 } },
            });

            const handler = vi.fn();
            wrapper.element.parentElement.addEventListener('humhub:comment:countChanged', handler);

            await wrapper.vm.$nextTick();
            expect(handler).not.toHaveBeenCalled();
        });
    });

    describe('LikeButton nesting', () => {
        it('renders LikeButton inside an entry with its initial like props', () => {
            const comment = makeComment({ likes: { count: 3, liked: true } });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            expect(wrapper.find('a.unlike').exists()).toBe(true);
            expect(wrapper.find('.likeCount').text()).toBe('(3)');
        });

        it('does not render a LikeButton when likes are unavailable', () => {
            const comment = makeComment({ likes: null });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            expect(wrapper.find('.likeLinkContainer').exists()).toBe(false);
        });
    });

    describe('edit/delete menu visibility', () => {
        // Actual edit/delete/admin-delete/live-update mutation behavior is
        // covered in commentMutations.test.js — this file stays focused on
        // the read path per docs/superpowers/plans/2026-08-19-vuejs-comments.md.
        it('hides edit/delete menu items when their flags are false', () => {
            const comment = makeComment({ canEdit: false, canDelete: false });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            const items = wrapper.findAll('.dropdown-item').map((item) => item.text());
            expect(items).not.toContain('Edit');
            expect(items).not.toContain('Delete');
        });
    });
});
