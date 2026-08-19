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
 *  - delete goes through the shared `#globalModalConfirm` confirm dialog (see
 *    `CommentEntry.vue`'s `onDelete()`), the same dialog `stream\StreamCest` already exercises
 *    for content deletion (`.preferences .dropdown-toggle` + `Delete` + confirm).
 *
 * Test methods run in declaration order against a shared DB fixture (no `Db` module/fixture
 * reset is configured for this suite - see `comment/tests/codeception/acceptance.suite.yml`),
 * so later methods build on the comment the first one creates, exactly like the legacy
 * single-method version already assumed a deterministic `#comment-message-1`.
 */
class CommentCest
{
    private const POST_SELECTOR = '.wall_humhubmodulespostmodelsPost_13';

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

        $commentSection = self::POST_SELECTOR . ' .comment-container';
        $richtext = $commentSection . ' .ProseMirror[contenteditable="true"]';

        $I->click('Comment', self::POST_SELECTOR);
        $I->wait(1);

        $I->click('.btn-comment-submit', $commentSection);
        $I->waitForText('The comment must not be empty!', 10, $commentSection . ' .invalid-feedback');

        $I->fillField($richtext, 'Test comment');
        $I->click('.btn-comment-submit', $commentSection);

        $I->waitForElementVisible('#comment_1', 10);
        $I->see('Test comment', '#comment_1 .comment-message');
        $I->dontSee('The comment must not be empty!', $commentSection);
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

        $comment = '#comment_1';
        $I->waitForElementVisible($comment, 10);
        $I->click('Comment', self::POST_SELECTOR);
        $I->wait(1);

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

        $comment = '#comment_1';
        $I->waitForElementVisible($comment, 10);
        $I->click('Comment', self::POST_SELECTOR);
        $I->wait(1);

        $I->click('.preferences .dropdown-toggle', $comment);
        $I->see('Permalink', $comment);
        $I->see('Edit', $comment);
        $I->click('Delete', $comment);

        $I->waitForElementVisible('#globalModalConfirm', 5);
        $I->see('Do you really want to delete this comment?', '#globalModalConfirm');
        $I->click('Delete', '#globalModalConfirm');

        $I->waitForElementNotVisible($comment, 10);
    }
}
