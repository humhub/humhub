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

            resolvePost(makeComment({ id: 2, message: 'new comment' }));
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
        // programmatic/synthetic submit() call (e.g. autofill, a browser
        // extension, or - see the "keyboard submit" describe block below -
        // the legacy richtext editor's own Ctrl+S handler clicking the
        // button directly, which a real click.preventDefault() keeps from
        // ALSO firing this event).
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

        // CommentForm is now built on the HumHubForm suite (see its own docblock's "Built
        // on HumHubForm" section) - the test above already proves the rendered TEXT still
        // shows up, which the pre-migration hand-rolled `errors` object would have produced
        // too. This one inspects the HumHubForm ref's own `errors` state directly, proving
        // the NEW plumbing (`this.$refs.form.setErrors(response)`) is what actually drives
        // it, not a leftover local field.
        it('routes a 422 message error through HumHubForm.setErrors, inspectable on the form ref itself', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({
                status: 422,
                errors: { message: ['Message cannot be blank.'] },
            }));

            const wrapper = mount(CommentForm, {
                ...mountOptions(),
                props: { shellHtml: buildShell(), contentId: 42 },
            });

            await wrapper.find('.btn-comment-submit').trigger('click');
            await vi.waitFor(() => expect(wrapper.vm.$refs.form.errors).toEqual({ message: ['Message cannot be blank.'] }));

            expect(wrapper.find('.invalid-feedback').text()).toBe('Message cannot be blank.');
        });

        // Clear-on-input parity with the native fields (browser-verified gap):
        // the legacy ProseMirror editor never routes its input through a Vue
        // binding, so without RichTextField's own `input` bridge (see its
        // mounted()) a rendered 422 message stuck around even while the user
        // was already fixing the value. Typing into the real editor's
        // contenteditable emits native, bubbling `input` events — dispatching
        // one from the editor node here exercises the exact event path the
        // bridge listens on (a delegated listener on the wrapper's root).
        it('clears the rendered 422 error as soon as the user edits the richtext content again', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({
                status: 422,
                errors: { message: ['Message cannot be blank.'] },
            }));

            const wrapper = mount(CommentForm, {
                ...mountOptions(),
                props: { shellHtml: buildShell(), contentId: 42 },
            });

            await wrapper.find('.btn-comment-submit').trigger('click');
            await vi.waitFor(() => expect(wrapper.find('.invalid-feedback').exists()).toBe(true));

            wrapper.find(RICHTEXT_SELECTOR).element.dispatchEvent(new Event('input', { bubbles: true }));
            await wrapper.vm.$nextTick();

            expect(wrapper.find('.invalid-feedback').exists()).toBe(false);
            expect(wrapper.vm.$refs.form.errors).toEqual({});
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

    // Bug: Ctrl+S (STRG+S) in the richtext editor stopped submitting the
    // comment form after the Vue island port. The legacy keyboard chain
    // lives in the vendored `humhub-prosemirror-richtext` package
    // (src/editor/core/plugins/save/plugin.js, bundled into
    // dist/humhub-editor.js) - its `handleKeyDown` intercepts a bare Ctrl+S
    // (`event.ctrlKey && event.key === 's'`), then does exactly:
    //   context.editor.$.closest('form').find('[type="submit"]').trigger('click')
    // (`context.editor.$` is the richtext widget's own root element, the
    // same node RICHTEXT_SELECTOR finds below). The Vue-owned submit button
    // used to be `type="button"` (see CommentForm's "Submit button"
    // docblock section, P2-7) so that `[type="submit"]` lookup came up
    // empty and the shortcut silently did nothing - even though, since
    // CommentForm.vue's own "Submit button placement" docblock section
    // (P2-7), the button had ALREADY started Teleporting into
    // `.richtext-create-buttons`, which is INSIDE the shell's `<form>`
    // (see commentFormShell.php) - `closest('form')` would have found it
    // fine, only the type attribute was wrong.
    //
    // jsdom can't load the real ProseMirror plugin bundle, so this
    // reproduces its exact DOM contract with real jQuery (the same library
    // and the same `.trigger()` the plugin itself calls) instead of
    // @vue/test-utils' own `.trigger()` (a plain jsdom `dispatchEvent()`
    // that, unlike jQuery's, never falls back to the element's native
    // `.click()` method and so never reaches a `type="submit"` button's
    // form-submission activation behavior at all here).
    describe('keyboard submit (Ctrl+S bridge)', () => {
        it('reaches the submit button via closest(form).find([type="submit"]).trigger("click"), same as the legacy keymap plugin', async () => {
            // A realistic resolved comment, not the default {} stub - CommentSection
            // renders whatever comes back as a fresh CommentEntry, which needs a real
            // `author` etc.; unrelated to what this test actually verifies.
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(makeComment({ id: 2 })));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow(), canComment: true, formShellHtml: buildShell() },
            });

            // The button Teleports into .richtext-create-buttons (INSIDE the shell's
            // <form>) only from the tick after mount (see CommentForm's own "Submit
            // button placement" docblock section / the dedicated Teleport-target test
            // above) - before that it renders disabled/in-place, still findable by
            // class but not yet a descendant of <form>.
            await wrapper.vm.$nextTick();

            const editor = jQuery(wrapper.find(RICHTEXT_SELECTOR).element).data('humhub-ui-richtexteditor');

            // Exactly the two-step lookup save/plugin.js's handleKeyDown performs on Ctrl+S.
            const $form = editor.$.closest('form');
            expect($form.length).toBe(1);
            const $submit = $form.find('[type="submit"]');
            expect($submit.length).toBe(1);
            expect($submit.hasClass('btn-comment-submit')).toBe(true);

            $submit.trigger('click');

            await vi.waitFor(() => expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith(
                '/comment/comment/create?contentId=42',
                { data: { message: 'hello', fileList: [] } },
            ));
            // A real click on a type="submit" button would, if not cancelled,
            // ALSO fire a native 'submit' event that CommentForm listens for
            // (see mounted()) - onSubmit()'s own event.preventDefault() (run
            // for the click first) heads that off, so this fires exactly
            // once rather than double-posting.
            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledTimes(1);
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
                makeComment({ id: 10, parentCommentId: 1, children: null, message: 'a reply' }),
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

    // Regression coverage for the "Show next 0 comments" bug: an own/live-appended
    // comment used to become the pagination cursor for the NEXT "show more" click,
    // skipping over whatever real gap existed and permanently stranding it - see
    // CommentList.vue's and CommentEntry.vue's own "Next-pagination gap fix" docblock
    // sections for the full root-cause writeup this covers.
    describe('replies/root pagination gap fix (Show next 0 comments)', () => {
        it('renders no show-more link after an own reply is appended to an already fully-loaded reply list', async () => {
            const replyA = makeComment({ id: 10, parentCommentId: 1, children: null, message: 'reply a' });
            const replyB = makeComment({ id: 11, parentCommentId: 1, children: null, message: 'reply b' });
            const root = makeComment({ id: 1, children: { total: 2, items: [replyA, replyB], hasMore: false } });
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(
                makeComment({ id: 20, parentCommentId: 1, children: null, message: 'own reply' }),
            ));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { comments: [root], prevCount: 0, nextCount: 0, total: 3 },
                    canComment: true,
                    formShellHtml: buildShell(),
                },
            });

            expect(wrapper.find('.nested-comments-root .showMore').exists()).toBe(false);

            const replyLink = wrapper.findAll('.wall-entry-controls a').find((a) => a.text().startsWith('Reply'));
            await replyLink.trigger('click');
            await wrapper.vm.$nextTick();
            await wrapper.find('.nested-comments-root .btn-comment-submit').trigger('click');

            await vi.waitFor(() => expect(wrapper.findAll('.nested-comments-root .single-comment').length).toBe(3));
            // The gate (`childHasMore`) and the label it would carry are now the SAME
            // derived value - an append can never leave one stale while the other updates.
            expect(wrapper.find('.nested-comments-root .showMore').exists()).toBe(false);
        });

        it('inserts a previously-hidden reply before an own-appended reply, dedupes the appended one, and clears the link once caught up (children)', async () => {
            const replyA = makeComment({ id: 10, parentCommentId: 1, children: null, message: 'reply a' });
            const replyB = makeComment({ id: 11, parentCommentId: 1, children: null, message: 'reply b' });
            const root = makeComment({ id: 1, children: { total: 3, items: [replyA, replyB], hasMore: true } });
            const ownReply = makeComment({ id: 20, parentCommentId: 1, children: null, message: 'own reply' });
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(ownReply));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { comments: [root], prevCount: 0, nextCount: 0, total: 4 },
                    canComment: true,
                    formShellHtml: buildShell(),
                    pageSize: 5,
                },
            });

            // Preview shows 2 of 3 replies before anything is appended.
            expect(wrapper.find('.nested-comments-root .showMore').exists()).toBe(true);

            const replyLink = wrapper.findAll('.wall-entry-controls a').find((a) => a.text().startsWith('Reply'));
            await replyLink.trigger('click');
            await wrapper.vm.$nextTick();
            await wrapper.find('.nested-comments-root .btn-comment-submit').trigger('click');
            await vi.waitFor(() => expect(wrapper.findAll('.nested-comments-root .single-comment').length).toBe(3));

            // Still open (never a stale-zero gate) right after the append - the
            // pre-existing hidden reply hasn't been fetched yet, so the derived count
            // (`childTotal - childItems.length`, still 1) is unchanged.
            expect(wrapper.find('.nested-comments-root .showMore').exists()).toBe(true);

            const hiddenReply = makeComment({ id: 15, parentCommentId: 1, children: null, message: 'hidden reply' });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({
                // Real "next" pagination from the pre-append cursor (11) legitimately
                // re-returns the already-known own reply (20) too, alongside the
                // genuinely new, previously-hidden one (15).
                comments: [hiddenReply, ownReply],
                prevCount: 0,
                nextCount: 0,
                total: 4,
            }));

            await wrapper.find('.nested-comments-root .showMore a').trigger('click');

            // The cursor is the pre-append last loaded reply (11), never the
            // own-appended one (20) that is now the array's tail.
            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith(
                '/comment/comment/list?contentId=42&parentCommentId=1&commentId=11&direction=next&pageSize=5',
            );

            await vi.waitFor(() => {
                const ids = wrapper.findAll('.nested-comments-root .single-comment').map((entry) => entry.attributes('id'));
                // The previously-hidden reply (15) lands BEFORE the own-appended one
                // (20), and the duplicate (20) from the response was deduped, not
                // inserted a second time.
                expect(ids).toEqual(['comment_10', 'comment_11', 'comment_15', 'comment_20']);
            });
            expect(wrapper.find('.nested-comments-root .showMore').exists()).toBe(false);
        });

        it('inserts a previously-hidden root comment before an own-appended one, dedupes the appended one, and clears the link once caught up (root list)', async () => {
            const commentA = makeComment({ id: 5, message: 'comment a' });
            const commentB = makeComment({ id: 6, message: 'comment b' });
            const ownComment = makeComment({ id: 10, message: 'own comment' });
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(ownComment));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { comments: [commentA, commentB], prevCount: 0, nextCount: 1, total: 3 },
                    canComment: true,
                    formShellHtml: buildShell(),
                    pageSize: 5,
                },
            });

            expect(wrapper.find('.showMore').exists()).toBe(true);

            await wrapper.find('.btn-comment-submit').trigger('click');
            await vi.waitFor(() => expect(wrapper.findAll('.single-comment').length).toBe(3));

            const hiddenComment = makeComment({ id: 8, message: 'hidden comment' });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({
                comments: [hiddenComment, ownComment],
                prevCount: 0,
                nextCount: 0,
                total: 4,
            }));

            await wrapper.find('.showMore a').trigger('click');

            // The cursor is the pre-append last loaded comment (6), never the
            // own-appended one (10) that is now items' tail.
            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith(
                '/comment/comment/list?contentId=42&commentId=6&direction=next&pageSize=5',
            );

            await vi.waitFor(() => {
                const ids = wrapper.findAll('.single-comment').map((entry) => entry.attributes('id'));
                expect(ids).toEqual(['comment_5', 'comment_6', 'comment_8', 'comment_10']);
            });
            expect(wrapper.find('.showMore').exists()).toBe(false);
        });
    });

    describe('own-create vs live race (I1)', () => {
        it('does not duplicate an entry when a live event and its own slow create response resolve for the same id', async () => {
            let resolvePost;
            globalThis.humhubStubs.client.post = vi.fn(() => new Promise((resolve) => { resolvePost = resolve; }));
            const racedComment = makeComment({ id: 77, parentCommentId: null, message: 'raced comment' });
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
            const racedReply = makeComment({ id: 88, parentCommentId: 1, children: null, message: 'raced reply' });
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
            const blockedChild = makeComment({ id: 10, parentCommentId: 1, children: null, blocked: true, author: null, message: null, messageRenderOptions: null, likes: null });
            const root = makeComment({ id: 1, children: { total: 1, items: [blockedChild], hasMore: false } });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { comments: [root], prevCount: 0, nextCount: 0, total: 2 } },
            });

            expect(wrapper.find('.nested-comments-root .comment-blocked-user').exists()).toBe(true);

            const revealed = makeComment({ id: 10, parentCommentId: 1, children: null, message: 'revealed reply' });
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
            const comment = makeComment({ id: 1, canEdit: true, message: 'before' });
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
                makeComment({ id: 1, canEdit: true, message: 'after' }),
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
            const comment = makeComment({ id: 1, canEdit: true, message: 'original' });
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

            // `#comment_editarea_1 form` now also matches HumHubForm's own outer <form>
            // (CommentForm is built on the suite - see its own docblock's "Built on
            // HumHubForm" section) - resolving via the richtext editor node's
            // closest('form') unambiguously reaches the INNER, legacy-shell-rendered
            // form `resetAcknowledge()` actually operates on (see RichTextField.vue's/
            // LegacyFormWrapper.vue's own docblocks), same as the production code and
            // the "keyboard submit" test above already do.
            const formEl = wrapper.find(`#comment_editarea_1 ${RICHTEXT_SELECTOR}`).element.closest('form');
            // Simulates onBeforeLoad()'s own baseline capture at boot time.
            jQuery(formEl).data('state', 'message=typed+but+discarded');

            await wrapper.find('.comment-cancel-edit-link').trigger('click');

            // The node is gone from the wrapper's DOM by now (Vue unmounted it) -
            // jQuery's data cache is keyed by the node object itself, still held
            // here, so it stays inspectable regardless.
            expect(jQuery(formEl).data('state')).toBeNull();
        });

        // The save twin of the cancel test above (browser-verified gap): the
        // edit form is discarded on success too, but the discard only removes
        // DOM — the acknowledgeForm baseline and the richtext editor's
        // sessionStorage draft backup both outlive the unmount, resurfacing
        // the just-saved text as a phantom draft/"unsaved changes" confirm
        // later. CommentForm therefore clear()s on edit success as well: the
        // editor 'clear' DOM event is what the real widget's own handler runs
        // resetBackup() on (see humhub.ui.richtext.prosemirror.js).
        it('resets the edit form\'s acknowledgeForm baseline and triggers the editor backup clear on save', async () => {
            const comment = makeComment({ id: 1, canEdit: true });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ message: 'raw markdown' }));
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(
                makeComment({ id: 1, canEdit: true, message: 'after' }),
            ));

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

            const editorNode = wrapper.find(`#comment_editarea_1 ${RICHTEXT_SELECTOR}`).element;
            const clearHandler = vi.fn();
            jQuery(editorNode).on('clear', clearHandler);
            const formEl = editorNode.closest('form');
            jQuery(formEl).data('state', 'message=typed+then+saved');

            await wrapper.find('#comment_editarea_1 .btn-comment-submit').trigger('click');
            await vi.waitFor(() => expect(wrapper.find('#comment_editarea_1 form').exists()).toBe(false));

            expect(clearHandler).toHaveBeenCalledTimes(1);
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

            // See the edit-form test above for why this resolves via the richtext
            // editor node's closest('form') rather than a plain 'form' selector.
            const formEl = wrapper.find(`.nested-comments-root ${RICHTEXT_SELECTOR}`).element.closest('form');
            jQuery(formEl).data('state', 'message=typed+but+discarded');

            await replyLink.trigger('click'); // toggles the reply form closed again

            expect(jQuery(formEl).data('state')).toBeNull();
        });
    });

    // Browser-verified bug: LegacyFormWrapper's per-page-load counter fallback
    // produced `vueform-1` on EVERY page, so the richtext editor's
    // sessionStorage draft backup (keyed by the hidden input's id, see
    // humhub.ui.richtext.prosemirror.js) collided across pages — drafts of
    // unrelated contents merged into one entry and armed phantom
    // "unsaved changes" confirms. CommentForm now derives a key from its mount
    // context instead (see its `formInstanceKey` computed): stable across page
    // loads for the same logical form, and distinct between the main create
    // form, each reply form and each edit form.
    describe('stable per-form instance ids (richtext draft backup key contract)', () => {
        it('derives main/reply/edit form ids from contentId/parent/comment id, not the page counter', async () => {
            const comment = makeComment({ id: 7, canEdit: true });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ message: 'raw' }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { comments: [comment], prevCount: 0, nextCount: 0, total: 1 },
                    canComment: true,
                    formShellHtml: buildShell(),
                },
            });

            // Main create form: keyed by content id alone.
            expect(wrapper.find('#newCommentForm_vueform-c42').exists()).toBe(true);

            // Reply form: additionally keyed by the parent comment id.
            const replyLink = wrapper.findAll('.wall-entry-controls a').find((a) => a.text().startsWith('Reply'));
            await replyLink.trigger('click');
            await wrapper.vm.$nextTick();
            expect(wrapper.find('#newCommentForm_vueform-c42-r7').exists()).toBe(true);

            // Edit form: keyed by the edited comment's own id.
            const editItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Edit');
            await editItem.trigger('click');
            await vi.waitFor(() => expect(wrapper.find('#newCommentForm_vueform-c42-e7').exists()).toBe(true));
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
                makeComment({ id: 5, parentCommentId: null, message: 'live comment' }),
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
                makeComment({ id: 20, parentCommentId: 1, children: null, message: 'live reply' }),
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
