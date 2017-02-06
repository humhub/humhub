<?php
namespace user\acceptance;

use user\AcceptanceTester;

class UserFollowCest
{
    public function testBaseAccountSettings(AcceptanceTester $I)
    {
        $I->wantTo('test the user follow by directory link');
        
        
        $I->amUser1();
        //$I->wait(300);
        $I->amOnProfile();
        
        $I->createPost('New User1 profile post');
        
        $I->amUser2(true);
        
        $I->amOnDashboard();
        $I->waitForElementVisible('.wall-entry');
        
        $I->dontSee('New User1 profile post', '.wall-entry');
        
        $I->amOnUser1Profile();
        
        $I->see('Follow', '[data-content-container-id="2"].followButton');
        
        $I->click('[data-content-container-id="2"].followButton');
        
        $I->waitForElementVisible('[data-content-container-id="2"].unfollowButton');
        
        $I->amOnDashboard();
        
        $I->waitForElementVisible('.wall-entry');

        $I->see('New User1 profile post', '.wall-entry');
                
    }
}