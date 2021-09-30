<?php

namespace tests\codeception\_pages;

use tests\codeception\_support\BasePage;

/**
 * Represents login page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class DirectoryPage extends BasePage
{

    public $route = 'user/people';

    public function clickMembers()
    {
        if($this->actor instanceof \AcceptanceTester) {
            $this->actor->waitForText('People', 30);
        }
    }

}
