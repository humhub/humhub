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


class ManageAccessCest
{
    public function testSpaceAccessManage(FunctionalTester $I)
    {
        $I->assertSpaceAccessFalse(Space::USERGROUP_MEMBER, '/space/manage');
        $I->assertSpaceAccessFalse(Space::USERGROUP_USER, '/space/manage');
        $I->assertSpaceAccessFalse(Space::USERGROUP_MODERATOR, '/space/manage');
        $I->assertSpaceAccessTrue(Space::USERGROUP_ADMIN, '/space/manage');
        $I->assertSpaceAccessTrue(Space::USERGROUP_OWNER, '/space/manage');
    }
}