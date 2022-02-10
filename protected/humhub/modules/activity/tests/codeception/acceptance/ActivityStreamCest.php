<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace stream\acceptance;

use activity\AcceptanceTester;

class ActivityStreamCest
{
    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testSimpleStream(AcceptanceTester $I)
    {
        $I->amUser1();
        $I->amOnSpace3();
        $I->wantToTest('test post creation activity');

        $I->amGoingTo('create a new post');

        $newEntrySelector = '[data-content-key="15"]';

        $I->createPost('Activity test post!');

        $I->waitForText('Activity test post!', null,'.wall-entry');


        $I->wantToTest('that i don\'t see my own activity in the activity stream');

        $I->amOnSpace2();

        $I->waitForText('There are no activities yet.', null, '#activityStream');


        $I->wantToTest('that another user see my activity in the activity stream');
        $I->amUser2(true);

        $I->amOnSpace3();

        $I->waitForElementVisible('.activity-entry');
        $I->see('Peter Tester created a new post "Activity test post!"', '#activityStream');
        $I->click('.activity-entry');

        $I->waitForText('Activity test post!', null,'.wall-entry');

        $I->wantToTest('deleting my post will remove the activity');
        $I->amUser1(true);

        $I->amOnSpace3();

        $I->waitForText('Activity test post!', null,'.wall-entry');

        $I->click('.preferences .dropdown-toggle', $newEntrySelector);
        $I->wait(1);
        $I->click('Delete',$newEntrySelector);

        $I->waitForElementVisible('#globalModalConfirm', 5);
        $I->see('Confirm post deletion');
        $I->click('Delete', '#globalModalConfirm');

        $I->waitForElementNotVisible($newEntrySelector);

        $I->amUser2(true);
        $I->waitForText('There are no activities yet.', null, '#activityStream');
    }
}
