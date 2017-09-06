<?php

namespace admin\functional;

use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\SeeAdminInformation;
use tests\codeception\_pages\AdminPage;
use admin\FunctionalTester;

class PermissionCest
{

    public function testSeeAdminInformation(FunctionalTester $I)
    {
        $I->wantTo('ensure that see admin information permission works');

        $I->amUser2();
        $I->amGoingTo('try to access admin page without any permissions');

        AdminPage::openBy($I);
        $I->expectTo('see permission denied message');
        $I->see('You are not permitted to access this section.');



        $I->amOnPage(['/admin/information']);
        $I->expectTo('see permission denied message');
        $I->see('You are not permitted to access this section.');

        $I->setGroupPermission(3, new SeeAdminInformation());
        
        AdminPage::openBy($I);
        $I->expect('not to see permission denied message');
        $I->dontSee('You are not permitted to access this section.');

        $I->dontSee('Users', '#admin-menu');
        $I->dontSee('Spaces', '#admin-menu');
        $I->dontSee('Modules', '#admin-menu');
        $I->dontSee('Settings', '#admin-menu');
        $I->see('Information', '#admin-menu');

        $I->amOnPage(['/admin/information']);
        $I->expect('not to see permission denied message');
        $I->dontSee('You are not permitted to access this section.');
    }

    public function testCanManageUsers(FunctionalTester $I)
    {
        $I->wantTo('ensure that the manage users permission works as expected');

        $I->amUser2();

        $I->amOnPage(['/admin/user']);
        $I->expectTo('see permission denied message');
        $I->see('You are not permitted to access this section.');

        $I->setGroupPermission(3, new \humhub\modules\admin\permissions\ManageUsers());

        $I->amOnPage(['/admin/user']);
        $I->expectTo('not to see permission denied message');
        $I->see('Add new user');
        $I->see('Profiles');

        $I->see('Users', '#admin-menu');
        $I->dontSee('Spaces', '#admin-menu');
        $I->dontSee('Modules', '#admin-menu');
        $I->dontSee('Settings', '#admin-menu');
        $I->dontSee('Information', '#admin-menu');

        $I->amOnPage(['/admin/user/edit', 'id' => 1]);
        $I->expectTo('see edit user profile view');
        $I->see('User administration');
        $I->dontSee('Groups');
        $I->dontSee('You are not permitted to access this section.');

        $I->amOnPage(['/admin/user-profile']);
        $I->see('Add new category');

        $I->amOnPage(['/admin/authentication']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/group']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/space']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/module']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/setting']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/information']);
        $I->see('You are not permitted to access this section.');
    }

    public function testCanManageGroups(FunctionalTester $I)
    {
        $I->wantTo('ensure that see manage groups permission works');

        $I->amUser2();

        $I->amOnPage(['/admin/user']);
        $I->expectTo('see permission denied message');
        $I->see('You are not permitted to access this section.');

        $I->setGroupPermission(3, new \humhub\modules\admin\permissions\ManageGroups());

        $I->amOnPage(['/admin/user']);
        $I->expectTo('not to see permission denied message');
        $I->see('Add new user');
        $I->see('Groups');

        $I->see('Users', '#admin-menu');
        $I->dontSee('Spaces', '#admin-menu');
        $I->dontSee('Modules', '#admin-menu');
        $I->dontSee('Settings', '#admin-menu');
        $I->dontSee('Information', '#admin-menu');

        $I->amOnPage(['/admin/user/edit', 'id' => 1]);
        $I->expectTo('see edit user profile view');
        $I->see('User administration');
        $I->see('Groups');
        $I->dontSee('You are not permitted to access this section.');

        $I->amOnPage(['/admin/group']);
        $I->dontSee('You are not permitted to access this section.');

        $I->amOnPage(['/admin/user-profile']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/authentication']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/space']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/module']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/setting']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/information']);
        $I->see('You are not permitted to access this section.');
    }

    public function testCanManageSettings(FunctionalTester $I)
    {
        $I->wantTo('ensure that see manage groups permission works');

        $I->amUser2();

        $I->amOnPage(['/admin/setting']);
        $I->expectTo('see permission denied message');
        $I->see('You are not permitted to access this section.');

        $I->setGroupPermission(3, new \humhub\modules\admin\permissions\ManageSettings());

        $I->amOnPage(['/admin/user']);
        $I->expectTo('not to see permission denied message');
        $I->dontSee('Groups');
        $I->see('User Settings');

        $I->see('Users', '#admin-menu');
        $I->see('Spaces', '#admin-menu');
        $I->dontSee('Modules', '#admin-menu');
        $I->see('Settings', '#admin-menu');
        $I->dontSee('Information', '#admin-menu');

        $I->amOnPage(['/admin/group']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/user-profile']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/authentication']);
        $I->dontSee('You are not permitted to access this section.');

        $I->amOnPage(['/admin/space']);
        $I->dontSee('You are not permitted to access this section.');
        $I->see('Space Settings');

        $I->amOnPage(['/admin/module']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/setting']);
        $I->dontSee('You are not permitted to access this section.');
        $I->see('General Settings');
        $I->see('Appearance');
        $I->see('E-Mail summaries');
        $I->see('Notifications');
        $I->see('Advanced');
        $I->see('General');

        $I->amOnPage(['/admin/setting/design']);
        $I->dontSee('You are not permitted to access this section.');
        $I->see('Appearance Settings');

        $I->amOnPage(['/notification/admin/defaults']);
        $I->dontSee('You are not permitted to access this section.');
        $I->see('Notification Settings');
        
        $I->amOnPage(['/activity/admin/defaults']);
        $I->dontSee('You are not permitted to access this section.');
        $I->see('E-Mail Summaries');

        $I->amOnPage(['/admin/setting/caching']);
        $I->dontSee('You are not permitted to access this section.');
        $I->see('Advanced Settings');

        $I->amOnPage(['/admin/information']);
        $I->see('You are not permitted to access this section.');
    }

    public function testCanManageModules(FunctionalTester $I)
    {
        $I->wantTo('ensure that see manage groups permission works');

        $I->amUser2();

        $I->amOnPage(['/admin/module']);
        $I->expectTo('see permission denied message');
        $I->see('You are not permitted to access this section.');

        $I->setGroupPermission(3, new ManageModules());

        $I->amOnPage(['/admin/module']);
        $I->expectTo('not to see permission denied message');
        $I->see('Modules directory');

        $I->dontSee('Users', '#admin-menu');
        $I->dontSee('Spaces', '#admin-menu');
        $I->see('Modules', '#admin-menu');
        $I->dontSee('Settings', '#admin-menu');
        $I->dontSee('Information', '#admin-menu');

        $I->amOnPage(['/admin/user']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/user/edit', 'id' => 1]);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/group']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/user-profile']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/authentication']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/space']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/setting']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/information']);
        $I->see('You are not permitted to access this section.');
    }

    public function testCanManageSpaces(FunctionalTester $I)
    {
        $I->wantTo('ensure that see manage groups permission works');

        $I->amUser2();

        $I->amOnPage(['/admin/space']);
        $I->expectTo('see permission denied message');
        $I->see('You are not permitted to access this section.');

        $I->setGroupPermission(3, new ManageSpaces());

        $I->amOnPage(['/admin/space']);
        $I->expectTo('see permission denied message');
        $I->dontSee('You are not permitted to access this section.');
        $I->see('Manage spaces');
        $I->dontSee('Users', '#admin-menu');
        $I->see('Spaces', '#admin-menu');
        $I->dontSee('Modules', '#admin-menu');
        $I->dontSee('Settings', '#admin-menu');
        $I->dontSee('Information', '#admin-menu');

        $I->amOnPage(['/space/manage', 'sguid' => '5396d499-20d6-4233-800b-c6c86e5fa34c']);
        $I->see('Space settings');
        $I->see('Basic');
        $I->see('Advanced');

        $I->amOnPage(['/admin/user']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/user/edit', 'id' => 1]);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/module']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/group']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/user-profile']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/authentication']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/setting']);
        $I->see('You are not permitted to access this section.');

        $I->amOnPage(['/admin/information']);
        $I->see('You are not permitted to access this section.');
    }

}
