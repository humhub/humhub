<?php

namespace tests\codeception\_pages;

use yii\codeception\BasePage;

/**
 * Represents login page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class ProfilePage extends BasePage
{

    public $route = 'user/account';

    public function clickAbout()
    {
        $this->actor->click('About');
        $this->actor->waitForText('About this user', 30);
    }

}
