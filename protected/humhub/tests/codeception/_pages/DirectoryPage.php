<?php

namespace tests\codeception\_pages;

use yii\codeception\BasePage;

/**
 * Represents login page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class DirectoryPage extends BasePage
{

    public $route = 'directory/directory';
    
    public function clickMembers()
    {
        $this->actor->click('Member');
        $this->actor->waitForText('Member directory', 30);
    }

}
