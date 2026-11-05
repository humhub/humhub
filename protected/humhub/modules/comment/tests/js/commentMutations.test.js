import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import CommentSection from '../../vue/CommentSection.vue';
import CommentForm from '../../vue/components/CommentForm.vue';
import CommentEntry from '../../vue/components/CommentEntry.vue';
import LikeButton from '../../../../modules/like/vue/LikeButton.vue';
import RichTextOutput from '../../../../vue/RichTextOutput.vue';
import DropdownMenu from '../../../../vue/DropdownMenu.vue';
import ExtensionSlot from '../../../../vue/ExtensionSlot.vue';
import UserImage from '../../../../modules/user/vue/UserImage.vue';
import AttachedFiles from '../../../../modules/file/vue/AttachedFiles.vue';
import HumHubForm from '../../../../vue/HumHubForm.vue';
import RichTextField from '../../../../vue/RichTextField.vue';
import UploadField from '../../../../vue/UploadField.vue';
import SubmitButton from '../../../../vue/SubmitButton.vue';
import UiModal from '../../../../vue/UiModal.vue';
import UserList from '../../../../modules/user/vue/UserList.vue';
import TextareaField from '../../../../vue/TextareaField.vue';
import CheckboxField from '../../../../vue/CheckboxField.vue';

await import('../../../../resources/js/humhub/humhub.url.js');
await import('../../../../resources/js/humhub/humhub.vue.js');

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
// DropdownMenu/ExtensionSlot/HumHubForm/RichTextField/SubmitButton
// directly (they now resolve through the global Vue component registry - see their own
// docblocks) - @vue/test-utils' `global.components` stands in for that registry here, the
// same way it already does for LikeButton below. UiModal/UserList are needed too - nested
// two levels down, inside LikeButton's own (always-rendered) user-list modal.
const mountOptions = () => ({
    global: {
        directives: { additions: additionsDirective },
        components: {
            LikeButton, RichTextOutput, DropdownMenu, ExtensionSlot, UserImage, AttachedFiles,
            HumHubForm, RichTextField, SubmitButton, UploadField, UiModal, UserList, TextareaField, CheckboxField,
        },
    },
});

/**
 * Attaches a fake `ui.richtext.prosemirror.RichTextEditor` instance to a DOM
 * node the same way the real widget caches itself once booted (jQuery
 * `.data('humhub-ui-richtexteditor', instance)` — see RichTextField's own
 * `getEditorInstance()`). `editor.serialize()`/`editor.init()` are wired to a shared
 * `currentValue` so getValue()/setValue() round-trip realistically.
 */
const attachFakeEditor = (node, initialValue = 'hello') => {
    const fake = { currentValue: initialValue };
    fake.$ = jQuery(node);
    fake.focus = vi.fn();
    fake.backup = vi.fn();
    fake.editor = {
        serialize: vi.fn(() => fake.currentValue),
        init: vi.fn((markdown) => {
            fake.currentValue = markdown || '';
        }),
    };
    fake.$.data('humhub-ui-richtexteditor', fake);
    return fake;
};

// API user shape (camelCase, see `user\serializers\UserSerializer::short()`).
const makeAuthor = (overrides = {}) => ({
    id: 9,
    guid: 'user-guid-1',
    displayName: 'Alice',
    url: '/user/alice',
    imageUrl: '/uploads/alice.jpg',
    contentContainerId: 5,
    ...overrides,
});

// RAW API comment shape (see `comment\serializers\CommentSerializer`).
const makeComment = (overrides = {}) => ({
    id: 1,
    message: 'Hello world',
    contentId: 42,
    parentCommentId: null,
    recordId: 100,
    createdBy: makeAuthor(),
    createdAt: '2026-08-01T10:00:00+00:00',
    updatedAt: '2026-08-01T10:00:00+00:00',
    url: '/comment/perma?id=1',
    files: [],
    childCount: 0,
    replies: { total: 0, items: [], hasMore: false },
    ...overrides,
});

// `rootTotal` defaults to whatever `total` ends up being (via `overrides`) rather than a
// fixed 0 - every existing caller uses this for a no-replies scenario, where the two are
// always equal - unless `overrides` supplies its own `rootTotal` explicitly.
const emptyWindow = (overrides = {}) => ({
    results: [],
    prevCount: 0,
    nextCount: 0,
    total: 0,
    rootTotal: overrides.total ?? 0,
    ...overrides,
});

/**
 * Opens an entry's context menu the way Bootstrap does in production (`show.bs.dropdown`
 * on the toggle, see DropdownMenu.vue) and settles the permissions request it triggers:
 * `canEdit`/`canDelete` are not part of the comment payload — which is caller-neutral and
 * therefore cacheable, see docs/develop/concept-api.md — but fetched when the menu opens.
 * The stub delegates every other URL to whatever the test had installed.
 */
const openCommentMenu = async (wrapper, permissions = { canEdit: true, canDelete: true }, index = 0) => {
    const inner = globalThis.humhubStubs.client.get;
    globalThis.humhubStubs.client.get = vi.fn((url, ...rest) => (String(url).includes('/permissions')
        ? Promise.resolve(permissions)
        : inner(url, ...rest)));

    wrapper.findAll('.dropdown-toggle')[index].element.dispatchEvent(new Event('show.bs.dropdown'));
    await flushPromises();
};

describe('Comment mutations + live updates', () => {
    beforeEach(() => {
        globalThis.humhub.modules.url.config.template = '/__route__';
        globalThis.humhub.config.module('user').isGuest = false;
        globalThis.humhub.config.module('user').loginUrl = '/user/auth/login';
        // The adapter derives canAdminDelete/blocked from these (see commentApi.js).
        globalThis.humhub.config.module('user').id = 9;
        globalThis.humhub.config.module('user').blockedUserIds = [];
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(emptyWindow()));
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({}));
        // PUT (edit save) and DELETE go through the vue bridge's put()/del() → client.ajax()
        globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ code: 200 }));
        globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(true));
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.logCalls.warn.length = 0;
        // Every mounted CommentSection subscribes to the live-update event on
        // this shared, page-lifetime bus (see its own docblock) - without
        // clearing it here, a previous test's still-mounted (vue-test-utils
        // does not auto-unmount between tests) instance would keep reacting
        // to events fired by a later test.
        globalThis.humhubStubs.event._handlers.clear();

        // UiModal teleports its dialog to document.body, and vue-test-utils never
        // auto-unmounts - a previous test's still-mounted island with an open modal
        // would otherwise leave its dialog in the body and every
        // `document.body.querySelector('.modal')` below would pick THAT one up.
        document.body.querySelectorAll('.modal, .modal-backdrop').forEach((el) => el.remove());

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
        // Submits through the button a user clicks, rather than a synthetic native
        // 'submit' event.
        it('posts message+fileList, appends at the end, clears the editor and bumps the count', async () => {
            const initial = { results: [makeComment({ id: 1 })], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 };
            let resolvePost;
            globalThis.humhubStubs.client.post = vi.fn(() => new Promise((resolve) => { resolvePost = resolve; }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial, canComment: true },
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
                '/api/v2/comment',
                { data: { contentId: 42, message: 'hello', fileList: [] } },
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
        it('renders field errors on a 422 response without clearing the editor or the list (native submit path)', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({
                status: 422,
                errors: { message: ['Message cannot be blank.'] },
            }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow(), canComment: true },
            });

            const editor = jQuery(wrapper.find(RICHTEXT_SELECTOR).element).data('humhub-ui-richtexteditor');
            const clearHandler = vi.fn();
            editor.$.on('clear', clearHandler);

            await wrapper.find('form').trigger('submit');
            await vi.waitFor(() => expect(wrapper.text()).toContain('Message cannot be blank.'));

            expect(clearHandler).not.toHaveBeenCalled();
            expect(editor.editor.serialize()).toBe('hello'); // input kept
            expect(wrapper.findAll('.single-comment').length).toBe(0);
            expect(globalThis.humhubStubs.logCalls.error.length).toBe(0); // validation errors render, never log
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
                props: { contentId: 42 },
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
        it('clears the rendered validation error as soon as the user edits the richtext content again', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({
                status: 422,
                errors: { message: ['Message cannot be blank.'] },
            }));

            const wrapper = mount(CommentForm, {
                ...mountOptions(),
                props: { contentId: 42 },
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
                props: { contentId: 42, initial: emptyWindow(), canComment: true },
            });

            await wrapper.find('.btn-comment-submit').trigger('click');
            await vi.waitFor(() => expect(globalThis.humhubStubs.logCalls.error.length).toBeGreaterThan(0));

            expect(wrapper.find('.invalid-feedback').exists()).toBe(false);
        });
    });

    // Ctrl+S in the editor submits: the richtext editor's save plugin
    // (protected/humhub/resources/js/richtext/core/plugins/save/plugin.js) does exactly
    //   context.editor.$.closest('form').find('[type="submit"]').trigger('click')
    // (`context.editor.$` is the node RICHTEXT_SELECTOR finds below), so the submit button
    // has to be a `type="submit"` inside the editor's form.
    //
    // jsdom can't load the real ProseMirror plugin bundle, so this reproduces its exact DOM
    // contract with real jQuery (the same `.trigger()` the plugin calls) instead of
    // @vue/test-utils' own `.trigger()`, which never reaches a `type="submit"` button's
    // form-submission activation behavior.
    describe('keyboard submit (Ctrl+S bridge)', () => {
        it('reaches the submit button via closest(form).find([type="submit"]).trigger("click"), same as the legacy keymap plugin', async () => {
            // A realistic resolved comment, not the default {} stub - CommentSection
            // renders whatever comes back as a fresh CommentEntry, which needs a real
            // `author` etc.; unrelated to what this test actually verifies.
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(makeComment({ id: 2 })));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: emptyWindow(), canComment: true },
            });

            const editor = jQuery(wrapper.find(RICHTEXT_SELECTOR).element).data('humhub-ui-richtexteditor');

            // Exactly the two-step lookup save/plugin.js's handleKeyDown performs on Ctrl+S.
            const $form = editor.$.closest('form');
            expect($form.length).toBe(1);
            const $submit = $form.find('[type="submit"]');
            expect($submit.length).toBe(1);
            expect($submit.hasClass('btn-comment-submit')).toBe(true);

            $submit.trigger('click');

            await vi.waitFor(() => expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith(
                '/api/v2/comment',
                { data: { contentId: 42, message: 'hello', fileList: [] } },
            ));
            // A real click on a type="submit" button would, if not cancelled,
            // ALSO fire a native 'submit' event that CommentForm listens for
            // (see mounted()) - onSubmit()'s own event.preventDefault() (run
            // for the click first) heads that off, so this fires exactly
            // once rather than double-posting.
            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledTimes(1);
        });
    });

    describe('submit button icon', () => {
        // Icon-only, as the legacy `Button::accent()->icon('send')` was, named by its aria-label.
        it('is an icon-only send button, in a reply form too', async () => {
            const comment = makeComment({ id: 1, replies: { total: 0, items: [], hasMore: false } });
            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { results: [comment], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 },
                    canComment: true,
                },
            });

            const button = wrapper.find('.btn-comment-submit');
            expect(button.classes()).toContain('btn-icon-only');
            expect(button.attributes('aria-label')).toBe('Submit');
            expect(button.find('.ti-send').exists()).toBe(true);
            expect(button.text()).toBe('');

            const replyLink = wrapper.findAll('.wall-entry-controls a').find((a) => a.text().startsWith('Reply'));
            await replyLink.trigger('click');
            await wrapper.vm.$nextTick();

            expect(wrapper.find('.nested-comments-root .btn-comment-submit .ti-send').exists()).toBe(true);
        });
    });

    describe('submit button placement', () => {
        it('renders the submit button in .richtext-create-buttons, bottom-right of the input', () => {
            const wrapper = mount(CommentForm, {
                ...mountOptions(),
                props: { contentId: 42 },
            });

            const group = wrapper.find('.richtext-create-input-group .richtext-create-buttons');
            expect(group.exists()).toBe(true);
            expect(group.find('.btn-comment-submit').exists()).toBe(true);
        });
    });

    describe('create (reply form)', () => {
        it('appends the reply under its parent and bumps both child and section totals', async () => {
            const root = makeComment({ id: 1, replies: { total: 0, items: [], hasMore: false } });
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(
                makeComment({ id: 10, parentCommentId: 1, replies: null, message: 'a reply' }),
            ));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { results: [root], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 },
                    canComment: true,
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
                '/api/v2/comment',
                { data: { contentId: 42, parentCommentId: 1, message: 'hello', fileList: [] } },
            );

            await vi.waitFor(() => expect(wrapper.find('.nested-comments-root .single-comment').exists()).toBe(true));
            expect(wrapper.vm.total).toBe(2);

            const badge = wrapper.find('.comment-count');
            expect(badge.attributes('data-count')).toBe('1');
            expect(badge.attributes('style')).toBeUndefined();
            expect(badge.text()).toBe('(1)');
        });
    });

    // Regression coverage for the "Show next 0 comments" bug (root list): an own/live-
    // appended comment used to become the pagination cursor for the NEXT "show more"
    // click, skipping over whatever real gap existed and permanently stranding it - see
    // CommentList.vue's own "Next-pagination gap fix" docblock section for the full
    // root-cause writeup this covers. The reply/children equivalent below instead covers
    // CommentEntry.vue's own "Previous-direction pagination fix" (a distinct bug: replies
    // paginate backwards from the oldest SHOWN reply, never forwards from an appended one -
    // see that docblock section for why children never needed the same next-direction
    // dedupe/splice machinery root pagination does).
    describe('replies/root pagination gap fix (Show next 0 comments)', () => {
        it('renders no show-more link after an own reply is appended to an already fully-loaded reply list', async () => {
            const replyA = makeComment({ id: 10, parentCommentId: 1, replies: null, message: 'reply a' });
            const replyB = makeComment({ id: 11, parentCommentId: 1, replies: null, message: 'reply b' });
            const root = makeComment({ id: 1, replies: { total: 2, items: [replyA, replyB], hasMore: false } });
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(
                makeComment({ id: 20, parentCommentId: 1, replies: null, message: 'own reply' }),
            ));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    // total: 3 (1 root + 2 replies), rootTotal: 1 (just the one root) -
                    // shape-realistic per CommentJsonService::serializeWindow()'s own
                    // docblock note on `total` vs. `rootTotal`.
                    initial: { results: [root], prevCount: 0, nextCount: 0, total: 3, rootTotal: 1 },
                    canComment: true,
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

        // Covers CommentEntry's own "Previous-direction pagination fix": an own-appended
        // reply lands at the TAIL of `childItems`, never touching `childFirstCursorId` (the
        // HEAD, seeded from the oldest SHOWN reply) - so a subsequent load-older still
        // cursors from the true pre-append head, and (unlike root/next pagination) the
        // response can never legitimately re-return an id already in `childItems`, since the
        // fetch is strictly for ids older than that head.
        it('own reply appended at the tail does not move the load-older cursor, produces no duplicates, and remaining reaches 0 once caught up', async () => {
            const reply15 = makeComment({ id: 15, parentCommentId: 4, replies: null, message: 'reply 15' });
            const reply16 = makeComment({ id: 16, parentCommentId: 4, replies: null, message: 'reply 16' });
            const root = makeComment({ id: 4, contentId: 2, replies: { total: 4, items: [reply15, reply16], hasMore: true } });
            const ownReply = makeComment({ id: 20, parentCommentId: 4, contentId: 2, replies: null, message: 'own reply' });
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(ownReply));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 2,
                    // total: 5 (1 root + 4 replies), rootTotal: 1 (just the one root).
                    initial: { results: [root], prevCount: 0, nextCount: 0, total: 5, rootTotal: 1 },
                    canComment: true,
                    pageSize: 10,
                },
            });

            // Preview shows 2 of 4 replies before anything is appended.
            expect(wrapper.find('.nested-comments-root .showMore').exists()).toBe(true);

            const replyLink = wrapper.findAll('.wall-entry-controls a').find((a) => a.text().startsWith('Reply'));
            await replyLink.trigger('click');
            await wrapper.vm.$nextTick();
            await wrapper.find('.nested-comments-root .btn-comment-submit').trigger('click');
            await vi.waitFor(() => expect(wrapper.findAll('.nested-comments-root .single-comment').length).toBe(3));

            // Still open right after the append - childTotal (5) still exceeds
            // childItems.length (3: 15, 16, 20).
            expect(wrapper.find('.nested-comments-root .showMore').exists()).toBe(true);

            const reply13 = makeComment({ id: 13, parentCommentId: 4, replies: null, message: 'reply 13' });
            const reply14 = makeComment({ id: 14, parentCommentId: 4, replies: null, message: 'reply 14' });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({
                results: [reply13, reply14],
                prevCount: 0,
                nextCount: 0,
                total: 5,
                rootTotal: 1,
            }));

            await wrapper.find('.nested-comments-root .showMore a').trigger('click');

            // The cursor is still 15 - the pre-append oldest SHOWN reply - never the
            // own-appended one (20), which is now the array's tail.
            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith(
                '/api/v2/comment/4/replies?cursor=15&direction=previous&limit=10',
            );

            await vi.waitFor(() => {
                const ids = wrapper.findAll('.nested-comments-root .single-comment').map((entry) => entry.attributes('id'));
                // No duplicates: the genuinely older replies (13, 14) prepend cleanly in
                // front of the already-loaded window (15, 16, 20).
                expect(ids).toEqual(['comment_13', 'comment_14', 'comment_15', 'comment_16', 'comment_20']);
            });
            expect(wrapper.find('.nested-comments-root .showMore').exists()).toBe(false);
        });

        // Covers the delete side: `onChildRemoved()` keeps `childTotal` and `childItems`
        // moving together, so the single derived `childRemaining`/`childHasMore` pair (and
        // the label reading off it) can never go stale/phantom around a delete - unlike the
        // pre-round-3 two-independently-mutated-fields shape this replaced.
        it('keeps the remaining count and label consistent (no phantom) after a loaded reply is deleted', async () => {
            const reply15 = makeComment({ id: 15, parentCommentId: 4, replies: null, message: 'reply 15' });
            const reply16 = makeComment({ id: 16, parentCommentId: 4, replies: null, message: 'reply 16' });
            // total: 3 - 2 shown (15, 16), 1 still hidden/unfetched.
            const root = makeComment({ id: 4, contentId: 2, replies: { total: 3, items: [reply15, reply16], hasMore: true } });
            globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ code: 200 }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 2,
                    initial: { results: [root], prevCount: 0, nextCount: 0, total: 4, rootTotal: 1 },
                },
            });

            const showMoreLink = () => wrapper.find('.nested-comments-root .showMore a');
            // The root CommentEntry (index 0 - see findAllComponents() order: the outer
            // entry renders before its own recursively-nested reply entries) owns the
            // childRemaining/childHasMore this parent's link is derived from.
            const rootEntry = () => wrapper.findAllComponents(CommentEntry)[0].vm;

            expect(showMoreLink().exists()).toBe(true);
            expect(rootEntry().childHasMore).toBe(true);
            expect(rootEntry().childRemaining).toBe(1);

            // Index 2 is the SECOND reply's menu (root first, then reply 15, then reply 16),
            // so the single open menu makes the Delete item unambiguous.
            await openCommentMenu(wrapper, { canEdit: true, canDelete: true }, 2);
            const deleteItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Delete');
            await deleteItem.trigger('click');
            await wrapper.vm.$nextTick();
            document.body.querySelector('.modal[role="dialog"] .btn-danger')
                .dispatchEvent(new MouseEvent('click', { bubbles: true }));

            await vi.waitFor(() => expect(wrapper.find('#comment_16').exists()).toBe(false));
            // childTotal (3 -> 2) and childItems.length (2 -> 1) moved together, so the
            // derived remaining count is unchanged - never a stale/phantom 0 or 2 - and the
            // gate/label (reading off the very same computed) stay just as consistent.
            expect(showMoreLink().exists()).toBe(true);
            expect(rootEntry().childHasMore).toBe(true);
            expect(rootEntry().childRemaining).toBe(1);
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
                    // No replies in this scenario - total and rootTotal coincide.
                    initial: { results: [commentA, commentB], prevCount: 0, nextCount: 1, total: 3, rootTotal: 3 },
                    canComment: true,
                    pageSize: 5,
                },
            });

            expect(wrapper.find('.showMore').exists()).toBe(true);

            await wrapper.find('.btn-comment-submit').trigger('click');
            await vi.waitFor(() => expect(wrapper.findAll('.single-comment').length).toBe(3));

            const hiddenComment = makeComment({ id: 8, message: 'hidden comment' });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({
                results: [hiddenComment, ownComment],
                prevCount: 0,
                nextCount: 0,
                total: 4,
                rootTotal: 4,
            }));

            await wrapper.find('.showMore a').trigger('click');

            // The cursor is the pre-append last loaded comment (6), never the
            // own-appended one (10) that is now items' tail.
            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith(
                '/api/v2/content/42/comments?cursor=6&direction=next&limit=5',
            );

            await vi.waitFor(() => {
                const ids = wrapper.findAll('.single-comment').map((entry) => entry.attributes('id'));
                expect(ids).toEqual(['comment_5', 'comment_6', 'comment_8', 'comment_10']);
            });
            expect(wrapper.find('.showMore').exists()).toBe(false);
        });
    });

    // Regression coverage for the phantom "Show next N comments" bug (N = the thread's
    // reply count) - see CommentJsonService::serializeWindow()'s and CommentList's own
    // docblocks ("Root-vs-all total") for the full root-cause writeup. This block pins the
    // mutation side: `rootTotal` must move in lockstep with a ROOT create/delete only,
    // never a reply.
    describe('phantom "show next N replies" fix (rootTotal mutations)', () => {
        it('bumps rootTotal (not just the badge total) on an own root create, without opening a phantom next link', async () => {
            const initial = {
                results: [makeComment({ id: 9 })],
                prevCount: 8,
                nextCount: 0,
                total: 23,
                rootTotal: 9,
            };
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(makeComment({ id: 30, message: 'new root' })));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial, canComment: true },
            });

            await wrapper.find('.btn-comment-submit').trigger('click');
            await vi.waitFor(() => expect(wrapper.findAll('.single-comment').length).toBe(2));

            expect(wrapper.vm.rootTotal).toBe(10); // 9 + 1, not the badge total's 24
            expect(wrapper.vm.total).toBe(24);
            // Still just the "previous" link - the own append must not open a phantom next.
            expect(wrapper.findAll('.showMore').length).toBe(1);
        });

        it('does not bump rootTotal, and does not open a phantom next link on the root list, when a reply is created', async () => {
            const root = makeComment({ id: 1, replies: { total: 0, items: [], hasMore: false } });
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(
                makeComment({ id: 40, parentCommentId: 1, replies: null, message: 'a reply' }),
            ));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    // Exactly 1 root known, nothing hidden either side - a reply create
                    // wrongly bumping rootTotal would flip remainingNext from 0 to 1,
                    // opening a phantom root-level "show next" link.
                    initial: { results: [root], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 },
                    canComment: true,
                },
            });

            const replyLink = wrapper.findAll('.wall-entry-controls a').find((a) => a.text().startsWith('Reply'));
            await replyLink.trigger('click');
            await wrapper.vm.$nextTick();
            await wrapper.find('.nested-comments-root .btn-comment-submit').trigger('click');

            await vi.waitFor(() => expect(wrapper.find('.nested-comments-root .single-comment').exists()).toBe(true));

            expect(wrapper.vm.total).toBe(2); // the badge does bump
            expect(wrapper.vm.rootTotal).toBe(1); // rootTotal does not
            expect(wrapper.find('.showMore').exists()).toBe(false); // no phantom root-level link
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
                props: { contentId: 42, initial: emptyWindow(), canComment: true },
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
            const root = makeComment({ id: 1, replies: { total: 0, items: [], hasMore: false } });
            let resolvePost;
            globalThis.humhubStubs.client.post = vi.fn(() => new Promise((resolve) => { resolvePost = resolve; }));
            const racedReply = makeComment({ id: 88, parentCommentId: 1, replies: null, message: 'raced reply' });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(racedReply));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { results: [root], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 },
                    canComment: true,
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

    describe('blocked-author reveal (root and nested, client-side)', () => {
        // Masking is derived client-side from the viewer's own block list and the
        // reveal is a local display toggle with no request (see commentApi.js's
        // `blocked` and CommentEntry's `revealed`) - covering a NESTED reply here
        // since it renders through the parent entry's recursion, a separate path
        // from the root list.
        it('reveals a blocked reply nested under a root comment without a request', async () => {
            globalThis.humhub.config.module('user').blockedUserIds = [66];
            const blockedChild = makeComment({ id: 10, parentCommentId: 1, replies: null, createdBy: makeAuthor({ id: 66 }), message: 'hidden reply' });
            const root = makeComment({ id: 1, replies: { total: 1, items: [blockedChild], hasMore: false } });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { results: [root], prevCount: 0, nextCount: 0, total: 2, rootTotal: 1 } },
            });

            expect(wrapper.find('.nested-comments-root .comment-blocked-user').exists()).toBe(true);
            expect(wrapper.text()).not.toContain('hidden reply');

            globalThis.humhubStubs.client.get.mockClear();
            await wrapper.find('.nested-comments-root .comment-blocked-user a').trigger('click');

            expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
            await vi.waitFor(() => expect(wrapper.find('.nested-comments-root .single-comment').exists()).toBe(true));
            expect(wrapper.find('.nested-comments-root .comment-blocked-user').exists()).toBe(false);
            expect(wrapper.text()).toContain('hidden reply');
        });
    });

    describe('edit', () => {
        it('fetches the raw message, prefills the booted editor, and swaps the entry (with a key bump) on save', async () => {
            const comment = makeComment({ id: 1, message: 'before' });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(makeComment({ id: 1, message: 'raw **markdown**' })));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { results: [comment], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 },
                },
            });

            const beforeEl = wrapper.find('#comment_1').element;

            await openCommentMenu(wrapper);
            const editItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Edit');
            await editItem.trigger('click');

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/comment/1');
            await vi.waitFor(() => expect(wrapper.find('#comment_editarea_1 form').exists()).toBe(true));

            const editor = jQuery(wrapper.find('#comment_editarea_1 ' + RICHTEXT_SELECTOR).element)
                .data('humhub-ui-richtexteditor');
            expect(editor.editor.init).toHaveBeenCalledWith('raw **markdown**');

            // #5: the edit form is the same CommentForm component, with its own
            // submit button.
            const editSubmit = wrapper.find('#comment_editarea_1 .btn-comment-submit');
            expect(editSubmit.exists()).toBe(true);

            globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve(
                makeComment({ id: 1, message: 'after' }),
            ));

            await editSubmit.trigger('click');

            expect(globalThis.humhubStubs.client.ajax).toHaveBeenCalledWith(
                '/api/v2/comment/1',
                expect.objectContaining({
                    method: 'PATCH',
                    data: { message: 'raw **markdown**', fileList: [] },
                }),
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
            const comment = makeComment({ id: 1, message: 'original' });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(makeComment({ id: 1, message: 'raw markdown' })));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { results: [comment], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 },
                },
            });

            await openCommentMenu(wrapper);
            const editItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Edit');
            await editItem.trigger('click');
            await vi.waitFor(() => expect(wrapper.find('#comment_editarea_1 form').exists()).toBe(true));

            await wrapper.find('.comment-cancel-edit-link').trigger('click');

            expect(wrapper.find('#comment_editarea_1 form').exists()).toBe(false);
            expect(wrapper.text()).toContain('original');
            expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();
        });
    });

    // A discarded form only loses its DOM - the editor's sessionStorage draft outlives the
    // unmount and would resurface the text later. The editor's 'clear' DOM event is what the
    // real widget's own handler runs resetBackup() on (see humhub.ui.richtext.prosemirror.js).
    describe('discarding a form drops its draft', () => {
        const watchClear = (wrapper, scope) => {
            const handler = vi.fn();
            jQuery(wrapper.find(`${scope} ${RICHTEXT_SELECTOR}`).element).on('clear', handler);
            return handler;
        };

        it('clears the edit form\'s editor before it unmounts on cancel', async () => {
            const comment = makeComment({ id: 1 });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(makeComment({ id: 1, message: 'raw markdown' })));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { results: [comment], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 },
                },
            });

            await openCommentMenu(wrapper);
            const editItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Edit');
            await editItem.trigger('click');
            await vi.waitFor(() => expect(wrapper.find('#comment_editarea_1 form').exists()).toBe(true));
            const cleared = watchClear(wrapper, '#comment_editarea_1');

            await wrapper.find('.comment-cancel-edit-link').trigger('click');

            expect(cleared).toHaveBeenCalledTimes(1);
        });

        it('clears the edit form\'s editor on save', async () => {
            const comment = makeComment({ id: 1 });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(makeComment({ id: 1, message: 'raw markdown' })));
            globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve(
                makeComment({ id: 1, message: 'after' }),
            ));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { results: [comment], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 },
                },
            });

            await openCommentMenu(wrapper);
            const editItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Edit');
            await editItem.trigger('click');
            await vi.waitFor(() => expect(wrapper.find('#comment_editarea_1 form').exists()).toBe(true));
            const cleared = watchClear(wrapper, '#comment_editarea_1');

            await wrapper.find('#comment_editarea_1 .btn-comment-submit').trigger('click');
            await vi.waitFor(() => expect(wrapper.find('#comment_editarea_1 form').exists()).toBe(false));

            expect(cleared).toHaveBeenCalledTimes(1);
        });

        it('clears the reply form\'s editor before it unmounts on close', async () => {
            const comment = makeComment({ id: 1, replies: { total: 0, items: [], hasMore: false } });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { results: [comment], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 },
                    canComment: true,
                },
            });

            const replyLink = wrapper.findAll('.wall-entry-controls a').find((a) => a.text().startsWith('Reply'));
            await replyLink.trigger('click');
            await wrapper.vm.$nextTick();
            const cleared = watchClear(wrapper, '.nested-comments-root');

            await replyLink.trigger('click'); // toggles the reply form closed again

            expect(cleared).toHaveBeenCalledTimes(1);
        });
    });

    // The editor's sessionStorage draft is keyed by its input's id (see RichTextField's "Draft
    // backup" docblock section): CommentForm derives the key from its mount context (see its
    // `formInstanceKey`), stable across page loads for the same logical form and distinct
    // between the main create form, each reply form and each edit form.
    describe('stable per-form instance ids (richtext draft backup key contract)', () => {
        it('derives main/reply/edit form ids from contentId/parent/comment id, not the page counter', async () => {
            const comment = makeComment({ id: 7 });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(makeComment({ id: 7, message: 'raw' })));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: {
                    contentId: 42,
                    initial: { results: [comment], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 },
                    canComment: true,
                },
            });

            // Main create form: keyed by content id alone.
            expect(wrapper.find('#richtext-c42').exists()).toBe(true);

            // Reply form: additionally keyed by the parent comment id.
            const replyLink = wrapper.findAll('.wall-entry-controls a').find((a) => a.text().startsWith('Reply'));
            await replyLink.trigger('click');
            await wrapper.vm.$nextTick();
            expect(wrapper.find('#richtext-c42-r7').exists()).toBe(true);

            // Edit form: keyed by the edited comment's own id.
            await openCommentMenu(wrapper);
            const editItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Edit');
            await editItem.trigger('click');
            await vi.waitFor(() => expect(wrapper.find('#richtext-c42-e7').exists()).toBe(true));
        });
    });

    describe('delete', () => {
        it('removes the entry and subtracts 1 + its current reply count from the total on confirm', async () => {
            const comment = makeComment({ id: 1, replies: { total: 2, items: [], hasMore: false } });
            globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ code: 200 }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                // total: 3 (1 root + 2 replies), rootTotal: 1 (just the one root).
                props: { contentId: 42, initial: { results: [comment], prevCount: 0, nextCount: 0, total: 3, rootTotal: 1 } },
            });

            await openCommentMenu(wrapper);
            const deleteItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Delete');
            await deleteItem.trigger('click');
            await wrapper.vm.$nextTick();

            // The native CommentDeleteModal (UiModal, Teleported to body) in plain mode —
            // no legacy modal.confirm bridge involved anymore.
            const dialog = document.body.querySelector('.modal[role="dialog"]');
            expect(dialog).not.toBeNull();
            expect(dialog.textContent).toContain('Do you really want to delete this comment?');
            expect(dialog.querySelector('textarea')).toBeNull(); // no admin fields in plain mode

            dialog.querySelector('.btn-danger').dispatchEvent(new MouseEvent('click', { bubbles: true }));

            await vi.waitFor(() => expect(globalThis.humhubStubs.client.ajax).toHaveBeenCalledWith(
                '/api/v2/comment/1',
                expect.objectContaining({ method: 'DELETE' }),
            ));

            await vi.waitFor(() => expect(wrapper.find('#comment_1').exists()).toBe(false));
            expect(wrapper.vm.total).toBe(0); // 3 - (1 + 2 children)
            // A ROOT delete adjusts rootTotal by -1 (never -(1 + childTotal), unlike the
            // badge `total` above) - see CommentSection's own docblock, "Root-only remaining
            // count".
            expect(wrapper.vm.rootTotal).toBe(0);

            wrapper.unmount();
        });

        it('decrements the parent reply badge (and total) when a child reply is deleted', async () => {
            const child = makeComment({ id: 10, parentCommentId: 1, replies: null });
            const root = makeComment({ id: 1, replies: { total: 1, items: [child], hasMore: false } });
            globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ code: 200 }));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                // total: 2 (1 root + 1 reply), rootTotal: 1 (just the one root).
                props: {
                    contentId: 42,
                    canComment: true,
                    initial: { results: [root], prevCount: 0, nextCount: 0, total: 2, rootTotal: 1 },
                },
            });

            expect(wrapper.find('.comment-count').attributes('data-count')).toBe('1');

            // Index 1 is the CHILD's menu (the root renders its own controls first), so the
            // single open menu makes the Delete item unambiguous.
            await openCommentMenu(wrapper, { canEdit: true, canDelete: true }, 1);
            const deleteItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Delete');
            await deleteItem.trigger('click');
            await wrapper.vm.$nextTick();
            document.body.querySelector('.modal[role="dialog"] .btn-danger')
                .dispatchEvent(new MouseEvent('click', { bubbles: true }));

            await vi.waitFor(() => expect(wrapper.find('#comment_10').exists()).toBe(false));
            expect(wrapper.vm.total).toBe(1); // 2 - 1 (a reply can't have its own children)
            // A reply delete never touches rootTotal - only ROOT deletes do.
            expect(wrapper.vm.rootTotal).toBe(1);

            const badge = wrapper.find('.comment-count');
            expect(badge.attributes('data-count')).toBe('0');
            expect(badge.attributes('style')).toBe('display: none;');

            wrapper.unmount();
        });

        it('leaves the entry and total untouched when the confirm is declined', async () => {
            const comment = makeComment({ id: 1 });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { results: [comment], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 } },
            });

            await openCommentMenu(wrapper);
            const deleteItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Delete');
            await deleteItem.trigger('click');
            await wrapper.vm.$nextTick();

            const dialog = document.body.querySelector('.modal[role="dialog"]');
            expect(dialog).not.toBeNull();
            dialog.querySelector('.btn-light').dispatchEvent(new MouseEvent('click', { bubbles: true }));
            await wrapper.vm.$nextTick();

            expect(document.body.querySelector('.modal[role="dialog"]')).toBeNull(); // closed again
            expect(globalThis.humhubStubs.client.ajax).not.toHaveBeenCalled();
            expect(wrapper.find('#comment_1').exists()).toBe(true);
            expect(wrapper.vm.total).toBe(1);

            wrapper.unmount();
        });
    });

    describe('admin-delete (native modal)', () => {
        it('opens the reason/notify mode, gates Confirm on the reason, and deletes with the moderation fields', async () => {
            // canAdminDelete is DERIVED: canDelete on someone ELSE's comment (author 66 ≠ caller 9).
            const comment = makeComment({ id: 1, createdBy: makeAuthor({ id: 66 }) });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { results: [comment], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 } },
            });

            await openCommentMenu(wrapper);
            const deleteItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Delete');
            await deleteItem.trigger('click');
            await wrapper.vm.$nextTick();

            // Native CommentDeleteModal in admin mode: no server round trip for the DIALOG
            // anymore (the get-admin-delete-modal route is gone), the legacy
            // AdminDeleteCommentForm fields are a reason textarea + notify checkbox. The one
            // GET this flow does make is the menu's own permissions call - see
            // openCommentMenu() and docs/develop/concept-api.md.
            expect(globalThis.humhubStubs.client.get.mock.calls.map(([url]) => url))
                .toEqual(['/api/v2/comment/1/permissions']);
            const dialog = document.body.querySelector('.modal[role="dialog"]');
            expect(dialog).not.toBeNull();
            expect(dialog.querySelector('.modal-title').innerHTML).toContain('<strong>Delete</strong> comment?');

            const textarea = dialog.querySelector('textarea');
            const checkbox = dialog.querySelector('input[type="checkbox"]');
            const confirmButton = dialog.querySelector('.btn-danger');
            expect(textarea).not.toBeNull();
            // notify defaults to checked (legacy parity), reason enabled and empty →
            // Confirm is gated until a reason is entered.
            expect(checkbox.checked).toBe(true);
            expect(textarea.disabled).toBe(false);
            expect(confirmButton.disabled).toBe(true);

            textarea.value = 'Against the rules';
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            await wrapper.vm.$nextTick();
            expect(confirmButton.disabled).toBe(false);

            confirmButton.dispatchEvent(new MouseEvent('click', { bubbles: true }));

            // The moderation fields travel in the query string - a DELETE carries no body
            await vi.waitFor(() => expect(globalThis.humhubStubs.client.ajax).toHaveBeenCalledWith(
                // (`+` or `%20` for the space, depending on the param serializer at hand)
                expect.stringMatching(/^\/api\/v2\/comment\/1\?notify=1&message=Against(\+|%20)the(\+|%20)rules$/),
                expect.objectContaining({ method: 'DELETE' }),
            ));

            await vi.waitFor(() => expect(wrapper.find('#comment_1').exists()).toBe(false));
            expect(wrapper.vm.total).toBe(0);

            wrapper.unmount();
        });

        it('unchecking notify disables the reason field and deletes without moderation fields', async () => {
            const comment = makeComment({ id: 1, createdBy: makeAuthor({ id: 66 }) });

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { results: [comment], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 } },
            });

            await openCommentMenu(wrapper);
            const deleteItem = wrapper.findAll('.dropdown-item').find((item) => item.text() === 'Delete');
            await deleteItem.trigger('click');
            await wrapper.vm.$nextTick();

            const dialog = document.body.querySelector('.modal[role="dialog"]');
            const checkbox = dialog.querySelector('input[type="checkbox"]');
            checkbox.checked = false;
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            await wrapper.vm.$nextTick();

            expect(dialog.querySelector('textarea').disabled).toBe(true);
            const confirmButton = dialog.querySelector('.btn-danger');
            expect(confirmButton.disabled).toBe(false); // no reason required without notify

            confirmButton.dispatchEvent(new MouseEvent('click', { bubbles: true }));

            // A plain DELETE — no notify/message payload at all.
            await vi.waitFor(() => expect(globalThis.humhubStubs.client.ajax).toHaveBeenCalledWith(
                '/api/v2/comment/1',
                expect.not.objectContaining({ data: expect.anything() }),
            ));

            wrapper.unmount();
        });
    });

    describe('live updates', () => {
        it('appends a matching root-level event and bumps the count', async () => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(
                makeComment({ id: 5, parentCommentId: null, message: 'live comment' }),
            ));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { results: [makeComment({ id: 1 })], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 } },
            });

            vueModule.events.trigger(LIVE_NEW_COMMENT, [
                [{ type: 'humhub.modules.comment.live.NewComment', data: { commentId: 5, contentId: 42 } }],
                {},
            ]);

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/comment/5');
            await vi.waitFor(() => expect(wrapper.find('#comment_5').exists()).toBe(true));
            expect(wrapper.vm.total).toBe(2);
        });

        it('appends a matching reply event under its already-loaded parent', async () => {
            const root = makeComment({ id: 1, replies: { total: 0, items: [], hasMore: false } });
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(
                makeComment({ id: 20, parentCommentId: 1, replies: null, message: 'live reply' }),
            ));

            const wrapper = mount(CommentSection, {
                ...mountOptions(),
                props: { contentId: 42, initial: { results: [root], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 } },
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
                props: { contentId: 42, initial: { results: [makeComment({ id: 1 })], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 } },
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
                props: { contentId: 42, initial: { results: [makeComment({ id: 1 })], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 } },
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
                props: { contentId: 42, initial: { results: [makeComment({ id: 1 })], prevCount: 0, nextCount: 0, total: 1, rootTotal: 1 } },
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
