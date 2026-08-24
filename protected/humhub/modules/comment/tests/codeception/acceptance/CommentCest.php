<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace comment\acceptance;

use comment\AcceptanceTester;
use Exception;

/**
 * Exercises the comment Vue island end-to-end (see `comment\vue\CommentSection.vue`) - the
 * comment list itself is no longer server-rendered HTML, only the initial JSON window and the
 * reusable form shell travel with the page (see `comment\widgets\Comments`/`CommentFormShell`).
 *
 * Selectors below are browser-verified against the island's rendered DOM rather than the
 * removed server-rendered `comment.php`/`form.php` markup:
 *  - the form has NO native submit button anymore - `CommentForm.vue` renders its own
 *    Vue-owned `button.btn-comment-submit` outside the shell's `<form>` (see that component's
 *    own docblock);
 *  - the editable richtext root is `.ProseMirror[contenteditable="true"]` inside
 *    `[data-ui-widget="ui.richtext.prosemirror.RichTextEditor"]` (the `.humhub-ui-richtext`
 *    class also lands on that same contenteditable node - either selector reaches it - but the
 *    widget instance itself is cached on the `[data-ui-widget]` root, not on that class; see
 *    the `e6e0362228` fix and `LegacyFormWrapper.vue`'s own docblock);
 *  - a comment entry's root is `#comment_<id>` (`.single-comment`), not the legacy
 *    `#comment-message-<id>` - that id no longer exists, the message itself is an
 *    unidentified `.comment-message` child of the entry root;
 *  - validation errors render into `.invalid-feedback` inside the form, not a
 *    server-re-rendered form partial;
 *  - a reply/"Comment" count badge is `.comment-count[data-count]`, mirroring the legacy
 *    `CommentLink`/`link.php` markup (`CommentLink` itself stays server-rendered PHP - only
 *    the list became an island, see the plan's non-goal on islandizing it);
 *  - delete goes through the island's own `CommentDeleteModal` (a native `UiModal`
 *    teleported into `document.body`, so `.modal.show` - see `CommentEntry.vue`'s
 *    `onDelete()`), not the legacy `#globalModalConfirm` dialog `stream\StreamCest` uses;
 *    the entry's `.preferences` menu toggle stays `visibility: hidden` until its comment
 *    is hovered (`_comment.scss`).
 *
 * Every test method starts from the same clean fixture state: this suite enables
 * `DynamicFixtureHelper` (see `comment/tests/codeception/acceptance.suite.yml`), which
 * unloads and reloads the default fixtures before each test - reloading `content` takes
 * the comments of the previous test down with it. Test methods therefore never build on
 * each other's comments; the ones that need an existing comment create their own through
 * {@see createComment()}, which also means no test may assume a fixed comment id (the
 * `comment` table has no fixture of its own, so its auto increment keeps climbing).
 */
class CommentCest
{
    private const POST_SELECTOR = '.wall_humhubmodulespostmodelsPost_13';
    private const COMMENT_SECTION = self::POST_SELECTOR . ' .comment-container';
    private const RICHTEXT = self::COMMENT_SECTION . ' .ProseMirror[contenteditable="true"]';

    /**
     * Mirrors the legacy scenario: empty-submit validation, then a successful create.
     *
     * @param AcceptanceTester $I
     * @throws Exception
     */
    public function testCreateComment(AcceptanceTester $I)
    {
        $I->amUser1();
        $I->amOnSpace2();
        $I->waitForText('Admin Space 2 Post Private');

        $this->openCommentForm($I);

        $I->click('.btn-comment-submit', self::COMMENT_SECTION);
        // `#comment-message-error` (the `Comment[message]` field id plus `-error`, see
        // `vue/form/fieldMixin.js`) rather than a bare `.invalid-feedback`: the legacy form
        // shell renders its own - always empty - feedback element before it, and Codeception
        // resolves a wait selector to the FIRST match only, which would never carry the text.
        $I->waitForText('The comment must not be empty!', 10, self::COMMENT_SECTION . ' #comment-message-error');

        $I->fillField(self::RICHTEXT, 'Test comment');
        $I->click('.btn-comment-submit', self::COMMENT_SECTION);

        $comment = $this->grabNewComment($I);
        $I->see('Test comment', $comment . ' .comment-message');
        $I->dontSee('The comment must not be empty!', self::COMMENT_SECTION);
    }

    /**
     * Replies to the comment created above and checks the parent's "Reply (n)" badge.
     *
     * @param AcceptanceTester $I
     * @throws Exception
     */
    public function testReplyToComment(AcceptanceTester $I)
    {
        $I->amUser1();
        $I->amOnSpace2();
        $I->waitForText('Admin Space 2 Post Private');

        $comment = $this->createComment($I, 'Test comment');

        $I->click('Reply', $comment);

        $replyRichtext = $comment . ' .nested-comments-root .ProseMirror[contenteditable="true"]';
        $I->waitForElementVisible($replyRichtext, 10);
        $I->fillField($replyRichtext, 'Test reply');
        $I->click($comment . ' .nested-comments-root .btn-comment-submit');

        $I->waitForElementVisible($comment . ' .nested-comments-root .single-comment', 10);
        $I->see('Test reply', $comment . ' .nested-comments-root .single-comment .comment-message');
        // The parent's own "Reply (n)" badge (see link.php's legacy .comment-count markup,
        // mirrored by CommentEntry.vue) reflects the new reply immediately.
        $I->see('(1)', $comment . ' .comment-count');
    }

    /**
     * Deletes the comment created above via the shared confirm modal.
     *
     * @param AcceptanceTester $I
     * @throws Exception
     */
    public function testDeleteComment(AcceptanceTester $I)
    {
        $I->amUser1();
        $I->amOnSpace2();
        $I->waitForText('Admin Space 2 Post Private');

        $comment = $this->createComment($I, 'Test comment');

        // The entry's menu toggle is `visibility: hidden` until its comment is hovered
        // (see `_comment.scss`), so a plain click would not be interactable.
        $I->moveMouseOver($comment);
        $I->click('.preferences .dropdown-toggle', $comment);
        // `Edit`/`Delete` only appear once the entry's permissions have been fetched
        // (`GET comment/<id>/permissions`, see `CommentEntry.vue`'s `loadPermissions()`).
        $I->waitForText('Delete', 10, $comment);
        $I->see('Permalink', $comment);
        $I->see('Edit', $comment);
        $I->click('Delete', $comment);

        // The island confirms through its own `CommentDeleteModal` (a native `UiModal`
        // teleported into `document.body`), not the legacy `#globalModalConfirm` dialog.
        $I->waitForElementVisible('.modal.show', 5);
        $I->see('Do you really want to delete this comment?', '.modal.show');
        $I->click('Delete', '.modal.show .modal-footer');

        $I->waitForElementNotVisible($comment, 10);
    }

    /**
     * Opens the post's comment form - the section starts collapsed - and gives the
     * richtext editor a moment to initialize.
     */
    private function openCommentForm(AcceptanceTester $I): void
    {
        $I->click('Comment', self::POST_SELECTOR);
        $I->wait(1);
    }

    /**
     * Creates a root comment through the island's own form, the way a user would, and
     * returns the resulting entry's selector.
     */
    private function createComment(AcceptanceTester $I, string $message): string
    {
        $this->openCommentForm($I);
        $I->fillField(self::RICHTEXT, $message);
        $I->click('.btn-comment-submit', self::COMMENT_SECTION);

        return $this->grabNewComment($I);
    }

    /**
     * Waits for the entry the form just created and returns its `#comment_<id>` selector -
     * the id is whatever the server assigned, never a fixed one (see the class docblock).
     */
    private function grabNewComment(AcceptanceTester $I): string
    {
        $entry = self::COMMENT_SECTION . ' .single-comment';
        $I->waitForElementVisible($entry, 10);

        return '#' . $I->grabAttributeFrom($entry, 'id');
    }
}
