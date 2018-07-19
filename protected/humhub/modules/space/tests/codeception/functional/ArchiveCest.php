<?php
/**
 * Created by PhpStorm.
 * User: kingb
 * Date: 19.07.2018
 * Time: 21:30
 */

namespace humhub\modules\space\tests\codeception\functional;

use FunctionalTester;
use humhub\modules\space\models\Space;


class ArchiveCest
{
    public function testSpaceArchiveAccess(FunctionalTester $I)
    {
        $I->assertSpaceAccessFalse(Space::USERGROUP_MEMBER, '/space/manage/default/archive');
        $I->assertSpaceAccessFalse(Space::USERGROUP_USER, '/space/manage/default/archive');
        $I->assertSpaceAccessFalse(Space::USERGROUP_MODERATOR, '/space/manage/default/archive');
        $I->assertSpaceAccessFalse(Space::USERGROUP_ADMIN, '/space/manage/default/archive');
        $I->assertSpaceAccessFalse(Space::USERGROUP_OWNER, '/space/manage/default/archive');
        $I->assertSpaceAccessTrue(Space::USERGROUP_OWNER, '/space/manage/default/archive', true);
    }

    public function testSpaceArchiveSpace(FunctionalTester $I)
    {
        $space = $I->loginBySpaceUserGroup(Space::USERGROUP_OWNER);
        $I->amOnSpace($space, '/space/manage/default/archive', true);
        $I->amOnSpace($space);
        $I->see('Archived');
    }
}