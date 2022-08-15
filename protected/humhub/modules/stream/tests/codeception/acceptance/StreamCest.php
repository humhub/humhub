<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace stream\acceptance;

use DateTime;
use stream\AcceptanceTester;
use Yii;

class StreamCest
{
    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testDeletePost(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace2();
        $I->wantToTest('the deletion of a stream entry');
        $I->amGoingTo('create a new post and delete it afterwards');

        $I->createPost('This is my stream test post!');

        $newEntrySelector = '[data-content-key="15"]';

        $I->waitForElementVisible($newEntrySelector);
        $I->see('This is my stream test post', '.wall-entry');

        $I->amGoingTo('Delte my new post');
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->wait(1);
        $I->click('Delete', '[data-content-key="15"]');

        $I->waitForElementVisible('#globalModalConfirm', 5);
        $I->see('Confirm post deletion');
        $I->click('Delete', '#globalModalConfirm');

        $I->waitForElementNotVisible($newEntrySelector);
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testArchivePost(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace2();
        $I->wantToTest('the archivation of a stream entry');
        $I->amGoingTo('create a new post and archive it afterwards');

        $I->createPost('This is my stream test post!');

        $newEntrySelector = '[data-content-key="15"]';
        $archivedEntrySelectors = ['[data-content-key="14"]', $newEntrySelector];

        $I->waitForElementVisible($newEntrySelector);
        $I->see('This is my stream test post', '.wall-entry');

        $I->amGoingTo('Archive my new post');
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->waitForText('Move to archive', 10);
        $I->click('Move to archive', $newEntrySelector);

        $I->seeSuccess('The content has been archived.');

        $I->expect('my archived post not to be in the stream anymore.');
        $I->dontSeeElement($newEntrySelector);

        $I->amGoingTo('check if my post is visible with filter include archived');
        $I->click('Filter', '.wall-stream-filter-head');
        $I->waitForElementVisible('[data-filter-id="entry_archived"]');
        $I->click('[data-filter-id="entry_archived"]');

        $I->waitForElementVisible($newEntrySelector, 20);
        $I->expectTo('see my archived post');
        $I->waitForText('This is my stream test post', null, '.wall-entry');

        $I->amGoingTo('unarchive this post again');

        foreach ($archivedEntrySelectors as $archivedEntrySelector) {
            $I->click('.preferences .dropdown-toggle', $archivedEntrySelector);
            $I->waitForText('Unarchive', 10);
            $I->click('Unarchive', $archivedEntrySelector);
            $I->seeSuccess('The content has been unarchived.');
        }

        $I->expectTo('See my unarchived post again');
        $I->see('No matches with your selected filters!', '.streamMessage');
        $I->dontSee('This is my stream test post', '.wall-entry');

        $I->amGoingTo('check if my post is visible without archived');
        $I->waitForElementVisible('[data-filter-id="entry_archived"]');
        $I->click('[data-filter-id="entry_archived"]');
        $I->waitForElementVisible($newEntrySelector);

        $I->amGoingTo('archive the post again with include archived filter');
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->waitForText('Move to archive', 10);
        $I->click('Move to archive', $newEntrySelector);
        $I->seeSuccess('The content has been archived.');
        $I->dontSee('This is my stream test post', '.wall-entry');
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testPinPost(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace2();
        $I->wantToTest('the pin of posts');
        $I->amGoingTo('create a new post and delete it afterwards');

        $I->createPost('This is my first stream test post!');

        $newEntrySelector = '[data-content-key="15"]';

        $I->waitForElementVisible($newEntrySelector);
        $I->see('This is my first stream test post', '.wall-entry');

        $I->amGoingTo('create another post');

        $I->createPost('This is my second stream test post!');

        $newEntrySelector2 = '[data-stream-entry]:nth-of-type(3)';
        $I->waitForElementVisible($newEntrySelector2);
        $I->expectTo('my new post beeing the latest entry');
        $I->waitForText('This is my second stream test post', null, '.s2_streamContent div:nth-child(1)');

        $I->amGoingTo('pin my first entry');
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->waitForText('Pin', 10);
        $I->click('Pin', $newEntrySelector);

        $I->waitForText('This is my first stream test post!', null, '.s2_streamContent div:nth-child(1)');

        $I->amGoingTo('unpin my first entry');
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->waitForText('Unpin', 10);
        $I->click('Unpin', $newEntrySelector);
        $I->waitForText('This is my second stream test post!', null, '.s2_streamContent div:nth-child(1)');
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testEditPost(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace2();

        $I->wantToTest('the edit post mechanism');
        $I->amGoingTo('create a new post and delete it afterwards');

        $I->createPost('This is my first stream test post!');

        $newEntrySelector = '[data-content-key="15"]';

        $I->waitForElementVisible($newEntrySelector);
        $I->see('This is my first stream test post', '.wall-entry');

        $I->amGoingTo('edit load the edit form');
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->waitForText('Edit', 10);
        $I->click('Edit', $newEntrySelector);

        $I->waitForElementVisible($newEntrySelector . ' .content_edit');
        $I->amGoingTo('cancel my edit');
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->waitForText('Cancel Edit', 10);
        $I->click('Cancel Edit', $newEntrySelector);
        $I->waitForElementNotVisible($newEntrySelector . ' .content_edit', 20);
        $I->waitForElementVisible($newEntrySelector . ' .content', 20);
        $I->see('This is my first stream test post!', $newEntrySelector);

        $I->amGoingTo('edit my new post');
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->waitForText('Edit', 10);
        $I->click('Edit', $newEntrySelector);

        $I->waitForElementVisible($newEntrySelector . ' .content_edit', 20);
        $I->fillField($newEntrySelector . ' [contenteditable]', 'This is my edited post!');
        $I->click('button[data-action-click=editSubmit]', $newEntrySelector);

        $I->wait(1);
        $I->seeElement($newEntrySelector);
        $I->see('This is my edited post!', $newEntrySelector);
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testEmptyStream(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace3();
        $I->wantToTest('the empty stream message and filter');

        $I->waitForText('This space is still empty!');
        $I->dontSeeElement('#wall-stream-filter-nav');

        $I->amGoingTo('create a new post and delete it afterwards');

        $I->createPost('This is my first stream test post!');

        $I->wait(1);

        $I->amGoingTo('Delete my new post again.');
        $I->waitForElementVisible('#wall-stream-filter-nav');
        $I->dontSee('This space is still empty!');
        $I->click('.preferences .dropdown-toggle', '[data-stream-entry]:nth-of-type(1)');
        $I->wait(1);
        $I->click('Delete');

        $I->waitForElementVisible('#globalModalConfirm', 5);
        $I->see('Confirm post deletion');
        $I->click('Delete', '#globalModalConfirm');

        $I->waitForText('This space is still empty!');
        $I->dontSeeElement('#wall-stream-filter-nav');
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testFilterInvolved(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace2();


        $I->amGoingTo('filter the stream for involved posts.');
        $I->expect('not to see any posts since I did not participate in any posts yet.');

        $I->waitForElementVisible('.wall-stream-filter-head');
        $I->click('Filter', '.wall-stream-filter-head');
        $I->wait(1);
        $I->waitForElementVisible('[data-filter-id="entry_userinvolved"]');
        $I->click('[data-filter-id="entry_userinvolved"]');
        $I->waitForText('No matches with your selected filters!');

        $I->amGoingTo('create a new post.');
        $I->expectTo('see my new post in the stream after creation.');

        $I->createPost('Involved Post.');
        $I->wait(1);
        $I->dontSee('No matches with your selected filters!');

        $I->amGoingTo('reset the filter and comment another post');

        $I->waitForElementVisible('[data-filter-id="entry_userinvolved"]');
        $I->click('[data-filter-id="entry_userinvolved"]');

        $postSelector = '[data-content-key="13"]';
        $I->waitForElementVisible($postSelector);

        $I->click('Comment', $postSelector);
        $I->wait(1);
        $I->waitForElementVisible($postSelector . ' .comment-container', null);
        $I->fillField($postSelector . ' .comment_create .humhub-ui-richtext', 'My Comment');
        $I->click('[data-action-click=submit]', $postSelector . ' .comment_create');
        $I->waitForText('My Comment', null, $postSelector . ' .comment');


        $I->amGoingTo('reactivate the involved filter.');
        $I->expectTo('see the commented post after the stream reload.');

        $I->scrollTop();
        $I->click('[data-filter-id="entry_userinvolved"]');
        $I->wait(1);
        $I->waitForText('Involved Post.');

        $I->seeElement('[data-content-key="13"]');
        $I->seeElement('[data-content-key="15"]');
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testSortStream(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace3();
        $I->wantToTest('the stream entry ordering');
        $I->amGoingTo('create a new post and delete it afterwards');

        $I->createPost('POST1');
        $I->wait(1);
        $I->createPost('POST2');
        $I->wait(1);
        $I->createPost('POST3');
        $I->wait(1);
        $I->createPost('POST4');
        $I->wait(1);
        $I->createPost('POST5');

        $I->see('POST5', '.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->see('POST4', '.s2_streamContent > [data-stream-entry]:nth-of-type(2)');
        $I->see('POST3', '.s2_streamContent > [data-stream-entry]:nth-of-type(3)');
        $I->see('POST2', '.s2_streamContent > [data-stream-entry]:nth-of-type(4)');
        $I->see('POST1', '.s2_streamContent > [data-stream-entry]:nth-of-type(5)');

        $post4Selector = '[data-stream-entry]:nth-of-type(2)';

        $I->click('Comment', $post4Selector);
        $I->wait(1);
        $I->fillField($post4Selector . ' [contenteditable]', 'My Comment!');
        $I->click('[data-action-click=submit]', $post4Selector . ' .comment-buttons');

        $I->scrollTop();

        $I->click('Filter', '.wall-stream-filter-head');
        $I->waitForElementVisible('[data-filter-id="sort_update"]');
        $I->click('[data-filter-id="sort_update"]');
        $I->wait(2);
        $I->waitForElementVisible($post4Selector);

        $I->see('POST4', '.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->see('POST5', '.s2_streamContent > [data-stream-entry]:nth-of-type(2)');
        $I->see('POST3', '.s2_streamContent > [data-stream-entry]:nth-of-type(3)');
        $I->see('POST2', '.s2_streamContent > [data-stream-entry]:nth-of-type(4)');
        $I->see('POST1', '.s2_streamContent > [data-stream-entry]:nth-of-type(5)');
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testDateFilter(AcceptanceTester $I)
    {
        $I->amAdmin();
        $I->amOnSpace1();
        $I->wantToTest('the stream date filters');
        $I->amGoingTo('create a new post');

        $postTitle = 'Post for test date filter';
        $today = Yii::$app->formatter->asDate(new DateTime(), 'short');
        $yesterday = Yii::$app->formatter->asDate((new DateTime())->modify('-1 day'), 'short');

        $I->createPost($postTitle);
        $I->waitForText($postTitle, null, '.s2_streamContent');

        $I->amGoingTo('filter stream by date from today');
        $I->waitForElementVisible('.wall-stream-filter-head');
        $I->click('Filter', '.wall-stream-filter-head');
        $I->wait(1);
        $I->waitForElementVisible('[data-filter-id=date_from]');
        $I->fillDateFilter('date_from', $today);
        $I->waitForText($postTitle, 10, '.s2_streamContent');

        $I->amGoingTo('filter stream by date until yesterday');
        $I->fillDateFilter('date_from', '');
        $I->fillDateFilter('date_to', $yesterday);
        $I->waitForElement('.s2_streamContent > .stream-end', 10);
        $I->dontSee($postTitle, '.s2_streamContent');
    }

    // Filtering
    // multi click logic
    // empty form
    // Poll
}
