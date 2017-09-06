<?php

namespace tests\codeception\_pages;

use tests\codeception\_support\BasePage;

/**
 * Represents login page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class DirectoryMemberPage extends BasePage
{

    public $route = 'directory/directory/members';

}
