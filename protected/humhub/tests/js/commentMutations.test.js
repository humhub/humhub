import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import CommentSection from '../../modules/comment/vue/CommentSection.vue';
import CommentForm from '../../modules/comment/vue/components/CommentForm.vue';
import LikeButton from '../../modules/like/vue/LikeButton.vue';
import RichTextOutput from '../../vue/RichTextOutput.vue';
import LegacyFormWrapper from '../../vue/LegacyFormWrapper.vue';
import DropdownMenu from '../../vue/DropdownMenu.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

const LIVE_NEW_COMMENT = 'humhub:modules:comment:live:NewComment';
const RICHTEXT_SELECTOR = '[data-ui-widget="ui.richtext.prosemirror.RichTextEditor"]';

// v-additions is registered per Vue *app* by humhub.vue.js's island mounter,
// not globally on the Vue runtime - mirrors commentSection.test.js's stand-in
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
// LegacyFormWrapper/DropdownMenu directly (they now resolve through the global Vue
// component registry - see their own docblocks) - @vue/test-utils' `global.components`
// stands in for that registry here, the same way it already does for LikeButton below.
const mountOptions = () => ({
    global: {
        directives: { additions: additionsDirective },
        components: { LikeButton, RichTextOutput, LegacyFormWrapper, DropdownMenu },
    },
});

// Synthetic __VUEFORM__ shell (see LegacyFormWrapper's own docblock and
// coreInterop.test.js): a <form> to attach the native submit interceptor
// to, a `.humhub-ui-richtext` node the auto-boot handler below attaches a
// fake editor to (exactly like a real richtext widget boot would), and a
// `.richtext-create-buttons` container - the REAL production shell's button
// group (see commentFormShell.php), which is where CommentForm's own
// Vue-rendered submit button now Teleports itself into (see its "Submit
// button placement" docblock section) - this is the PRIMARY, tested path.
//
// Deliberately has NO `.btn-comment-submit` button of its own (matching the
// REAL production shell, see CommentForm's "Submit button" docblock section)
// - only CommentForm's own Vue-rendered button carries that class, so tests
// can select it unambiguously.
const buildShell = () => `
    <div id="comment_create_form___VUEFORM__" class="comment_create content_create">
        <form id="w___VUEFORM__" action="/comment/comment/post" method="post">
            <div data-ui-widget="ui.richtext.prosemirror.RichTextEditor" class="humhub-ui-richtext" id="newCommentForm___VUEFORM__"></div>
            <div class="richtext-create-buttons"></div>
        </form>
    </div>
`;

/**
 * Attaches a fake `ui.richtext.prosemirror.RichTextEditor` instance to a DOM
 * node the same way the real widget caches itself once booted (jQuery
 * `.data('humhub-ui-richtexteditor', instance)` — see LegacyFormWrapper's own
 * docblock). `editor.serialize()`/`editor.init()` are wired to a shared
 * `currentValue` so getValue()/setValue() round-trip realistically.
 */
const attachFakeEditor = (node, initialValue = 'hello') => {
    const fake = { currentValue: initialValue };
    fake.$ = jQuery(node);
    fake.focus = vi.fn();
    fake.editor = {
        serialize: vi.fn(() => fake.currentValue),
        init: vi.fn((markdown) => {
            fake.currentValue = markdown || '';
        }),
    };
    fake.$.data('humhub-ui-richtexteditor', fake);
    return fake;
};

const makeAuthor = (overrides = {}) => ({
    guid: 'user-guid-1',
    displayName: 'Alice',
    url: '/user/alice',
    imageUrl: '/uploads/alice.jpg',
    contentContainerId: 5,
    imageAlt: 'Profile picture of Alice',
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

describe('Comment mutations + live updates', () => {
    beforeEach(() => {
        globalThis.humhub.modules.url.config.template = '/__route__';
        globalThis.humhub.config.module('user').isGuest = false;
        globalThis.humhub.config.module('user').loginUrl = '/user/auth/login';
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(emptyWindow()));
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({}));
        globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(true));
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.logCalls.warn.length = 0;
        // Every mounted CommentSection subscribes to the live-update event on
        // this shared, page-lifetime bus (see its own docblock) - without
        // clearing it here, a previous test's still-mounted (vue-test-utils
        // does not auto-unmount between tests) instance would keep reacting
        // to events fired by a later test.
        globalThis.humhubStubs.event._handlers.clear();

        // Auto-boots a fake richtext editor on every `.humhub-ui-richtext`
        // node handed to ui.additions - mirrors the real widget's boot
        // timing (synchronous, inside v-additions' `mounted`/`updated` hook,
        // i.e. strictly before a *parent* CommentForm's own `mounted()` runs)
        // closely enough to exercise CommentForm's setValue-after-boot logic
        // for real instead of needing to fake the timing separately.
        globalThis.humhubStubs.additions.register('test-richtext-autoboot', RICHTEXT_SELECTOR, ($match) => {
            $match.each(function () {
                if (!jQuery(this).data('humhub-ui-richtexteditor')) {
                    attachFakeEditor(this);
                }
            });
        });
    });

    describe('create (main form)', () => {
        // Regression guard for the P2-7 finding: the __VUEFORM__ shell has no
        // submit button of its own (see CommentForm's own "Submit button"
        // docblock section) — the ONLY way a real user can submit is the
        // Vue-owned button rendered alongside it. Clicking that button here,
        // rather than dispatching a synthetic native 'submit' event, is what
        // would have caught this gap.
        it('posts message+fileList, appends at the end, clears the editor and bumps the count', async () => {
            const initial = { comments: [makeComment({ id: 1 })], prevCount: 0, nextCount: 0, total: 1 };
            let resolvePost;
            globalThis.humhubStubs.client.post = vi.fn(() => new Promise((resolve) => { resolvePost = resolve; }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial, canComment: true, formShellHtml: buildShell() },
            });

            const countHandler = vi.fn();
            wrapper.element.parentElement.addEventListener('humhub:comment:countChanged', countHandler);

            const editor = jQuery(wrapper.find(RICHTEXT_SELECTOR).element).data('humhub-ui-richtexteditor');
            const clearHandler = vi.fn();
            editor.$.on('clear', clearHandler);

            const submitButton = wrapper.find('.btn-comment-submit');
            expect(submitButton.exists()).toBe(true);
            expect(submitButton.attributes('disabled')).toBeUndefined();

            await submitButton.trigger('click');

            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith(
                '/comment/comment/create?contentId=42',
                { data: { message: 'hello', fileList: [] } },
            );

            // Busy guard: the button is disabled while the request is in
            // flight, and a second click is a no-op regardless.
            expect(wrapper.find('.btn-comment-submit').attributes('disabled')).toBeDefined();
            await wrapper.find('.btn-comment-submit').trigger('click');
            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledTimes(1);

            resolvePost(makeComment({ id: 2, messageOutput: '<div>new comment</div>' }));
            await vi.waitFor(() => expect(wrapper.findAll('.single-comment').length).toBe(2));

            const ids = wrapper.findAll('.single-comment').map((entry) => entry.attributes('id'));
            expect(ids).toEqual(['comment_1', 'comment_2']); // appended at the end

            expect(clearHandler).toHaveBeenCalledTimes(1);
            expect(countHandler).toHaveBeenCalledTimes(1);
            expect(countHandler.mock.calls[0][0].detail).toEqual({ contentId: 42, total: 2 });

            // Busy guard released — the button is re-enabled and a further click goes through.
            expect(wrapper.find('.btn-comment-submit').attributes('disabled')).toBeUndefined();
            globalThis.humhubStubs.client.post.mockClear();
            await wrapper.find('.btn-comment-submit').trigger('click');
            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledTimes(1);
        });

        // The ONE retained synthetic-submit test: the native 'submit'
        // listener (see CommentForm's mounted()) stays wired for a
        // programmatic/synthetic submit() call, even though nothing in the
        // rendered shell can trigger one through user interaction anymore.
        it('renders field errors on 422 without clearing the editor or the list (native submit path)', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({
                status: 422,
                errors: { message: ['Message cannot be blank.'] },
            }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow(), canComment: true, formShellHtml: buildShell() },
            });

            const editor = jQuery(wrapper.find(RICHTEXT_SELECTOR).element).data('humhub-ui-richtexteditor');
            const clearHandler = vi.fn();
            editor.$.on('clear', clearHandler);

            await wrapper.find('form').trigger('submit');
            await vi.waitFor(() => expect(wrapper.text()).toContain('Message cannot be blank.'));

            expect(clearHandler).not.toHaveBeenCalled();
            expect(editor.editor.serialize()).toBe('hello'); // input kept
            expect(wrapper.findAll('.single-comment').length).toBe(0);
            expect(globalThis.humhubStubs.logCalls.error.length).toBe(0); // 422 is rendered, not logged
        });

        it('logs via status instead of rendering field errors for a Yii framework error response (403/404 shape)', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({
                status: 403,
                name: 'Forbidden',
                message: 'You are not allowed to comment.',
            }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow(), canComment: true, formShellHtml: buildShell() },
            });

            await wrapper.find('.btn-comment-submit').trigger('click');
            await vi.waitFor(() => expect(globalThis.humhubStubs.logCalls.error.length).toBeGreaterThan(0));

            expect(wrapper.find('.invalid-feedback').exists()).toBe(false);
        });
    });

    describe('submit button icon parity (F1)', () => {
        // The legacy button was icon-only (Button::accent()->icon('send'), no visible
        // label) - see Comments.php's `submitIconHtml` prop and CommentForm.vue's own
        // "Submit button" docblock section for the exact evidence trail.
        const ICON_HTML = '<i class="fa fa-send" aria-hidden="true"></i>';

        it('renders the server-provided icon html, aria-label and btn-icon-only when submitIconHtml is given', () => {
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: emptyWindow(),
                    canComment: true,
                    formShellHtml: buildShell(),
                    submitIconHtml: ICON_HTML,
                },
            });

            const button = wrapper.find('.btn-comment-submit');
            expect(button.exists()).toBe(true);
            expect(button.classes()).toContain('btn-icon-only');
            expect(button.attributes('aria-label')).toBe('Submit');
            expect(button.find('.fa-send').exists()).toBe(true);
            expect(button.text()).toBe(''); // icon-only - no visible text label
        });

        it('falls back to a visible text label (no btn-icon-only) when submitIconHtml is not provided', () => {
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow(), canComment: true, formShellHtml: buildShell() },
            });

            const button = wrapper.find('.btn-comment-submit');
            expect(button.classes()).not.toContain('btn-icon-only');
            expect(button.attributes('aria-label')).toBe('Submit');
            expect(button.text()).toBe('Submit');
        });

        it('threads submitIconHtml down to a reply form too', async () => {
            const comment = makeComment({ id: 1, children: { total: 0, items: [], hasMore: false } });
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 },
                    canComment: true,
                    formShellHtml: buildShell(),
                    submitIconHtml: ICON_HTML,
                },
            });

            const replyLink = wrapper.findAll('.wall-entry-controls a').find((a) => a.text().startsWith('Reply'));
            await replyLink.trigger('click');
            await wrapper.vm.$nextTick();

            expect(wrapper.find('.nested-comments-root .btn-comment-submit .fa-send').exists()).toBe(true);
        });
    });

    describe('submit button placement (item 1 - Teleport into the shell button group)', () => {
        // Primary path: the real production shell (commentFormShell.php) always carries
        // `.richtext-create-buttons` (the upload dropdown's button group) - buildShell()
        // above now includes it, so this is the shell every other test in this file already
        // mounts CommentForm/CommentSection with.
        it('teleports the submit button into the shell\'s .richtext-create-buttons container', async () => {
            const wrapper = mount(CommentForm, {
                ...mountOptions(),
                props: { shellHtml: buildShell(), contentId: 42 },
            });

            // teleportTarget is only resolved in mounted() (the ref isn't populated any
            // earlier - see CommentForm's own "Submit button placement" docblock section),
            // so the Teleport itself only picks it up on the reactive update this triggers -
            // one tick after the initial (fallback, in-place) render.
            await wrapper.vm.$nextTick();

            const group = wrapper.find('.richtext-create-buttons');
            expect(group.exists()).toBe(true);
            expect(group.find('.btn-comment-submit').exists()).toBe(true);
        });

        // Fallback path: a shell without the container (e.g. a caller that hasn't wired up
        // the full production markup) must still get a working, visible button - Teleport's
        // own `disabled` prop renders it in place instead of warning/vanishing.
        it('falls back to rendering the button in place when the shell has no button-group container', () => {
            const shellWithoutButtonGroup = `
                <div id="comment_create_form___VUEFORM__" class="comment_create content_create">
                    <form id="w___VUEFORM__" action="/comment/comment/post" method="post">
                        <div data-ui-widget="ui.richtext.prosemirror.RichTextEditor" class="humhub-ui-richtext" id="newCommentForm___VUEFORM__"></div>
                    </form>
                </div>
            `;

            const wrapper = mount(CommentForm, {
                ...mountOptions(),
                props: { shellHtml: shellWithoutButtonGroup, contentId: 42 },
            });

            expect(wrapper.find('.richtext-create-buttons').exists()).toBe(false);
            expect(wrapper.find('.btn-comment-submit').exists()).toBe(true);
        });
    });

    describe('create (reply form)', () => {
        it('appends the reply under its parent and bumps both child and section totals', async () => {
            const root = makeComment({ id: 1, children: { total: 0, items: [], hasMore: false } });
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(
                makeComment({ id: 10, parentCommentId: 1, children: null, messageOutput: '<div>a reply</div>' }),
            ));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { comments: [root], prevCount: 0, nextCount: 0, total: 1 },
                    canComment: true,
                    formShellHtml: buildShell(),
                },
            });

            const replyLink = wrapper.findAll('.wall-entry-controls a').find((a) => a.text().startsWith('Reply'));
            // Mirrors legacy link.php's `.comment-count[data-count]` badge,
            // hidden (not omitted) via inline style while zero, for theme
            // CSS parity - see CommentEntry's own template comment.
            expect(replyLink.find('.comment-count').attributes('data-count')).toBe('0');
            expect(replyLink.find('.comment-count').attributes('style')).toBe('display: none;');

            await replyLink.trigger('click');
            await wrapper.vm.$nextTick();

            // The reply form is the SAME CommentForm component as the main
            // form - it renders its own submit button too (#5: verify both
            // reply and edit modes get one, not just the root create form).
            const replySubmit = wrapper.find('.nested-comments-root .btn-comment-submit');
            expect(replySubmit.exists()).toBe(true);
            await replySubmit.trigger('click');

            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith(
                '/comment/comment/create?contentId=42&parentCommentId=1',
                { data: { message: 'hello', fileList: [] } },
            );

            await vi.waitFor(() => expect(wrapper.find('.nested-comments-root .single-comment').exists()).toBe(true));
            expect(wrapper.vm.total).toBe(2);

            const badge = wrapper.find('.comment-count');
            expect(badge.attributes('data-count')).toBe('1');
            expect(badge.attributes('style')).toBeUndefined();
            expect(badge.text()).toBe('(1)');
        });
    });

    describe('own-create vs live race (I1)', () => {
        it('does not duplicate an entry when a live event and its own slow create response resolve for the same id', async () => {
            let resolvePost;
            globalThis.humhubStubs.client.post = vi.fn(() => new Promise((resolve) => { resolvePost = resolve; }));
            const racedComment = makeComment({ id: 77, parentCommentId: null, messageOutput: '<div>raced comment</div>' });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(racedComment));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow(), canComment: true, formShellHtml: buildShell() },
            });

            await wrapper.find('.btn-comment-submit').trigger('click'); // create POST now in flight, unresolved

            // The live poller delivers (and this component fetches+appends)
            // the same comment before the slow create POST above resolves.
            vueModule.events.trigger(LIVE_NEW_COMMENT, [
                [{ type: 'humhub.modules.comment.live.NewComment', data: { commentId: 77, contentId: 42 } }],
                {},
            ]);
            await vi.waitFor(() => expect(wrapper.find('#comment_77').exists()).toBe(true));
            expect(wrapper.vm.total).toBe(1);

            // The create POST itself now resolves with the very same comment
            // — onMainCreated() must recognize it as already-known and skip
            // appending/counting it a second time.
            resolvePost(racedComment);

            await vi.waitFor(() => expect(wrapper.findAll('.single-comment').length).toBe(1));
            expect(wrapper.vm.total).toBe(1);
        });

        it('does not duplicate a reply when a live event and its own slow reply-create response resolve for the same id', async () => {
            const root = makeComment({ id: 1, children: { total: 0, items: [], hasMore: false } });
            let resolvePost;
            globalThis.humhubStubs.client.post = vi.fn(() => new Promise((resolve) => { resolvePost = resolve; }));
            const racedReply = makeComment({ id: 88, parentCommentId: 1, children: null, messageOutput: '<div>raced reply</div>' });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(racedReply));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { comments: [root], prevCount: 0, nextCount: 0, total: 1 },
                    canComment: true,
                    formShellHtml: buildShell(),
                },
            });

            const replyLink = wrapper.findAll('.wall-entry-controls a').find((a) => a.text().startsWith('Reply'));
            await replyLink.trigger('click');
            await wrapper.vm.$nextTick();
            await wrapper.find('.nested-comments-root .btn-comment-submit').trigger('click'); // reply POST now in flight

            vueModule.events.trigger(LIVE_NEW_COMMENT, [
                [{ type: 'humhub.modules.comment.live.NewComment', data: { commentId: 88, contentId: 42 } }],
                {},
            ]);
            await vi.waitFor(() => expect(wrapper.find('#comment_88').exists()).toBe(true));
            expect(wrapper.vm.total).toBe(2);

            resolvePost(racedReply);

            await vi.waitFor(() => expect(wrapper.findAll('.nested-comments-root .single-comment').length).toBe(1));
            expect(wrapper.vm.total).toBe(2);
        });
    });

    describe('blocked-author reveal retrofit (root and nested)', () => {
        // P2-4 review note: reveal used to swap in a local `revealed` object
        // without a key bump. This task retrofits it onto the same
        // entry-object-swap + revision-bump mechanism edit/live-append use
        // (see CommentEntry's own docblock) - covering a NESTED reply here
        // too, since the parent CommentEntry's `onChildUpdated` handler is a
        // separate code path from CommentList's `onEntryUpdated`.
        it('reveals a blocked reply nested under a root comment, remounting just that entry', async () => {
            const blockedChild = makeComment({ id: 10, parentCommentId: 1, children: null, blocked: true, author: null, messageOutput: null, likes: null });
            const root = makeComment({ id: 1, children: { total: 1, items: [blockedChild], hasMore: false } });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [root], prevCount: 0, nextCount: 0, total: 2 } },
            });

            expect(wrapper.find('.nested-comments-root .comment-blocked-user').exists()).toBe(true);

            const revealed = makeComment({ id: 10, parentCommentId: 1, children: null, messageOutput: '<div>revealed reply</div>' });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(revealed));

            await wrapper.find('.nested-comments-root .comment-blocked-user a').trigger('click');

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/comment/comment/info?id=10&showBlocked=1');
            await vi.waitFor(() => expect(wrapper.find('.nested-comments-root .single-comment').exists()).toBe(true));
            expect(wrapper.find('.nested-comments-root .comment-blocked-user').exists()).toBe(false);
            expect(wrapper.text()).toContain('revealed reply');
        });
    });

    describe('edit', () => {
        it('fetches the raw message, prefills the booted editor, and swaps the entry (with a key bump) on save', async () => {
            const comment = makeComment({ id: 1, canEdit: true, messageOutput: '<div>before</div>' });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ message: 'raw **markdown**' }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 },
                    formShellHtml: buildShell(),
                },
            });

            const beforeEl = wrapper.find('#comment_1').element;

            const editItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Edit');
            await editItem.trigger('click');

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/comment/comment/update?id=1');
            await vi.waitFor(() => expect(wrapper.find('#comment_editarea_1 form').exists()).toBe(true));

            const editor = jQuery(wrapper.find('#comment_editarea_1 ' + RICHTEXT_SELECTOR).element)
                .data('humhub-ui-richtexteditor');
            expect(editor.editor.init).toHaveBeenCalledWith('raw **markdown**');

            // #5: the edit form is the same CommentForm component and also
            // gets its own submit button (the shell it hosts has none).
            const editSubmit = wrapper.find('#comment_editarea_1 .btn-comment-submit');
            expect(editSubmit.exists()).toBe(true);

            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(
                makeComment({ id: 1, canEdit: true, messageOutput: '<div>after</div>' }),
            ));

            await editSubmit.trigger('click');

            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith(
                '/comment/comment/update?id=1',
                { data: { message: 'raw **markdown**', fileList: [] } },
            );

            await vi.waitFor(() => expect(wrapper.text()).toContain('after'));
            expect(wrapper.find('#comment_editarea_1 form').exists()).toBe(false); // back to read view
            expect(wrapper.text()).not.toContain('before');

            // The entry object was swapped under a bumped :key, so Vue fully
            // remounted the component instead of patching it in place - the
            // DOM node identity itself changed.
            const afterEl = wrapper.find('#comment_1').element;
            expect(afterEl).not.toBe(beforeEl);
        });

        it('discards the fetched message and leaves the entry untouched on cancel', async () => {
            const comment = makeComment({ id: 1, canEdit: true, messageOutput: '<div>original</div>' });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ message: 'raw markdown' }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 },
                    formShellHtml: buildShell(),
                },
            });

            const editItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Edit');
            await editItem.trigger('click');
            await vi.waitFor(() => expect(wrapper.find('#comment_editarea_1 form').exists()).toBe(true));

            await wrapper.find('.comment-cancel-edit-link').trigger('click');

            expect(wrapper.find('#comment_editarea_1 form').exists()).toBe(false);
            expect(wrapper.text()).toContain('original');
            expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();
        });
    });

    describe('discarding a form resets its unsaved-changes guard (F2)', () => {
        it('resets the edit form\'s acknowledgeForm baseline before it unmounts on cancel', async () => {
            const comment = makeComment({ id: 1, canEdit: true });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ message: 'raw markdown' }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 },
                    formShellHtml: buildShell(),
                },
            });

            const editItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Edit');
            await editItem.trigger('click');
            await vi.waitFor(() => expect(wrapper.find('#comment_editarea_1 form').exists()).toBe(true));

            const formEl = wrapper.find('#comment_editarea_1 form').element;
            // Simulates onBeforeLoad()'s own baseline capture at boot time.
            jQuery(formEl).data('state', 'message=typed+but+discarded');

            await wrapper.find('.comment-cancel-edit-link').trigger('click');

            // The node is gone from the wrapper's DOM by now (Vue unmounted it) -
            // jQuery's data cache is keyed by the node object itself, still held
            // here, so it stays inspectable regardless.
            expect(jQuery(formEl).data('state')).toBeNull();
        });

        it('resets the reply form\'s acknowledgeForm baseline before it unmounts on close', async () => {
            const comment = makeComment({ id: 1, children: { total: 0, items: [], hasMore: false } });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 },
                    canComment: true,
                    formShellHtml: buildShell(),
                },
            });

            const replyLink = wrapper.findAll('.wall-entry-controls a').find((a) => a.text().startsWith('Reply'));
            await replyLink.trigger('click');
            await wrapper.vm.$nextTick();

            const formEl = wrapper.find('.nested-comments-root form').element;
            jQuery(formEl).data('state', 'message=typed+but+discarded');

            await replyLink.trigger('click'); // toggles the reply form closed again

            expect(jQuery(formEl).data('state')).toBeNull();
        });
    });

    describe('delete', () => {
        it('removes the entry and subtracts 1 + its current reply count from the total on confirm', async () => {
            const comment = makeComment({ id: 1, canDelete: true, children: { total: 2, items: [], hasMore: false } });
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ success: true }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 3 } },
            });

            const deleteItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Delete');
            await deleteItem.trigger('click');

            expect(globalThis.humhubStubs.modal.confirm).toHaveBeenCalledWith({
                header: '<strong>Confirm</strong> comment deleting',
                body: 'Do you really want to delete this comment?',
                confirmText: 'Delete',
                cancelText: 'Cancel',
            });

            await vi.waitFor(() => expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith(
                '/comment/comment/delete?id=1',
                undefined,
            ));

            await vi.waitFor(() => expect(wrapper.find('#comment_1').exists()).toBe(false));
            expect(wrapper.vm.total).toBe(0); // 3 - (1 + 2 children)
        });

        it('decrements the parent reply badge (and total) when a child reply is deleted', async () => {
            const child = makeComment({ id: 10, parentCommentId: 1, children: null, canDelete: true });
            const root = makeComment({ id: 1, canDelete: false, children: { total: 1, items: [child], hasMore: false } });
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ success: true }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, canComment: true, initial: { comments: [root], prevCount: 0, nextCount: 0, total: 2 } },
            });

            expect(wrapper.find('.comment-count').attributes('data-count')).toBe('1');

            // Only the child has canDelete: true, so this is unambiguous.
            const deleteItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Delete');
            await deleteItem.trigger('click');

            await vi.waitFor(() => expect(wrapper.find('#comment_10').exists()).toBe(false));
            expect(wrapper.vm.total).toBe(1); // 2 - 1 (a reply can't have its own children)

            const badge = wrapper.find('.comment-count');
            expect(badge.attributes('data-count')).toBe('0');
            expect(badge.attributes('style')).toBe('display: none;');
        });

        it('leaves the entry and total untouched when the confirm is declined', async () => {
            globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(false));
            const comment = makeComment({ id: 1, canDelete: true });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            const deleteItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Delete');
            await deleteItem.trigger('click');
            await wrapper.vm.$nextTick();

            expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();
            expect(wrapper.find('#comment_1').exists()).toBe(true);
            expect(wrapper.vm.total).toBe(1);
        });
    });

    describe('admin-delete', () => {
        let $fixture;

        beforeEach(() => {
            $fixture = jQuery(
                '<div id="globalModalConfirm"><form>'
                + '<textarea name="message">Reason text</textarea>'
                + '<input type="checkbox" name="notify" value="1" checked>'
                + '</form></div>',
            ).appendTo(document.body);
        });

        afterEach(() => {
            $fixture.remove();
        });

        it('fetches the modal body, confirms via the modal bridge, then posts the delete with the form fields read from it', async () => {
            const comment = makeComment({ id: 1, canDelete: true, canAdminDelete: true });
            const modalResponse = {
                header: '<strong>Delete</strong> comment?',
                body: '<form>...</form>',
                confirmText: 'Confirm',
                cancelText: 'Cancel',
            };
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(modalResponse));
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ success: true }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 } },
            });

            const deleteItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Delete');
            await deleteItem.trigger('click');

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/comment/comment/get-admin-delete-modal?id=1');
            await vi.waitFor(() => expect(globalThis.humhubStubs.modal.confirm).toHaveBeenCalledWith(modalResponse));

            await vi.waitFor(() => expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith(
                '/comment/comment/delete?id=1',
                { data: { message: 'Reason text', notify: '1' } },
            ));

            await vi.waitFor(() => expect(wrapper.find('#comment_1').exists()).toBe(false));
            expect(wrapper.vm.total).toBe(0);
        });
    });

    describe('live updates', () => {
        it('appends a matching root-level event and bumps the count', async () => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(
                makeComment({ id: 5, parentCommentId: null, messageOutput: '<div>live comment</div>' }),
            ));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [makeComment({ id: 1 })], prevCount: 0, nextCount: 0, total: 1 } },
            });

            vueModule.events.trigger(LIVE_NEW_COMMENT, [
                [{ type: 'humhub.modules.comment.live.NewComment', data: { commentId: 5, contentId: 42 } }],
                {},
            ]);

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/comment/comment/info?id=5');
            await vi.waitFor(() => expect(wrapper.find('#comment_5').exists()).toBe(true));
            expect(wrapper.vm.total).toBe(2);
        });

        it('appends a matching reply event under its already-loaded parent', async () => {
            const root = makeComment({ id: 1, children: { total: 0, items: [], hasMore: false } });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(
                makeComment({ id: 20, parentCommentId: 1, children: null, messageOutput: '<div>live reply</div>' }),
            ));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [root], prevCount: 0, nextCount: 0, total: 1 } },
            });

            vueModule.events.trigger(LIVE_NEW_COMMENT, [
                [{ type: 'humhub.modules.comment.live.NewComment', data: { commentId: 20, contentId: 42 } }],
                {},
            ]);

            await vi.waitFor(() => expect(wrapper.find('.nested-comments-root #comment_20').exists()).toBe(true));
            expect(wrapper.vm.total).toBe(2);
        });

        it('ignores an event for a comment id that is already known (covers own just-created posts)', async () => {
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [makeComment({ id: 1 })], prevCount: 0, nextCount: 0, total: 1 } },
            });
            globalThis.humhubStubs.client.get.mockClear();

            vueModule.events.trigger(LIVE_NEW_COMMENT, [
                [{ type: 'humhub.modules.comment.live.NewComment', data: { commentId: 1, contentId: 42 } }],
                {},
            ]);
            await wrapper.vm.$nextTick();

            expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
            expect(wrapper.vm.total).toBe(1);
        });

        it('ignores an event for a foreign contentId', async () => {
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [makeComment({ id: 1 })], prevCount: 0, nextCount: 0, total: 1 } },
            });
            globalThis.humhubStubs.client.get.mockClear();

            vueModule.events.trigger(LIVE_NEW_COMMENT, [
                [{ type: 'humhub.modules.comment.live.NewComment', data: { commentId: 99, contentId: 999 } }],
                {},
            ]);
            await wrapper.vm.$nextTick();

            expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
            expect(wrapper.vm.total).toBe(1);
        });

        it('unsubscribes on unmount', async () => {
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [makeComment({ id: 1 })], prevCount: 0, nextCount: 0, total: 1 } },
            });

            wrapper.unmount();
            globalThis.humhubStubs.client.get.mockClear();

            expect(() => vueModule.events.trigger(LIVE_NEW_COMMENT, [
                [{ type: 'humhub.modules.comment.live.NewComment', data: { commentId: 5, contentId: 42 } }],
                {},
            ])).not.toThrow();

            expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
        });
    });
});
