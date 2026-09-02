<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The content context menu endpoint
 * (`humhub\modules\content\controllers\api\ControlsController`) — the data behind the `⋮`
 * menu, and what the `ContentControls` Vue island loads when that menu is opened.
 *
 * Fixture baseline: content 1 is Admin's (user 1) private profile post, content 10 a public
 * post in Space 2 where User1 (user 2) is a member.
 *
 * **One identity per test**, for the reason described in {@see CommentApiCest}: the API base
 * controller replaces the user component with a session-less one, so `amLoggedInAs()` must
 * run before the first request of a test.
 */
class ContentControlsApiCest
{
    /**
     * @return array the decoded `entries` of the last response
     */
    private function entries(ApiTester $I): array
    {
        return (array)(json_decode($I->grabResponse(), true)['entries'] ?? []);
    }

    public function testMenuOfOwnContent(ApiTester $I)
    {
        $I->wantTo('read the context menu of my own content');

        $I->amLoggedInAs(1);
        $I->sendGet('content/1/controls');
        $I->seeResponseCodeIs(200);

        // Capabilities are the whole point of the endpoint: they are what a host island gates
        // its OWN entries on, instead of re-implementing the platform's rules client-side.
        $I->seeResponseContainsJson([
            'capabilities' => [
                'canEdit' => true,
                'canDelete' => true,
                // Admin's own post - the moderation flow (reason, notify author) does not apply.
                'canAdminDelete' => false,
            ],
        ]);

        $entries = $this->entries($I);
        Assert::assertNotEmpty($entries, 'the menu of an editable record is never empty');

        foreach ($entries as $entry) {
            // `id` and `sortOrder` are what a client sorts, overrides and removes entries by,
            // so every entry carries them even when it is a divider or a raw-HTML fallback.
            Assert::assertArrayHasKey('id', $entry);
            Assert::assertArrayHasKey('sortOrder', $entry);
            Assert::assertTrue(
                isset($entry['label']) || isset($entry['html']) || !empty($entry['divider']),
                'an entry is either described, a divider, or the raw-HTML fallback: ' . json_encode($entry),
            );
        }

        $ids = array_column($entries, 'id');
        Assert::assertSame(array_unique($ids), $ids, 'entry ids are unique within one menu');
    }

    public function testEntryIdsAreNamedAfterTheirWidget(ApiTester $I)
    {
        $I->wantTo('see entries carry stable, class-derived ids');

        $I->amLoggedInAs(1);
        $I->sendGet('content/1/controls');
        $I->seeResponseCodeIs(200);

        $ids = array_column($this->entries($I), 'id');

        // The id is the handle a client overrides and removes an entry by, so it must not
        // depend on the entry's position: a module contributing a single entry through
        // `WallEntryControls::EVENT_INIT` would otherwise renumber everything after it, and
        // the same id would mean different entries on two installations.
        foreach ($ids as $id) {
            Assert::assertDoesNotMatchRegularExpression(
                '/^entry(-\d+)?$/',
                $id,
                'entry ids are derived from the widget class, never from the position',
            );
        }

        // Core entries, under the name their widget class gives them.
        Assert::assertContains('edit-link', $ids);
        Assert::assertContains('delete-link', $ids);
        Assert::assertContains('perma-link', $ids);
        Assert::assertContains('archive-link', $ids);

        // `move-content-link` already describes itself while `archive-link` is still shipped
        // as raw HTML - and both are named by the same rule. That is what makes converting a
        // widget to `DescribableWidget` invisible to a client: the entry keeps its id.
        Assert::assertContains('move-content-link', $ids);
    }

    public function testSuppressDropsCoreEntries(ApiTester $I)
    {
        $I->wantTo('drop the core entries I render myself');

        $I->amLoggedInAs(1);
        $I->sendGet('content/1/controls');
        $I->seeResponseCodeIs(200);
        $full = count($this->entries($I));

        // What a module's own list does when it already offers Edit/Delete/Move of its own -
        // without this it gets a second, server-rendered copy of each next to it.
        $I->sendGet('content/1/controls?suppress=edit,delete,move');
        $I->seeResponseCodeIs(200);
        $suppressed = count($this->entries($I));

        Assert::assertSame($full - 3, $suppressed, 'each suppressed name removes exactly its entry');

        // An unknown name is ignored rather than being an error, so a client may pass a name
        // a newer core knows without breaking against an older one.
        $I->sendGet('content/1/controls?suppress=edit,delete,move,not-a-real-entry');
        $I->seeResponseCodeIs(200);
        Assert::assertSame($suppressed, count($this->entries($I)));
    }

    public function testViewContextSelectsTheProfile(ApiTester $I)
    {
        $I->wantTo('see the view context pick the server-side render profile');

        // Pin is offered in the default and detail contexts only (see
        // `WallStreamEntryWidget::getControlsMenuEntries()`), which is exactly the kind of
        // stream-only action a menu inside a module's own UI must not be handed.
        $I->amLoggedInAs(1);
        $I->sendGet('content/1/controls?viewContext=detail');
        $I->seeResponseCodeIs(200);
        $detail = count($this->entries($I));

        $I->sendGet('content/1/controls?viewContext=modal');
        $I->seeResponseCodeIs(200);
        $modal = count($this->entries($I));

        Assert::assertGreaterThan($modal, $detail, 'the modal profile offers fewer entries than the detail one');
    }

    public function testGuestsAreRejected(ApiTester $I)
    {
        $I->wantTo('see the endpoint refuse guests');

        // A guest has no permissions to report and no menu to open, so the endpoint stays
        // authenticated-only even where the content itself is guest-readable.
        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 1);

        try {
            $I->sendGet('content/10/controls');
            $I->seeResponseCodeIs(401);
        } finally {
            Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 0);
        }
    }

    public function testContentTheCallerMayNotSee(ApiTester $I)
    {
        $I->wantTo('be refused the menu of content I may not see');

        // Content 1 is Admin's private profile post.
        $I->amLoggedInAs(2);
        $I->sendGet('content/1/controls');
        $I->seeResponseCodeIs(403);

        $I->sendGet('content/99999/controls');
        $I->seeResponseCodeIs(404);
    }
}
