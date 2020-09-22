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


class ManageMembersCest
{
    public function testSpaceManageMembersAccess(FunctionalTester $I)
    {
        $I->assertSpaceAccessFalse(Space::USERGROUP_MEMBER, '/space/manage/member');
        $I->assertSpaceAccessFalse(Space::USERGROUP_USER, '/space/manage/member');
        $I->assertSpaceAccessFalse(Space::USERGROUP_MODERATOR, '/space/manage/member');
        $I->assertSpaceAccessTrue(Space::USERGROUP_ADMIN, '/space/manage/member');
        $I->assertSpaceAccessTrue(Space::USERGROUP_OWNER, '/space/manage/member');
    }

    public function testChangeOwnerAccess(FunctionalTester $I)
    {
        $I->assertSpaceAccessFalse(Space::USERGROUP_MEMBER, '/space/manage/member/change-owner');
        $I->assertSpaceAccessFalse(Space::USERGROUP_USER, '/space/manage/member/change-owner');
        $I->assertSpaceAccessFalse(Space::USERGROUP_MODERATOR, '/space/manage/member/change-owner');
        $I->assertSpaceAccessFalse(Space::USERGROUP_ADMIN, '/space/manage/member/change-owner');
        $I->assertSpaceAccessTrue(Space::USERGROUP_OWNER, '/space/manage/member/change-owner');

        $I->amAdmin();
        $I->amOnSpace4('/space/manage/member/change-owner', [], ['ChangeOwnerForm[ownerId]' => 2]);
        $I->seeSuccessResponseCode();

        $space = Space::findOne(4);

        if(!$space->ownerUser->id === 2) {
            $I->see('Change owner did not work');
        }
    }

    public function testApprovalAccess(FunctionalTester $I)
    {
        $I->assertSpaceAccessFalse(Space::USERGROUP_MEMBER, '/space/manage/member/pending-invitations');
        $I->assertSpaceAccessFalse(Space::USERGROUP_USER, '/space/manage/member/pending-invitations');
        $I->assertSpaceAccessFalse(Space::USERGROUP_MODERATOR, '/space/manage/member/pending-invitations');
        $I->assertSpaceAccessTrue(Space::USERGROUP_ADMIN, '/space/manage/member/pending-invitations');
        $I->assertSpaceAccessTrue(Space::USERGROUP_OWNER, '/space/manage/member/pending-invitations');

        $I->assertSpaceAccessFalse(Space::USERGROUP_MEMBER, '/space/manage/member/pending-approvals');
        $I->assertSpaceAccessFalse(Space::USERGROUP_USER, '/space/manage/member/pending-approvals');
        $I->assertSpaceAccessFalse(Space::USERGROUP_MODERATOR, '/space/manage/member/pending-approvals');
        $I->assertSpaceAccessTrue(Space::USERGROUP_ADMIN, '/space/manage/member/pending-approvals');
        $I->assertSpaceAccessTrue(Space::USERGROUP_OWNER, '/space/manage/member/pending-approvals');
    }
}