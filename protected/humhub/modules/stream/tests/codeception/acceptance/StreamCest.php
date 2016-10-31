<?php

namespace stream\acceptance;

use stream\AcceptanceTester;

class StreamCest
{

    public function testDeletePost(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace2();
        $I->wantToTest('the deletion of a stream entry');
        $I->amGoingTo('create a new post and delete it afterwards');

        $I->click('#contentForm_message_contenteditable');
        $I->fillField('#contentForm_message_contenteditable', 'This is my stream test post!');
        $I->click('#post_submit_button');

        $newEntrySelector = '[data-content-key="12"]';

        $I->waitForElementVisible($newEntrySelector);
        $I->see('This is my stream test post', '.wall-entry');

        $I->amGoingTo('Delte my new post');
        $I->click('.preferences', $newEntrySelector);
        $I->wait(1);
        $I->click('Delete');

        $I->waitForElementVisible('#globalModalConfirm', 5);
        $I->see('Do you really want to perform this action?');
        $I->click('Confirm');

        $I->seeSuccess('The content has been deleted');
    }

    public function testArchivePost(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace2();
        $I->wantToTest('the archivation of a stream entry');
        $I->amGoingTo('create a new post and archive it afterwards');

        $I->createPost('This is my stream test post!');

        $newEntrySelector = '[data-content-key="12"]';

        $I->waitForElementVisible($newEntrySelector);
        $I->see('This is my stream test post', '.wall-entry');

        $I->amGoingTo('Archive my new post');
        $I->click('.preferences', $newEntrySelector);
        $I->waitForText('Move to archive', 10);
        $I->click('Move to archive', $newEntrySelector);

        $I->seeSuccess('The content has been archived.');

        $I->expect('my archived post not to be in the stream anymore.');
        $I->dontSeeElement($newEntrySelector);

        $I->amGoingTo('check if my post is visible with filter include archived');
        $I->click('Filter', '#filter');
        $I->waitForElementVisible('#filter_entry_archived');
        $I->click('#filter_entry_archived');

        $I->waitForElementVisible($newEntrySelector, 20);
        $I->expectTo('see my archived post');
        $I->see('This is my stream test post', '.wall-entry');
        $I->see('Archived', $newEntrySelector);

        $I->amGoingTo('unarchive this post again');

        $I->click('.preferences', $newEntrySelector);
        $I->waitForText('Unarchive', 10);
        $I->click('Unarchive', $newEntrySelector);

        $I->expectTo('See my unarchived post again');
        $I->seeSuccess('The content has been unarchived.');
        $I->see('This is my stream test post', '.wall-entry');
        $I->dontSee('Archived', $newEntrySelector);

        $I->amGoingTo('archive the post again with include archived filter');
        $I->click('.preferences', $newEntrySelector);
        $I->waitForText('Move to archive', 10);
        $I->click('Move to archive', $newEntrySelector);
        $I->seeSuccess('The content has been archived.');
        $I->see('This is my stream test post', '.wall-entry');
        $I->see('Archived', $newEntrySelector);
    }

    public function testStickPost(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace2();
        $I->wantToTest('the stick of posts');
        $I->amGoingTo('create a new post and delete it afterwards');

        $I->createPost('This is my first stream test post!');

        $newEntrySelector = '[data-content-key="12"]';

        $I->waitForElementVisible($newEntrySelector);
        $I->see('This is my first stream test post', '.wall-entry');

        $I->amGoingTo('create another post');

        $I->createPost('This is my second stream test post!');

        $newEntrySelector2 = '[data-content-key="14"]';
        $I->waitForElementVisible($newEntrySelector2);
        $I->expectTo('my new post beeing the latest entry');
        $I->see('This is my second stream test post', '.s2_streamContent div:nth-child(1)');

        $I->amGoingTo('stick my first entry');
        $I->click('.preferences', $newEntrySelector);
        $I->waitForText('Stick', 10);
        $I->click('Stick', $newEntrySelector);
        $I->seeSuccess('The content has been sticked.');

        $I->see('This is my first stream test post!', '.s2_streamContent div:nth-child(1)');
        $I->see('Sticked', $newEntrySelector);

        $I->amGoingTo('unstick my first entry');
        $I->click('.preferences', $newEntrySelector);
        $I->waitForText('Unstick', 10);
        $I->click('Unstick', $newEntrySelector);
        $I->seeSuccess('The content has been unsticked.');
        $I->see('This is my second stream test post!', '.s2_streamContent div:nth-child(1)');
        $I->dontSee('Sticked', $newEntrySelector);
    }

    public function testEditPost(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace2();
        $I->wantToTest('the edit post mechanism');
        $I->amGoingTo('create a new post and delete it afterwards');

        $I->createPost('This is my first stream test post!');

        $newEntrySelector = '[data-content-key="12"]';

        $I->waitForElementVisible($newEntrySelector);
        $I->see('This is my first stream test post', '.wall-entry');

        $I->amGoingTo('edit my new post');
        $I->click('.preferences', $newEntrySelector);
        $I->waitForText('Edit', 10);
        $I->click('Edit', $newEntrySelector);

        $I->waitForElementVisible($newEntrySelector . ' .content_edit', 20);
        $I->fillField($newEntrySelector . ' [contenteditable]', 'This is my edited post!');
        $I->click('Save', $newEntrySelector);
        ;

        $I->seeSuccess('Saved');
        $I->seeElement($newEntrySelector);
        $I->see('This is my edited post!', $newEntrySelector);
    }
    
    public function testEmptyStream(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace3();
        $I->wantToTest('the empty stream message and filter');
        
        $I->waitForText('This space is still empty!');
        $I->dontSeeElement('#filter');
        
        $I->amGoingTo('create a new post and delete it afterwards');
        
        $I->createPost('This is my first stream test post!');

        $I->wait(1);    
        
        $I->amGoingTo('Delete my new post again.');
        $I->dontSee('This space is still empty!');
        $I->seeElement('#filter');
        $I->click('.preferences', '[data-stream-entry]:nth-of-type(1)');
        $I->wait(1);
        $I->click('Delete');
        
        $I->waitForElementVisible('#globalModalConfirm', 5);
        $I->see('Do you really want to perform this action?');
        $I->click('Confirm');

        $I->seeSuccess('The content has been deleted');
        $I->see('This space is still empty!');
        $I->dontSeeElement('#filter');
    }

    public function testSortStream(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace3();
        $I->wantToTest('the stream entry ordering');
        $I->amGoingTo('create a new post and delete it afterwards');

        $I->createPost('POST1');
        $I->createPost('POST2');
        $I->createPost('POST3');
        $I->createPost('POST4');
        $I->createPost('POST5');

        $I->see('POST5', '.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->see('POST4', '.s2_streamContent > [data-stream-entry]:nth-of-type(2)');
        $I->see('POST3', '.s2_streamContent > [data-stream-entry]:nth-of-type(3)');
        $I->see('POST2', '.s2_streamContent > [data-stream-entry]:nth-of-type(4)');
        $I->see('POST1', '.s2_streamContent > [data-stream-entry]:nth-of-type(5)');

        $post4Selector = '[data-stream-entry][data-content-key="18"]';

        $I->click('Comment', $post4Selector);
        $I->fillField($post4Selector . ' [contenteditable]', 'My Comment!');
        $I->click('Send', $post4Selector . ' .comment-buttons');
        
        $I->scrollTop();
        
        $I->click('.stream-sorting', '#filter');
        $I->waitForElementVisible('#sorting_u');
        $I->click('#sorting_u');
        $I->wait(2);
        $I->waitForElementVisible($post4Selector);
        
        $I->wait(20);
        
        $I->see('POST4', '.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->see('POST5', '.s2_streamContent > [data-stream-entry]:nth-of-type(2)');
        $I->see('POST3', '.s2_streamContent > [data-stream-entry]:nth-of-type(3)');
        $I->see('POST2', '.s2_streamContent > [data-stream-entry]:nth-of-type(4)');
        $I->see('POST1', '.s2_streamContent > [data-stream-entry]:nth-of-type(5)');
    }

    // Filtering
    // multi click logic
    // empty form
    // Poll
}
