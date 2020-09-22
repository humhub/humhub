<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace stream\acceptance;

use humhub\modules\content\models\Content;
use stream\AcceptanceTester;

class TopicCest
{
    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testDeletePost(AcceptanceTester $I)
    {
        $I->amUser();
        $I->wantToTest('the deletion of a stream entry');
        $I->amGoingTo('create a new post and delete it afterwards');
        $I->createTopics(2, ['Topic1', 'Topic2']);

        $I->amOnSpace2();

        $I->createPost('My Topic1 Post', 'Topic1');
        $I->see('My Topic1 Post', '.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->see('Topic1', '.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->see('Admin Space 2 Post Public', '[data-stream-entry]');

        $I->createPost('My Topic2 Post', 'Topic2');
        $I->see('My Topic2 Post', '.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->see('Topic2', '.s2_streamContent > [data-stream-entry]:nth-of-type(1)');

        $I->createPost('All Topics Post', ['Topic1', 'Topic2']);
        $I->see('All Topics Post', '.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->see('Topic1', '.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->see('Topic2', '.s2_streamContent > [data-stream-entry]:nth-of-type(1)');

        $I->click('Topic1', '.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->wait(1);
        $I->waitForElementVisible('.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->see('My Topic1 Post', '[data-stream-entry]');
        $I->see('All Topics Post', '[data-stream-entry]');
        $I->dontSee('My Topic2 Post', '[data-stream-entry]');
        $I->dontSee('Admin Space 2 Post Public', '[data-stream-entry]');

        $I->click('.topic-remove-label');
        $I->wait(1);
        $I->waitForElementVisible('.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->see('My Topic1 Post', '[data-stream-entry]');
        $I->see('All Topics Post', '[data-stream-entry]');
        $I->see('My Topic2 Post', '[data-stream-entry]');
        $I->see('Admin Space 2 Post Public', '[data-stream-entry]');
        $I->dontSee('Topic1', '.topic-remove-label');

        $I->click('Topic2', '.s2_streamContent > [data-stream-entry]:nth-of-type(2)');
        $I->wait(1);
        $I->waitForElementVisible('.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->dontSee('My Topic1 Post', '[data-stream-entry]');
        $I->see('All Topics Post', '[data-stream-entry]');
        $I->see('My Topic2 Post', '[data-stream-entry]');
        $I->dontSee('Admin Space 2 Post Public', '[data-stream-entry]');

        $I->click('Topic1', '.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->wait(1);
        $I->waitForElementVisible('.s2_streamContent > [data-stream-entry]:nth-of-type(1)');
        $I->dontSee('My Topic1 Post', '[data-stream-entry]');
        $I->see('All Topics Post', '[data-stream-entry]');
        $I->dontSee('My Topic2 Post', '[data-stream-entry]');
        $I->dontSee('Admin Space 2 Post Public', '[data-stream-entry]');
    }
}
