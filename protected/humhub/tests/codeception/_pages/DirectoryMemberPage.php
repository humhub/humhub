<?php

namespace tests\codeception\_pages;

use yii\codeception\BasePage;

/**
 * Represents login page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class DirectoryMemberPage extends BasePage
{

    public $route = 'directory/directory/members';

}
