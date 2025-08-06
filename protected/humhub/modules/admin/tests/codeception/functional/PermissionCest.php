<?php

namespace admin\functional;

use admin\FunctionalTester;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\admin\permissions\SeeAdminInformation;
use tests\codeception\_pages\AdminPage;

class PermissionCest
{
    public function testSeeAdminInformation(FunctionalTester $I)
    {
        $I->wantTo('ensure that see admin information permission works');

        $I->amUser2();
        $I->amGoingTo('try to access admin page without any permissions');

        AdminPage::openBy($I);
        $I->expectTo('see permission denied message');
        $I->seeResponseCodeIs(403);

        $I->amOnRoute('/admin/information');
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

        $I->amOnRoute('/admin/information');
        $I->expect('not to see permission denied message');
        $I->dontSee('You are not permitted to access this section.');
    }

    public function testCanManageUsers(FunctionalTester $I)
    {
        $I->wantTo('ensure that the manage users permission works as expected');

        $I->amUser2();

        $I->amOnRoute('/admin/user');
        $I->expectTo('see permission denied message');
        $I->see('You are not permitted to access this section.');

        $I->setGroupPermission(3, new ManageUsers());

        $I->amOnRoute('/admin/user');
        $I->expectTo('not to see permission denied message');
        $I->see('Add new user');
        $I->see('Profiles');

        $I->see('Users', '#admin-menu');
        $I->dontSee('Spaces', '#admin-menu');
        $I->dontSee('Modules', '#admin-menu');
        $I->dontSee('Settings', '#admin-menu');
        $I->dontSee('Information', '#admin-menu');

        $I->amOnRoute('/admin/user/edit', ['id' => 1]);
        $I->expectTo('see edit user profile view');
        $I->see('User administration');
        $I->dontSee('Groups');
        $I->dontSee('You are not permitted to access this section.');

        $I->amOnRoute('/admin/user-profile');
        $I->see('Add new field');

        $I->amOnRoute('/admin/authentication');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/group');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/space');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/module');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/setting');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/information');
        $I->see('You are not permitted to access this section.');
    }

    public function testCanManageGroups(FunctionalTester $I)
    {
        $I->wantTo('ensure that see manage groups permission works');

        $I->amUser2();

        $I->amOnRoute('/admin/user');
        $I->expectTo('see permission denied message');
        $I->see('You are not permitted to access this section.');

        $I->setGroupPermission(3, new ManageGroups());

        $I->amOnRoute('/admin/user');
        $I->expectTo('not to see permission denied message');
        $I->see('Add new user');
        $I->see('Groups');

        $I->see('Users', '#admin-menu');
        $I->dontSee('Spaces', '#admin-menu');
        $I->dontSee('Modules', '#admin-menu');
        $I->dontSee('Settings', '#admin-menu');
        $I->dontSee('Information', '#admin-menu');

        $I->amOnRoute('/admin/user/edit', ['id' => 1]);
        $I->expectTo('see edit user profile view');
        $I->see('User administration');
        $I->see('Groups');
        $I->dontSee('You are not permitted to access this section.');

        $I->amOnRoute('/admin/group');
        $I->dontSee('You are not permitted to access this section.');

        $I->amOnRoute('/admin/user-profile');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/authentication');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/space');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/module');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/setting');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/information');
        $I->see('You are not permitted to access this section.');
    }

    public function testCanManageSettings(FunctionalTester $I)
    {
        $I->wantTo('ensure that see manage settings permission works');

        $I->amUser2();

        $I->amOnRoute('/admin/setting');
        $I->expectTo('see permission denied message');
        $I->see('You are not permitted to access this section.');

        $I->setGroupPermission(3, new ManageSettings());

        $I->amOnRoute('/admin/user');
        $I->expectTo('not to see permission denied message');
        $I->dontSee('Groups');
        $I->see('User Settings');

        $I->see('Users', '#admin-menu');
        $I->see('Spaces', '#admin-menu');
        $I->see('Modules', '#admin-menu');
        $I->see('Settings', '#admin-menu');
        $I->dontSee('Information', '#admin-menu');

        $I->amOnRoute('/admin/group');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/user-profile');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/authentication');
        $I->dontSee('You are not permitted to access this section.');

        $I->amOnRoute('/admin/space');
        $I->dontSee('You are not permitted to access this section.');
        $I->see('Space Settings');

        $I->amOnRoute('/admin/module');
        $I->expectTo('not to see permission denied message');
        $I->see('Module administration');
        $I->see('You do not have the permission to manage modules.');

        $I->amOnRoute('/admin/setting');
        $I->dontSee('You are not permitted to access this section.');
        $I->see('General Settings');
        $I->see('Appearance');
        $I->see('E-Mail summaries');
        $I->see('Notifications');
        $I->see('Advanced');
        $I->see('General');

        $I->amOnRoute('/admin/setting/design');
        $I->dontSee('You are not permitted to access this section.');
        $I->see('Appearance Settings');

        $I->amOnRoute('/notification/admin/defaults');
        $I->dontSee('You are not permitted to access this section.');
        $I->see('Notification Settings');

        $I->amOnRoute('/activity/admin/defaults');
        $I->dontSee('You are not permitted to access this section.');
        $I->see('E-Mail Summaries');

        $I->amOnRoute('/admin/setting/caching');
        $I->dontSee('You are not permitted to access this section.');
        $I->see('Advanced Settings');

        $I->amOnRoute('/admin/information');
        $I->see('You are not permitted to access this section.');
    }

    public function testCanManageModules(FunctionalTester $I)
    {
        $I->wantTo('ensure that see manage modules permission works');

        $I->amUser2();

        $I->amOnRoute('/admin/module');
        $I->expectTo('see permission denied message');
        $I->see('You are not permitted to access this section.');

        $I->setGroupPermission(3, new ManageModules());

        $I->amOnRoute('/admin/module');
        $I->expectTo('not to see permission denied message');
        $I->see('Module administration');
        $I->see('You do not have the permission to configure modules.');

        $I->amOnRoute('/marketplace/browse');
        $I->see('Marketplace');
        $I->dontSeeElement('#admin-menu');

        $I->amOnRoute('/admin/user');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/user/edit', ['id' => 1]);
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/group');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/user-profile');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/authentication');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/space');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/setting');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/information');
        $I->see('You are not permitted to access this section.');
    }

    public function testCanManageSpaces(FunctionalTester $I)
    {
        $I->wantTo('ensure that see manage spaces permission works');

        $I->amUser2();

        $I->amOnRoute('/admin/space');
        $I->expectTo('see permission denied message');
        $I->see('You are not permitted to access this section.');

        $I->setGroupPermission(3, new ManageSpaces());

        $I->amOnRoute('/admin/space');
        $I->expectTo('see permission denied message');
        $I->dontSee('You are not permitted to access this section.');
        $I->see('Manage spaces');
        $I->dontSee('Users', '#admin-menu');
        $I->see('Spaces', '#admin-menu');
        $I->dontSee('Modules', '#admin-menu');
        $I->dontSee('Settings', '#admin-menu');
        $I->dontSee('Information', '#admin-menu');

        $I->amOnRoute('/space/manage', ['sguid' => '5396d499-20d6-4233-800b-c6c86e5fa34c']);
        $I->see('Space settings');
        $I->see('Basic');
        $I->see('Advanced');

        $I->amOnRoute('/admin/user');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/user/edit', ['id' => 1]);
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/module');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/group');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/user-profile');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/authentication');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/setting');
        $I->see('You are not permitted to access this section.');

        $I->amOnRoute('/admin/information');
        $I->see('You are not permitted to access this section.');
    }

}
