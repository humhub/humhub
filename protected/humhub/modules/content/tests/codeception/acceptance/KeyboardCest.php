<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace content\acceptance;

use content\AcceptanceTester;
use Facebook\WebDriver\WebDriverKeys;
use PHPUnit\Framework\Assert;

/**
 * The keyboard scenarios of docs/ui/keyboard.md ("Checking"), on the stream of space 2 with the
 * fixture post "Admin Space 2 Post Private".
 *
 * @since 1.20
 */
class KeyboardCest
{
    private const POST = '.wall_humhubmodulespostmodelsPost_13';
    private const POST_MENU_TOGGLE = self::POST . ' .preferences .dropdown-toggle';

    public function testSkipLinkIsTheFirstStop(AcceptanceTester $I)
    {
        $I->wantToTest('that Tab reaches the skip link first and Enter lands in the content column, after the space menu');
        $this->openSpaceStream($I);

        $I->pressKeyboard(WebDriverKeys::TAB);
        // The space page has a content column, so the link to the main area is hidden and this one shows
        Assert::assertTrue((bool)$I->executeJS('return document.activeElement.getAttribute("href") === "#layout-content-column";'),
            'the first stop is the skip link to the content, was: ' . $I->grabActiveElement());

        $I->pressKeyboard(WebDriverKeys::ENTER);
        $I->pressKeyboard(WebDriverKeys::TAB);
        Assert::assertTrue((bool)$I->executeJS('return document.getElementById("layout-content-column").contains(document.activeElement);'),
            'the stop after the skip link is inside the content column, was: ' . $I->grabActiveElement());
    }

    public function testSkipLinksReachTheNavigationAndTheSidebar(AcceptanceTester $I)
    {
        $I->wantToTest('that the skip links follow each other and Enter on "Skip to sidebar" lands in the sidebar');
        $this->openSpaceStream($I);

        $I->pressKeyboard(WebDriverKeys::TAB);
        $I->pressKeyboard(WebDriverKeys::TAB);
        Assert::assertTrue((bool)$I->executeJS('return document.activeElement.getAttribute("href") === "#topbar-second";'),
            'the second stop is the skip link to the navigation, was: ' . $I->grabActiveElement());

        $I->pressKeyboard(WebDriverKeys::TAB);
        Assert::assertTrue((bool)$I->executeJS('return document.activeElement.getAttribute("href") === "#layout-sidebar";'),
            'the third stop is the skip link to the sidebar, was: ' . $I->grabActiveElement());

        $I->pressKeyboard(WebDriverKeys::ENTER);
        $I->pressKeyboard(WebDriverKeys::TAB);
        Assert::assertTrue((bool)$I->executeJS('return document.getElementById("layout-sidebar").contains(document.activeElement);'),
            'the stop after the skip link is inside the sidebar, was: ' . $I->grabActiveElement());
    }

    public function testFeedKeysMoveBetweenStreamEntriesAndLeaveTheStream(AcceptanceTester $I)
    {
        $I->wantToTest('that PageDown moves to the next stream entry, PageUp back, and Ctrl+End leaves the stream');
        $this->openSpaceStream($I);

        $I->createPost('Keyboard feed post');
        $I->waitForText('Keyboard feed post', 10, '[data-stream-entry]');
        $entries = '[data-stream-content][role="feed"] > [data-stream-entry][role="article"]';
        Assert::assertGreaterThanOrEqual(2, (int)$I->executeJS('return document.querySelectorAll(' . json_encode($entries) . ').length;'));
        $isEntry = 'return [...document.querySelectorAll(' . json_encode($entries) . ')].indexOf(document.activeElement);';

        $this->focus($I, $entries);
        Assert::assertSame(0, (int)$I->executeJS($isEntry), 'the first entry holds the focus');

        $I->pressKeyboard(WebDriverKeys::PAGE_DOWN);
        Assert::assertSame(1, (int)$I->executeJS($isEntry), 'PageDown moves to the second entry, was: ' . $I->grabActiveElement());

        $I->pressKeyboard(WebDriverKeys::PAGE_UP);
        Assert::assertSame(0, (int)$I->executeJS($isEntry), 'PageUp moves back, was: ' . $I->grabActiveElement());

        $I->pressKeyboard(WebDriverKeys::CONTROL, WebDriverKeys::END);
        Assert::assertTrue((bool)$I->executeJS('return !document.querySelector("[data-stream-content]").contains(document.activeElement)'
            . ' && document.activeElement !== document.body;'),
            'Ctrl+End leaves the stream, was: ' . $I->grabActiveElement());
    }

    public function testFeedKeysMoveBetweenCommentsAndLeaveThemIntoThePost(AcceptanceTester $I)
    {
        $I->wantToTest('that PageDown moves between the comments of a post, stays at the last one, and Ctrl+End leaves the comments into the post');
        $this->openSpaceStream($I);

        $I->click('Comment', self::POST);
        $richtext = self::POST . ' .comment-container .ProseMirror[contenteditable="true"]';
        $I->waitForElementVisible($richtext, 10);
        foreach (['First keyboard comment', 'Second keyboard comment'] as $text) {
            $I->fillField($richtext, $text);
            $I->click('.btn-comment-submit', self::POST . ' .comment-container');
            $I->waitForText($text, 10, self::POST . ' .comment-container');
            // The form clears and remounts its editor after the submit; typing into the old one fails as stale
            $I->waitForJS('var editor = document.querySelector(' . json_encode($richtext) . '); return editor && !editor.textContent.includes(' . json_encode($text) . ');', 10);
            $I->wait(1);
        }
        $comments = self::POST . ' .comment-container > .comment[role="feed"] > .single-comment[role="article"]';
        $index = 'return [...document.querySelectorAll(' . json_encode($comments) . ')].indexOf(document.activeElement);';
        Assert::assertSame(2, (int)$I->executeJS('return document.querySelectorAll(' . json_encode($comments) . ').length;'), 'two comments are articles of the comment feed');

        $this->focus($I, $comments);
        $I->pressKeyboard(WebDriverKeys::PAGE_DOWN);
        Assert::assertSame(1, (int)$I->executeJS($index), 'PageDown moves to the second comment, was: ' . $I->grabActiveElement());

        $I->pressKeyboard(WebDriverKeys::PAGE_DOWN);
        Assert::assertSame(1, (int)$I->executeJS($index), 'the last comment keeps the focus instead of the next post, was: ' . $I->grabActiveElement());

        $I->pressKeyboard(WebDriverKeys::CONTROL, WebDriverKeys::END);
        Assert::assertTrue((bool)$I->executeJS('return document.querySelector(' . json_encode(self::POST) . ').contains(document.activeElement)'
            . ' && !document.activeElement.closest(".comment[role=feed]");'),
            'Ctrl+End leaves the comments but stays in the post, was: ' . $I->grabActiveElement());
    }

    public function testDialogReturnsTheFocusToItsTrigger(AcceptanceTester $I)
    {
        $I->wantToTest('that a dialog closed with Escape puts the focus back on its trigger');
        $this->openSpaceStream($I);

        $this->focus($I, self::POST_MENU_TOGGLE);
        $I->pressKeyboard(WebDriverKeys::ENTER);
        $I->waitForElementVisible(self::POST . ' .dropdown-menu.show', 5);

        $this->focus($I, self::POST . ' [data-content-permalink]');
        $I->pressKeyboard(WebDriverKeys::ENTER);
        $I->waitForElementVisible('.modal.show#globalModal', 5);
        $I->waitForJS('return document.getElementById("globalModal").contains(document.activeElement);', 5); // Bootstrap moves the focus in on `shown`

        $I->pressKeyboard(WebDriverKeys::ESCAPE);
        $I->waitForElementNotVisible('#globalModal', 5);
        $I->waitForJS('return !document.body.classList.contains("modal-open");', 5);
        // The trigger (the Permalink item) sits in the closed dropdown, so its toggle takes the focus
        Assert::assertTrue((bool)$I->executeJS('return document.activeElement === document.querySelector(' . json_encode(self::POST_MENU_TOGGLE) . ');'),
            'the focus is back on the menu toggle, was: ' . $I->grabActiveElement());
    }

    public function testDialogKeepsTabInsideAndConfirmFocusesCancel(AcceptanceTester $I)
    {
        $I->wantToTest('that Tab goes round the controls of an open dialog and a confirmation focuses "Cancel"');
        $this->openSpaceStream($I);

        $this->focus($I, self::POST_MENU_TOGGLE);
        $I->pressKeyboard(WebDriverKeys::ENTER);
        $I->waitForElementVisible(self::POST . ' .dropdown-menu.show', 5);
        $this->focus($I, self::POST . ' [data-content-permalink]');
        $I->pressKeyboard(WebDriverKeys::ENTER);
        $I->waitForElementVisible('.modal.show#globalModal', 5);
        $I->waitForJS('return document.getElementById("globalModal").contains(document.activeElement);', 5);

        for ($i = 0; $i < 6; $i++) {
            $I->pressKeyboard(WebDriverKeys::TAB);
            Assert::assertTrue((bool)$I->executeJS('return document.getElementById("globalModal").contains(document.activeElement);'),
                'Tab ' . ($i + 1) . ' stays in the dialog, was: ' . $I->grabActiveElement());
        }
        $I->pressKeyboard(WebDriverKeys::ESCAPE);
        $I->waitForElementNotVisible('#globalModal', 5);

        $I->executeJS('humhub.modules.ui.modal.confirm({body: "Sure?"});');
        $I->waitForElementVisible('.modal.show#globalModalConfirm', 5);
        $I->waitForJS('return !!document.activeElement && document.activeElement.matches("#globalModalConfirm [data-modal-cancel]");', 5);

        $I->pressKeyboard(WebDriverKeys::ENTER);
        $I->waitForElementNotVisible('#globalModalConfirm', 5);
    }

    public function testTooltipShowsOnKeyboardFocusAndHidesOnEscape(AcceptanceTester $I)
    {
        $I->wantToTest('that a tooltip appears on keyboard focus and Escape hides it');
        $this->openSpaceStream($I);

        // The space page has no focusable tooltip control in a fixed place, so add one before the skip link
        $I->executeJS('document.body.insertAdjacentHTML("afterbegin", '
            . '\'<button id="kb-tooltip" class="btn" data-bs-toggle="tooltip" data-bs-title="Keyboard tooltip">t</button>\');');

        $I->pressKeyboard(WebDriverKeys::TAB);
        Assert::assertStringContainsString('button#kb-tooltip', $I->grabActiveElement());
        $I->waitForElementVisible('.tooltip.show', 3);
        $I->see('Keyboard tooltip', '.tooltip.show');

        // Enter clicks the button: the click dismisses the tooltip through Bootstrap, so Escape is not swallowed later
        $I->pressKeyboard(WebDriverKeys::ENTER);
        // Bootstrap removes the tip after its fade; waiting for that avoids racing the next show() against it
        $I->waitForJS('return document.querySelectorAll(".tooltip").length === 0;', 3);

        // Focus it again by keyboard (the modality is still keyboard): the tooltip comes back, Escape hides it.
        // Bootstrap shows it after its delay; a second focus covers a hide that was still running (CI flake)
        for ($i = 0; $i < 3 && !$I->executeJS('return !!document.querySelector(".tooltip.show");'); $i++) {
            $I->executeJS('document.activeElement.blur(); document.getElementById("kb-tooltip").focus();');
            $I->wait(1);
        }
        $I->waitForElementVisible('.tooltip.show', 3);

        $I->pressKeyboard(WebDriverKeys::ESCAPE);
        // Bootstrap removes the tip after its fade; waiting for that avoids racing the next show() against it
        $I->waitForJS('return document.querySelectorAll(".tooltip").length === 0;', 3);
        Assert::assertStringContainsString('button#kb-tooltip', $I->grabActiveElement());
    }

    public function testCommentMenuIsReachableByKeyboard(AcceptanceTester $I)
    {
        $I->wantToTest('that the comment menu toggle shows while the focus is in the comment and is reached by Shift+Tab');
        $this->openSpaceStream($I);

        $I->click('Comment', self::POST);
        $richtext = self::POST . ' .comment-container .ProseMirror[contenteditable="true"]';
        $I->waitForElementVisible($richtext, 10);
        $I->fillField($richtext, 'Keyboard test comment');
        $I->click('.btn-comment-submit', self::POST . ' .comment-container');
        $entry = self::POST . ' .comment-container .single-comment';
        $I->waitForElementVisible($entry, 10);
        $comment = '#' . $I->grabAttributeFrom($entry, 'id');
        $toggle = $comment . ' > .preferences .dropdown-toggle';
        $opacity = 'return getComputedStyle(document.querySelector(' . json_encode($toggle) . ')).opacity;';
        $isToggleFocused = 'return document.activeElement === document.querySelector(' . json_encode($toggle) . ');';

        // Headless Chrome reports no hover capability, so the toggle is always shown there and the hidden state
        // is only covered by a manual check in a desktop browser (docs/ui/keyboard.md, "Checking")
        if ($I->executeJS('return matchMedia("(hover: hover)").matches;')) {
            Assert::assertSame('0', $I->executeJS($opacity), 'hidden without hover or focus');
        }

        $this->focus($I, $comment . ' .comment-heading a');
        Assert::assertSame('1', $I->executeJS($opacity), 'shown while the focus is in the comment');

        for ($i = 0; $i < 3 && !$I->executeJS($isToggleFocused); $i++) {
            $I->pressKeyboard(WebDriverKeys::SHIFT, WebDriverKeys::TAB);
        }
        Assert::assertTrue((bool)$I->executeJS($isToggleFocused), 'Shift+Tab reaches the toggle, last stop: ' . $I->grabActiveElement());
        Assert::assertSame('1', $I->executeJS($opacity), 'shown while it holds the focus');
    }

    public function testEnterTriggersAnActionOnANonNativeElement(AcceptanceTester $I)
    {
        $I->wantToTest('that Enter triggers data-action-click on an element that is no link or button');
        $this->openSpaceStream($I);

        // No such element exists in core (links and buttons are used), so add one before the skip link
        $permalink = $I->grabAttributeFrom(self::POST . ' [data-content-permalink]', 'data-content-permalink');
        $I->executeJS('document.body.insertAdjacentHTML("afterbegin", \'<span id="kb-action" role="button" tabindex="0"'
            . ' data-action-click="content.permalink" data-content-permalink="\' + ' . json_encode($permalink) . ' + \'">p</span>\');');

        $I->pressKeyboard(WebDriverKeys::TAB);
        Assert::assertStringContainsString('span#kb-action', $I->grabActiveElement());

        $I->pressKeyboard(WebDriverKeys::ENTER);
        $I->waitForElementVisible('.modal.show#globalModal', 5);
        $I->waitForJS('return document.getElementById("globalModal").contains(document.activeElement);', 5); // Bootstrap moves the focus in on `shown`

        $I->pressKeyboard(WebDriverKeys::ESCAPE);
        $I->waitForElementNotVisible('#globalModal', 5);
        $I->waitForJS('return !document.body.classList.contains("modal-open");', 5);
        Assert::assertStringContainsString('span#kb-action', $I->grabActiveElement());

        // Space does the same
        $I->pressKeyboard(WebDriverKeys::SPACE);
        $I->waitForElementVisible('.modal.show#globalModal', 5);
        $I->waitForJS('return document.getElementById("globalModal").contains(document.activeElement);', 5);
        $I->pressKeyboard(WebDriverKeys::ESCAPE);
        $I->waitForElementNotVisible('#globalModal', 5);
        $I->waitForJS('return !document.body.classList.contains("modal-open");', 5);
    }

    public function testEnterOnAChildOfAnActionElementDoesNotFireIt(AcceptanceTester $I)
    {
        $I->wantToTest('that Enter on a focusable child of a data-action-click element does not fire the action of the parent');
        $this->openSpaceStream($I);

        $permalink = $I->grabAttributeFrom(self::POST . ' [data-content-permalink]', 'data-content-permalink');
        $I->executeJS('document.body.insertAdjacentHTML("afterbegin", \'<div id="kb-card" tabindex="0" data-action-click="content.permalink"'
            . ' data-content-permalink="\' + ' . json_encode($permalink) . ' + \'"><span id="kb-inner" tabindex="0">i</span></div>\');');

        $this->focus($I, '#kb-inner');
        Assert::assertStringContainsString('span#kb-inner', $I->grabActiveElement());
        $I->pressKeyboard(WebDriverKeys::ENTER);
        $I->wait(1);
        $I->dontSeeElement('.modal.show#globalModal');
    }

    public function testDeletedPostHandsTheFocusToTheNextEntry(AcceptanceTester $I)
    {
        $I->wantToTest('that deleting a stream entry moves the focus into the next entry');
        $this->openSpaceStream($I);

        $I->createPost('Keyboard test post');
        $I->waitForText('Keyboard test post', 10, '[data-stream-entry]');
        $newEntry = '[data-content-key="' . $I->executeJS('return document.querySelector("[data-stream-entry]").getAttribute("data-content-key");') . '"]';
        $I->see('Keyboard test post', $newEntry);

        $this->focus($I, $newEntry . ' .preferences .dropdown-toggle');
        $I->pressKeyboard(WebDriverKeys::ENTER);
        $I->waitForElementVisible($newEntry . ' .dropdown-menu.show', 5);
        $I->executeJS('[...document.querySelectorAll(' . json_encode($newEntry . ' .dropdown-menu.show a') . ')]'
            . '.find((a) => a.textContent.trim() === "Delete").focus();');
        $I->pressKeyboard(WebDriverKeys::ENTER);

        $I->waitForElementVisible('.modal.show#globalModalConfirm', 5);
        // Answered by a click right after the dialog appeared: depending on the server's timing the focus is then in an open or a closing dialog; both paths are covered by the ui.focus unit tests, the outcome is the same
        $I->click('Delete', '#globalModalConfirm');
        $I->waitForElementNotVisible($newEntry, 10);
        $I->waitForElementNotVisible('#globalModalConfirm', 5);
        $I->waitForJS('return !document.body.classList.contains("modal-open");', 5);

        Assert::assertTrue((bool)$I->executeJS('return document.activeElement.closest("[data-stream-entry]") !== null;'),
            'the focus is inside another stream entry, was: ' . $I->grabActiveElement());
    }

    public function testTheCreateFormExpandsOnKeyboardFocus(AcceptanceTester $I)
    {
        $I->wantToTest('that the create-content form expands when the keyboard focus enters it, and the focus stays in the editor');
        $this->openSpaceStream($I);

        $editor = '#contentFormBody .ProseMirror[contenteditable="true"]';
        $I->waitForElementVisible($editor, 10);
        Assert::assertFalse((bool)$I->executeJS('return document.getElementById("contentFormBody").classList.contains("contentForm-expanded");'),
            'the form is collapsed at first');

        $this->focus($I, $editor);
        $I->waitForElementVisible('.contentForm-expanded#contentFormBody', 5);
        $I->waitForElementVisible('#contentFormBody .contentForm_options', 5);
        Assert::assertTrue((bool)$I->executeJS('return document.activeElement === document.querySelector(' . json_encode($editor) . ');'),
            'the editor keeps the focus, was: ' . $I->grabActiveElement());
    }

    private function openSpaceStream(AcceptanceTester $I): void
    {
        $I->amUser1();
        $I->amOnSpace2();
        $I->waitForText('Admin Space 2 Post Private');
    }

    private function focus(AcceptanceTester $I, string $selector): void
    {
        $I->executeJS('document.querySelector(' . json_encode($selector) . ').focus();');
    }
}
