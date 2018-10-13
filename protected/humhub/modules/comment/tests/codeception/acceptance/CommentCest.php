<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace comment\acceptance;

use comment\AcceptanceTester;

class CommentCest
{
    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testCreateComment(AcceptanceTester $I)
    {
        $I->amUser1();
        $I->amOnSpace2();
        $I->waitForText('Admin Space 2 Post Private');

        $postEntry = '.wall_humhubmodulespostmodelsPost_13';
        $commentSection  = $postEntry.' .comment-container';

        $I->click('Comment', $postEntry);
        $I->wait(1);

        $I->click('.btn-comment-submit', $commentSection);

        $I->seeError('The comment must not be empty!');

        $I->fillField($commentSection.' .humhub-ui-richtext[contenteditable]', 'Test comment');

        $I->click('.btn-comment-submit', $commentSection);

        $I->waitForElementVisible('#comment-message-1');
        $I->see('Test comment','#comment-message-1');
    }
}
