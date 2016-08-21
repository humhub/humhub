<?php
namespace dashboard\acceptance;


use dashboard\AcceptanceTester;

class DashboardCest
{
    
    public function testDashboardInputBar(AcceptanceTester $I)
    {
        $I->amAdmin();
        $I->wantToTest('the dashboard input bar is working');
        $I->amGoingTo('activate the input bar');
        $I->amOnPage('index-test.php?r=admin/setting/basic');
        
        $I->jsClick('#basicsettingsform-dashboardshowprofilepostform'); // Active
        $I->click('Save');
       
        $I->amOnPage('index-test.php?r=dashboard/dashboard');
        $I->expectTo('see the dashboard input');
        $I->seeElement('#contentForm_message_contenteditable');
        
        $I->fillField('#contentForm_message_contenteditable', 'My Test Post');
        $I->click('#post_submit_button');
        $I->wait(6);
        
        $I->expectTo('see my newly created post');
        $I->see('My Test Post', '.wall-entry');
    }
   
}