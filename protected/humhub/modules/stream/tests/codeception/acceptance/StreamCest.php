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

        $I->createPost('This is my stream test post!');

        $newEntrySelector = '[data-content-key="12"]';

        $I->waitForElementVisible($newEntrySelector);
        $I->see('This is my stream test post', '.wall-entry');

        $I->amGoingTo('Delte my new post');
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->wait(1);
        $I->click('Delete');

        $I->waitForElementVisible('#globalModalConfirm', 5);
        $I->see('Confirm post deletion');
        $I->click('Delete', '#globalModalConfirm');

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
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
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

        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->waitForText('Unarchive', 10);
        $I->click('Unarchive', $newEntrySelector);

        $I->expectTo('See my unarchived post again');
        $I->seeSuccess('The content has been unarchived.');
        $I->see('This is my stream test post', '.wall-entry');
        $I->dontSee('Archived', $newEntrySelector);

        $I->amGoingTo('archive the post again with include archived filter');
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->waitForText('Move to archive', 10);
        $I->click('Move to archive', $newEntrySelector);
        $I->seeSuccess('The content has been archived.');
        $I->see('This is my stream test post', '.wall-entry');
        $I->see('Archived', $newEntrySelector);
    }

    public function testPinPost(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace2();
        $I->wantToTest('the pin of posts');
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

        $I->amGoingTo('pin my first entry');
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->waitForText('Pin', 10);
        $I->click('Pin', $newEntrySelector);
        $I->seeSuccess('The content has been pinned.');

        $I->see('This is my first stream test post!', '.s2_streamContent div:nth-child(1)');
        $I->see('Pinned', $newEntrySelector);

        $I->amGoingTo('unpin my first entry');
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->waitForText('Unpin', 10);
        $I->click('Unpin', $newEntrySelector);
        $I->seeSuccess('The content has been unpinned.');
        $I->see('This is my second stream test post!', '.s2_streamContent div:nth-child(1)');
        $I->dontSee('Pinned', $newEntrySelector);
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

        $I->amGoingTo('edit load the edit form');
        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->waitForText('Edit', 10);
        $I->click('Edit', $newEntrySelector);
        
        $I->waitForElementVisible($newEntrySelector . ' .content_edit', 20);
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
        $I->click('Save', $newEntrySelector);
        
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
        $I->click('.preferences .dropdown-toggle', '[data-stream-entry]:nth-of-type(1)');
        $I->wait(1);
        $I->click('Delete');
        
        $I->waitForElementVisible('#globalModalConfirm', 5);
        $I->see('Confirm post deletion');
        $I->click('Delete', '#globalModalConfirm');

        $I->seeSuccess('The content has been deleted');
        $I->see('This space is still empty!');
        $I->dontSeeElement('#filter');
    }
    
    public function testFilterInvolved(AcceptanceTester $I)
    {
        $I->amUser();
        $I->amOnSpace2();
        $I->waitForElementVisible('#filter');
        $I->click('.stream-filter', '#filter');
        $I->waitForElementVisible('#filter_entry_userinvolved');
        $I->click('#filter_entry_userinvolved');
        $I->waitForText('No matches with your selected filters!');
        
        $I->createPost('Involved Post.');
        $I->dontSee('No matches with your selected filters!');
        
        $I->amGoingTo('Reset filter');
        $I->click('.stream-filter', '#filter');
        $I->waitForElementVisible('#filter_entry_userinvolved');
        $I->click('#filter_entry_userinvolved');
        
        $I->waitForElementVisible('[data-content-key="10"]');
        
        $I->click('Comment', '[data-content-key="10"]');
        $I->waitForElementVisible('#newCommentForm_humhubmodulespostmodelsPost_10');
        $I->fillField('#newCommentForm_humhubmodulespostmodelsPost_10', 'My Comment');
        $I->click('Send', '#comment_create_form_humhubmodulespostmodelsPost_10');
        $I->waitForText('My Comment', 30, '#comment_humhubmodulespostmodelsPost_10 .comment');
        
        $I->click('Like', '[data-content-key="11"]');
        
        $I->click('.stream-filter', '#filter');
        $I->waitForElementVisible('#filter_entry_userinvolved');
        $I->click('#filter_entry_userinvolved');
        $I->wait(1);
        $I->waitForText('Involved Post.');

        $I->seeElement('[data-content-key="10"]');
        $I->seeElement('[data-content-key="11"]');
        $I->seeElement('[data-content-key="12"]');
    }

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
