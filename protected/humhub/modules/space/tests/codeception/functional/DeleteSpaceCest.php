<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace enterprise\acceptance\modules\emailwhitelist;

use humhub\modules\space\models\Space;
use FunctionalTester;

class DeleteSpaceCest
{
    public function testSpaceDeleteAccess(FunctionalTester $I)
    {
        $I->assertSpaceAccessFalse(Space::USERGROUP_MEMBER, '/space/manage/default/delete');
        $I->assertSpaceAccessFalse(Space::USERGROUP_USER, '/space/manage/default/delete');
        $I->assertSpaceAccessFalse(Space::USERGROUP_MODERATOR, '/space/manage/default/delete');
        $I->assertSpaceAccessFalse(Space::USERGROUP_ADMIN, '/space/manage/default/delete');
        $I->assertSpaceAccessTrue(Space::USERGROUP_OWNER, '/space/manage/default/delete');
        $space = $I->assertSpaceAccessStatus(Space::USERGROUP_OWNER, 302, '/space/manage/default/delete', [], ['DeleteForm[currentPassword]' => '123qwe']);
        $I->amOnSpace($space);
        $I->seeResponseCodeIs(404);
    }

    public function testSystemAdminDeletion(FunctionalTester $I)
    {
        $I->assertSpaceAccessTrue('root', '/space/manage/default/delete', [],  ['DeleteForm[currentPassword]' => 'test']);
    }
}
