<?php
namespace content\acceptance;

use content\AcceptanceTester;
use humhub\modules\post\models\Post;

class ReadableCest
{
    public function testProfileArchivedContents(AcceptanceTester $I)
    {
        $I->wantTo('ensure that private content is not readable.');

        $spacePostMessage = 'User 2 Space 2 Post Private';
        $spacePostUrl = Post::findOne(['message' => $spacePostMessage])->getUrl();

        $profilePostMessage = 'User 2 Profile Post Private';
        $profilePostUrl = Post::findOne(['message' => $profilePostMessage])->getUrl();

        $I->amGoingTo('check a private post is readable by member.');
        $I->amUser1();
        $I->amOnPage($spacePostUrl);
        $I->waitForText($spacePostMessage, null, '.wall-entry-content');
        $I->amOnPage($profilePostUrl);
        $I->waitForText($profilePostMessage, null, '.wall-entry-content');

        $I->amGoingTo('check a private post is not readable by non-member.');
        $I->amUser2(true);
        $I->amOnPage($spacePostUrl);
        $I->waitForText('Access denied');
        $I->see('You do not have permission to access this content, as it is reserved for members of this Space. Please become a member or apply for membership. The available options for membership will depend on the Space\'s settings.', '.layout-content-container');

        $I->amGoingTo('check a private post is not readable by another user.');
        $I->amOnPage($profilePostUrl);
        $I->waitForText('Access denied');
        $I->see('You do not have permission to access this content. The user has marked it as private.', '.layout-content-container');

        $I->amGoingTo('check error message for not existing content.');
        $wrongPostUrl = $spacePostUrl. '999';
        $I->amOnPage($wrongPostUrl);
        $I->waitForText('The requested content cannot be displayed. Either it was deleted, you mistyped it or it is currently not available for you.', null, '.panel-body');
    }
}
