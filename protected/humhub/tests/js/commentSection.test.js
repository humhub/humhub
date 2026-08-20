import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import CommentSection from '../../modules/comment/vue/CommentSection.vue';
import CommentForm from '../../modules/comment/vue/components/CommentForm.vue';
import LikeButton from '../../modules/like/vue/LikeButton.vue';
import RichTextOutput from '../../vue/RichTextOutput.vue';
import LegacyFormWrapper from '../../vue/LegacyFormWrapper.vue';
import DropdownMenu from '../../vue/DropdownMenu.vue';
import ExtensionSlot from '../../vue/ExtensionSlot.vue';
import UserImage from '../../modules/user/vue/UserImage.vue';
import HumHubForm from '../../vue/HumHubForm.vue';
import RichTextField from '../../vue/RichTextField.vue';
import SubmitButton from '../../vue/SubmitButton.vue';
import UiModal from '../../vue/UiModal.vue';
import UserList from '../../modules/user/vue/UserList.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

// v-additions is registered per Vue *app* by humhub.vue.js's island mounter,
// not globally on the Vue runtime - mirrors coreInterop.test.js's stand-in
// verbatim so spying on it observes exactly what production wiring calls.
const additionsDirective = {
    mounted(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
    updated(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
};

// CommentEntry/CommentForm/CommentControls no longer import RichTextOutput/
// LegacyFormWrapper/DropdownMenu/ExtensionSlot/HumHubForm/RichTextField/SubmitButton
// directly (they now resolve through the global Vue component registry - see their own
// docblocks) - @vue/test-utils' `global.components` stands in for that registry here, the
// same way it already does for LikeButton below. UiModal/UserList are needed too - nested
// two levels down, inside LikeButton's own (always-rendered) user-list modal.
const mountOptions = () => ({
    global: {
        directives: { additions: additionsDirective },
        components: {
            LikeButton, RichTextOutput, LegacyFormWrapper, DropdownMenu, ExtensionSlot, UserImage,
            HumHubForm, RichTextField, SubmitButton, UiModal, UserList,
        },
    },
});

// Synthetic __VUEFORM__ shell: a minimal stand-in for the real
// comment/widgets/views/form.php contract (see LegacyFormWrapper's own
// docblock and coreInterop.test.js) - only what CommentForm itself
// touches (a <form> to attach the submit listener to, a `.humhub-ui-richtext`
// node so LegacyFormWrapper.focus()/getValue() have something to look up).
const buildShell = () => `
    <div id="comment_create_form___VUEFORM__" class="comment_create content_create">
        <form id="w___VUEFORM__" action="/comment/comment/post" method="post">
            <div data-ui-widget="ui.richtext.prosemirror.RichTextEditor" class="humhub-ui-richtext" id="newCommentForm___VUEFORM__"></div>
            <button type="submit" class="btn-comment-submit">Send</button>
        </form>
    </div>
`;

const makeAuthor = (overrides = {}) => ({
    guid: 'user-guid-1',
    displayName: 'Alice',
    url: '/user/alice',
    imageUrl: '/uploads/alice.jpg',
    contentContainerId: 5,
    imageAlt: 'Profile picture of Alice',
    // null (no overlay) by default - matches CommentJsonService::serializeOnlineStatus()'s
    // own default for the online-status feature disabled/self-comment case.
    online: null,
    ...overrides,
});

const makeComment = (overrides = {}) => ({
    id: 1,
    contentId: 42,
    parentCommentId: null,
    recordId: 100,
    createdAt: '2026-08-01T10:00:00+00:00',
    isEdited: false,
    updatedAt: null,
    author: makeAuthor(),
    blocked: false,
    message: 'Hello world',
    messageRenderOptions: { 'ui-richtext': true, 'ui-widget': 'ui.richtext.prosemirror.RichText' },
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
            // total: 3 (not 5) - the response confirms comment 7 is the LAST one that exists
            // (nextCount: 0), so the true total is 3 (5,6,7), not the stale 5 the hydration
            // payload guessed - see CommentList's own "Next-pagination gap fix" docblock
            // section for why `remainingNext` is now derived from `total`, refreshed from
            // this same response, rather than trusted directly off `nextCount`.
            const response = { comments: [makeComment({ id: 7 })], prevCount: 0, nextCount: 0, total: 3 };
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

            // total: 1 (not 3) - the response confirms nothing remains before item 5 in
            // either direction (empty comments, prevCount 0), so the true total is just
            // the one already-loaded item, not the stale 3 the hydration payload guessed -
            // see CommentList's own "Next-pagination gap fix" docblock section for why
            // `remainingNext` is now derived from `total`, refreshed from every response.
            resolveGet({ comments: [], prevCount: 0, nextCount: 0, total: 1 });
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

        // Bug: the nested reply list rendered with no padding/background at all -
        // CommentEntry.vue's nested block (`.nested-comments-root > .comment > ...`)
        // dropped the `.bg-light.p-2.mt-3.comment-container` wrapper the legacy
        // `Comments::widget()` template (`comments.php`) renders around the exact same
        // content at EVERY nesting level, root included - CommentSection.vue already
        // reproduces that same wrapper one level up (see its own "legacy comment-container
        // classes" test above), just not this nested one.
        describe('nested .comment-container wrapper (padding/background parity)', () => {
            it('wraps an existing reply list in the legacy classes, visible (no d-none)', () => {
                const root = makeComment({
                    id: 1,
                    children: { total: 1, items: [makeComment({ id: 10, parentCommentId: 1, children: null })], hasMore: false },
                });

                const wrapper = mount(CommentSection, {
                    ...mountOptions(),
                    props: { contentId: 42, initial: { comments: [root], prevCount: 0, nextCount: 0, total: 1 } },
                });

                // .nested-comments-root > .comment-container > .comment > .single-comment
                const nestedContainer = wrapper.find('.nested-comments-root > .comment-container');
                expect(nestedContainer.exists()).toBe(true);
                expect(nestedContainer.classes()).toEqual(expect.arrayContaining(['bg-light', 'p-2', 'mt-3', 'comment-container']));
                expect(nestedContainer.classes()).not.toContain('d-none');
                expect(nestedContainer.find('.comment .single-comment').exists()).toBe(true);
            });

            it('keeps the wrapper d-none (no padding/background) with no replies and the reply form closed, then reveals it once the reply form opens', async () => {
                const root = makeComment({ id: 1, children: { total: 0, items: [], hasMore: false } });

                const wrapper = mount(CommentSection, {
                    ...mountOptions(),
                    props: {
                        contentId: 42,
                        initial: { comments: [root], prevCount: 0, nextCount: 0, total: 1 },
                        canComment: true,
                        formShellHtml: buildShell(),
                    },
                });

                const nestedContainer = () => wrapper.find('.nested-comments-root > .comment-container');
                expect(nestedContainer().classes()).toContain('d-none');

                await wrapper.find('.single-comment .wall-entry-controls a').trigger('click'); // "Reply"

                expect(nestedContainer().classes()).not.toContain('d-none');
            });
        });

        // Bug: hovering a REPLY also revealed the PARENT comment's own `⋮` controls
        // dropdown (browser-verified with Playwright against the compiled hover rule -
        // jsdom can't evaluate `:hover`, hence the structural assertions below instead).
        //
        // Root cause: `:hover` matches every ANCESTOR of the hovered element, not just
        // the element itself - a nested reply's `.single-comment` is a DESCENDANT of its
        // parent's `.single-comment` (this is true of both the legacy DOM and this one -
        // restoring the `.comment-container` wrapper above does NOT change that), so
        // `.single-comment:hover > .nav-pills.preferences > .dropdown > .dropdown-toggle`
        // (_comment.scss) matched for the PARENT too whenever a reply was hovered, since
        // the parent's own `.nav-pills.preferences` (rendered by CommentControls, the
        // FIRST child of `.single-comment` - see below) is just as much a direct child of
        // a now-`:hover`-matching `.single-comment` as the reply's own is of its.
        //
        // Fix (_comment.scss): `&:hover:not(:has(.single-comment:hover)) > .nav-pills...`
        // - the `:not(:has(...))` guard keeps only the INNERMOST hovered `.single-comment`
        // "active" (replies never nest further, so one `:has()` check suffices). That CSS
        // fix depends on `.nav-pills.preferences` being a DIRECT CHILD of `.single-comment`
        // at every nesting level, which these assertions confirm structurally.
        it('renders CommentControls\' .nav-pills.preferences as a direct child of .single-comment at both nesting levels (precondition for the hover-scoping CSS fix)', () => {
            const root = makeComment({
                id: 1,
                children: { total: 1, items: [makeComment({ id: 10, parentCommentId: 1, children: null })], hasMore: false },
            });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [root], prevCount: 0, nextCount: 0, total: 1 } },
            });

            const rootComment = wrapper.find('#comment_1');
            expect(rootComment.element.querySelector(':scope > .nav-pills.preferences')).not.toBeNull();

            const replyComment = wrapper.find('#comment_10');
            expect(replyComment.element.querySelector(':scope > .nav-pills.preferences')).not.toBeNull();

            // ... and the reply IS a descendant of the root's .single-comment box (via
            // .flex-grow-1 > .nested-comments-root > .comment-container > .comment), which
            // is exactly why hovering it also matches `.single-comment:hover` for the root.
            expect(rootComment.element.contains(replyComment.element)).toBe(true);
        });
    });

    describe('blocked authors', () => {
        it('masks the entry with no message/author leak, and reveals it in place', async () => {
            const blocked = makeComment({
                id: 9,
                blocked: true,
                author: null,
                message: null,
                messageRenderOptions: null,
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

        it('fetches and renders the default window on expand when previewMax=0 shipped an empty-but-nonzero window', async () => {
            const response = { comments: [makeComment({ id: 5 }), makeComment({ id: 6 })], prevCount: 2, nextCount: 0, total: 4 };
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(response));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow({ total: 4 }), pageSize: 2, collapsed: true },
            });

            expect(wrapper.find('.single-comment').exists()).toBe(false);

            wrapper.element.parentElement.dispatchEvent(new CustomEvent('humhub:comment:toggle'));

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/comment/comment/list?contentId=42&pageSize=2');

            await vi.waitFor(() => {
                const ids = wrapper.findAll('.single-comment').map((entry) => entry.attributes('id'));
                expect(ids).toEqual(['comment_5', 'comment_6']);
            });
            expect(wrapper.vm.prevCount).toBe(2);
        });

        it('does not re-fetch on a second toggle while the expand fetch is still in flight', async () => {
            let resolveGet;
            globalThis.humhubStubs.client.get = vi.fn(() => new Promise((resolve) => { resolveGet = resolve; }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow({ total: 4 }), collapsed: true },
            });

            wrapper.element.parentElement.dispatchEvent(new CustomEvent('humhub:comment:toggle'));
            wrapper.element.parentElement.dispatchEvent(new CustomEvent('humhub:comment:toggle'));
            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);

            resolveGet({ comments: [makeComment({ id: 1 })], prevCount: 0, nextCount: 0, total: 4 });
            await vi.waitFor(() => expect(wrapper.find('.single-comment').exists()).toBe(true));
        });

        it('does not fetch on expand when the window is genuinely empty (total 0)', () => {
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow(), collapsed: true },
            });

            wrapper.element.parentElement.dispatchEvent(new CustomEvent('humhub:comment:toggle'));

            expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
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

    // Items 2-7 below: visual-parity gaps found via live browser comparison against the
    // legacy UI (see CommentEntry's own "Visual parity fixes" docblock section).
    describe('entry links order (item 2)', () => {
        it('renders Reply before Like, with a separator only when both are present', () => {
            const comment = makeComment({ likes: { count: 2, liked: false } });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 }, canComment: true },
            });

            const controls = wrapper.find('.wall-entry-controls').element;
            const replyLink = [...controls.querySelectorAll('a')].find((a) => a.textContent.startsWith('Reply'));
            const likeContainer = controls.querySelector('.likeLinkContainer');

            expect(replyLink).toBeTruthy();
            expect(likeContainer).toBeTruthy();
            // CommentEntryLinks: CommentLink sortOrder 100 before LikeLink sortOrder 200.
            // eslint-disable-next-line no-bitwise
            expect(replyLink.compareDocumentPosition(likeContainer) & Node.DOCUMENT_POSITION_FOLLOWING).toBeTruthy();
            expect(controls.textContent).toContain(' · ');
        });

        it('renders Reply with no separator when likes are unavailable', () => {
            const comment = makeComment({ likes: null });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 }, canComment: true },
            });

            expect(wrapper.find('.wall-entry-controls').text()).not.toContain('·');
        });
    });

    describe('avatar + author-link parity (item 3, popovers)', () => {
        it('carries data-contentcontainer-id/imageAlt on the avatar and data-contentcontainer-id/data-guid on the author link', () => {
            const comment = makeComment({
                author: makeAuthor({ contentContainerId: 7, guid: 'user-guid-7', imageAlt: 'Profile picture of Alice' }),
            });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            const img = wrapper.find('.comment-header-image img');
            expect(img.attributes('data-contentcontainer-id')).toBe('7');
            expect(img.attributes('alt')).toBe('Profile picture of Alice');

            const authorLink = wrapper.find('.comment-heading a');
            expect(authorLink.attributes('data-contentcontainer-id')).toBe('7');
            expect(authorLink.attributes('data-guid')).toBe('user-guid-7');
        });
    });

    describe('online-status overlay (item 4)', () => {
        it('renders the online overlay for another online user', () => {
            const comment = makeComment({ author: makeAuthor({ online: true }) });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            const overlay = wrapper.find('.comment-header-image .user-online-status');
            expect(overlay.exists()).toBe(true);
            expect(overlay.classes()).toContain('user-is-online');
            expect(wrapper.find('.comment-header-image a').classes()).toEqual(
                expect.arrayContaining(['has-online-status', 'img-size-small']),
            );
        });

        it('renders the offline overlay variant', () => {
            const comment = makeComment({ author: makeAuthor({ online: false }) });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            const overlay = wrapper.find('.comment-header-image .user-online-status');
            expect(overlay.exists()).toBe(true);
            expect(overlay.classes()).toContain('user-is-offline');
        });

        it('renders no overlay when the online status is null (disabled or own comment)', () => {
            const comment = makeComment({ author: makeAuthor({ online: null }) });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            expect(wrapper.find('.comment-header-image .user-online-status').exists()).toBe(false);
            expect(wrapper.find('.comment-header-image a').classes()).not.toContain('has-online-status');
        });
    });

    describe('data-ui-markdown + flattened RichTextOutput wrapper (items 5 and 7)', () => {
        it('renders .comment-message as the RichTextOutput root with data-ui-markdown and no intermediate div', () => {
            const comment = makeComment({ message: 'Hello world', messageRenderOptions: { 'ui-richtext': true } });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            const message = wrapper.find('.comment-message').element;
            expect(message.hasAttribute('data-ui-markdown')).toBe(true);
            expect(message.hasAttribute('data-ui-show-more')).toBe(true);
            expect(message.getAttribute('data-read-more-text')).toBe('Read full comment...');

            // Direct child, not nested one level deeper behind an extra RichTextOutput-owned div.
            expect(message.children.length).toBe(1);
            expect(message.children[0].hasAttribute('data-ui-richtext')).toBe(true);
            expect(wrapper.find('.comment-message > div > [data-ui-richtext]').exists()).toBe(false);
        });
    });

    describe('edited marker parity (item 6)', () => {
        it('shows a tooltipped edited marker with the localized update time when isEdited', () => {
            const comment = makeComment({ isEdited: true, updatedAt: '2026-08-10T12:30:00+00:00' });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            const icon = wrapper.find('.comment-heading .fa-clock-o');
            expect(icon.exists()).toBe(true);
            expect(icon.classes()).toContain('tt');
            expect(icon.attributes('title')).toBe(new Date('2026-08-10T12:30:00+00:00').toLocaleString());
        });

        it('renders no edited marker when the comment was never edited', () => {
            const comment = makeComment({ isEdited: false, updatedAt: null });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            expect(wrapper.find('.comment-heading .fa-clock-o').exists()).toBe(false);
        });
    });

    describe('extension slots', () => {
        // Registers `probeDef` on the real humhub.vue registry (so ExtensionSlot's own
        // isRegistered() filter — see its docblock — finds it) AND into this mount()'s
        // isolated test app via global.components (so `<component :is="'Name'">` actually
        // resolves inside it) — the same two-registration shape ExtensionSlot's production
        // usage collapses into one, since a real island shares a single registry for both.
        const mountWithProbe = (name, probeDef, mountProps) => {
            const options = mountOptions();
            options.global.components[name] = probeDef;
            vueModule.register(name, probeDef);

            return mount(CommentSection, { ...options, props: mountProps });
        };

        it('renders a probe component registered for comment.links, at the end of .wall-entry-controls', () => {
            const comment = makeComment();
            const probeDef = {
                props: { comment: { type: Object, required: true } },
                render() {
                    return Vue.h('a', { class: 'probe-links-item', href: '#' }, 'Probe link #' + this.comment.id);
                },
            };
            vueModule.registerSlotComponent('comment.links', 'ProbeCommentLinksItem');

            const wrapper = mountWithProbe('ProbeCommentLinksItem', probeDef, {
                contentId: 42,
                initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 },
            });

            const controls = wrapper.find('.wall-entry-controls');
            const probe = controls.find('.probe-links-item');
            expect(probe.exists()).toBe(true);
            expect(probe.text()).toBe('Probe link #' + comment.id);
            // Last child of .wall-entry-controls, i.e. after the core Reply/Like links.
            expect(controls.element.lastElementChild).toBe(probe.element);
        });

        it('exposes a comment\'s extensions data to a comment.links slot component via context.comment.extensions', () => {
            const comment = makeComment({ extensions: { reportcontent: { reported: true } } });
            const probeDef = {
                props: { comment: { type: Object, required: true } },
                render() {
                    return Vue.h(
                        'span',
                        { class: 'probe-extensions-item' },
                        this.comment.extensions.reportcontent.reported ? 'reported' : 'clean',
                    );
                },
            };
            vueModule.registerSlotComponent('comment.links', 'ProbeExtensionsLinksItem');

            const wrapper = mountWithProbe('ProbeExtensionsLinksItem', probeDef, {
                contentId: 42,
                initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 },
            });

            expect(wrapper.find('.probe-extensions-item').text()).toBe('reported');
        });
    });

    // `comment.controls` is a data-driven DropdownMenu menu (registerMenuEntry()/
    // removeMenuEntry()), not an ExtensionSlot — see CommentControls.vue's own docblock and
    // docs/develop/ui-js-vuejs-extensions.md, "Menu entries". It is also the same menuId
    // CommentControls.vue's own built-in Edit/Delete entries are keyed under, so every test
    // below shares that one production id.
    describe('menu entries (comment.controls)', () => {
        // resetMenuRegistry() is a TEST-ONLY seam (see its own docblock in humhub.vue.js) that
        // wipes every registerMenuEntry()/removeMenuEntry() call — including the otherwise
        // PERMANENT removals a test below makes (there is no "un-remove", see
        // removeMenuEntry()'s own docblock) — so each test starts from a clean registry
        // instead of depending on running order relative to the others in this block.
        beforeEach(() => {
            vueModule.resetMenuRegistry();
        });

        it('renders a registered component entry, passing the comment via context', () => {
            const comment = makeComment();
            const probeDef = {
                props: { context: { type: Object, required: true } },
                render() {
                    return Vue.h('a', { class: 'dropdown-item probe-controls-item', href: '#' }, 'Probe #' + this.context.comment.id);
                },
            };
            vueModule.register('ProbeCommentControlsComponent', probeDef);
            vueModule.registerMenuEntry('comment.controls', { id: 'probeComponentEntry', component: 'ProbeCommentControlsComponent' });

            const options = mountOptions();
            options.global.components.ProbeCommentControlsComponent = probeDef;

            const wrapper = mount(CommentSection, {
                ...options,
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            const probe = wrapper.find('.probe-controls-item');
            expect(probe.exists()).toBe(true);
            expect(probe.text()).toBe('Probe #' + comment.id);
            // Rendered inside the same <DropdownMenu> as the core edit/delete items, after them
            // (default sortOrder 1000 on both sides ties in registration/prop order — built-ins
            // first, see DropdownMenu.vue's resolution pipeline docblock).
            const items = wrapper.findAll('.dropdown-menu > li');
            expect(items[items.length - 1].find('.probe-controls-item').exists()).toBe(true);
        });

        it('positions a registered entry among the built-in edit/delete items according to sortOrder', () => {
            const comment = makeComment({ canEdit: true, canDelete: true });
            vueModule.registerMenuEntry('comment.controls', {
                id: 'probeSortOrderEntry',
                label: 'Probe Sort Order',
                sortOrder: 1, // ahead of edit/delete, which both default to sortOrder 1000
            });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            const items = wrapper.findAll('.dropdown-menu > li').map((li) => li.text());
            expect(items).toEqual(['Permalink', 'Probe Sort Order', 'Edit', 'Delete']);
        });

        it('removeMenuEntry(\'comment.controls\', \'edit\') hides the built-in Edit entry', () => {
            const comment = makeComment({ canEdit: true, canDelete: true });
            vueModule.removeMenuEntry('comment.controls', 'edit');

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            const items = wrapper.findAll('.dropdown-item').map((item) => item.text());
            expect(items).not.toContain('Edit');
            // Delete (a different id) is unaffected by removing 'edit'.
            expect(items).toContain('Delete');
        });
    });
});
