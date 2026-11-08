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
        $I->wantToTest('that Tab reaches the skip link first and Enter lands in the main content');
        $this->openSpaceStream($I);

        $I->pressKeyboard(WebDriverKeys::TAB);
        Assert::assertTrue((bool)$I->executeJS('return document.activeElement.getAttribute("href") === "#main-content";'),
            'the first stop is the skip link, was: ' . $I->grabActiveElement());

        $I->pressKeyboard(WebDriverKeys::ENTER);
        $I->pressKeyboard(WebDriverKeys::TAB);
        Assert::assertTrue((bool)$I->executeJS('return document.getElementById("main-content").contains(document.activeElement);'),
            'the stop after the skip link is inside the main content, was: ' . $I->grabActiveElement());
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
        $I->waitForElementNotVisible('.tooltip', 3);

        // Focus it again by keyboard (the modality is still keyboard): the tooltip comes back, Escape hides it
        $I->executeJS('document.activeElement.blur(); document.getElementById("kb-tooltip").focus();');
        $I->waitForElementVisible('.tooltip.show', 3);

        $I->pressKeyboard(WebDriverKeys::ESCAPE);
        $I->waitForElementNotVisible('.tooltip', 3);
        Assert::assertStringContainsString('button#kb-tooltip', $I->grabActiveElement());
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
