<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\tests\codeception\functional;

use FunctionalTester;
use humhub\modules\space\models\Space;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * Ensures the member role dropdown of the space member administration only assigns
 * roles a space admin is allowed to assign.
 *
 * Space 4 fixture: created_by = 1, so Admin is the space owner.
 * Memberships of space 4: user 1 (Admin) admin, user 2 (User1) admin, user 3 (User2) member.
 * User1 is therefore a space admin without being the space owner.
 */
class MemberRoleChangeCest
{
    private const SPACE_ID = 4;
    private const SPACE_ADMIN_ID = 2; // User1
    private const SPACE_OWNER_ID = 1; // Admin

    private function groupIdOf(int $userId, int $spaceId = self::SPACE_ID): ?string
    {
        return Yii::$app->db->createCommand(
            'SELECT group_id FROM space_membership WHERE space_id = :s AND user_id = :u',
            [':s' => $spaceId, ':u' => $userId],
        )->queryScalar() ?: null;
    }

    private function submitRoleChange(FunctionalTester $I, int $userId, array $membership): void
    {
        $I->amOnSpace(self::SPACE_ID, '/space/manage/member', [], [
            'dropDownColumnSubmit' => 1,
            'user_id' => $userId,
            'Membership' => $membership,
        ]);
    }

    public function testSpaceAdminCanAssignRegularRoles(FunctionalTester $I)
    {
        $I->wantTo('ensure a space admin can still change a member role through the dropdown');

        $I->amUser('User1');
        $this->submitRoleChange($I, 3, ['group_id' => Space::USERGROUP_MODERATOR]);
        $I->seeResponseCodeIs(200);

        Assert::assertSame(Space::USERGROUP_MODERATOR, $this->groupIdOf(3));
    }

    public function testOwnerRoleIsNotAssignable(FunctionalTester $I)
    {
        $I->wantTo('ensure the owner role cannot be assigned through the dropdown');

        $I->amUser('User1');
        $this->submitRoleChange($I, self::SPACE_ADMIN_ID, ['group_id' => Space::USERGROUP_OWNER]);

        Assert::assertSame(Space::USERGROUP_ADMIN, $this->groupIdOf(self::SPACE_ADMIN_ID));
    }

    public function testUnknownRoleIsNotAssignable(FunctionalTester $I)
    {
        $I->wantTo('ensure an unknown role cannot be assigned through the dropdown');

        $I->amUser('User1');
        $this->submitRoleChange($I, self::SPACE_ADMIN_ID, ['group_id' => 'notarole']);

        Assert::assertSame(Space::USERGROUP_ADMIN, $this->groupIdOf(self::SPACE_ADMIN_ID));
    }

    public function testMembershipCannotBeMovedToAnotherSpace(FunctionalTester $I)
    {
        $I->wantTo('ensure the dropdown cannot repoint a membership to another space or user');

        Assert::assertNull($this->groupIdOf(self::SPACE_ADMIN_ID, 1), 'precondition: no membership in space 1');

        $I->amUser('User1');
        $this->submitRoleChange($I, self::SPACE_ADMIN_ID, [
            'space_id' => 1,
            'user_id' => self::SPACE_ADMIN_ID,
            'group_id' => Space::USERGROUP_ADMIN,
        ]);

        Assert::assertNull($this->groupIdOf(self::SPACE_ADMIN_ID, 1), 'no membership must be created in space 1');
        Assert::assertSame(Space::USERGROUP_ADMIN, $this->groupIdOf(self::SPACE_ADMIN_ID));
    }

    public function testOwnerMembershipCannotBeChanged(FunctionalTester $I)
    {
        $I->wantTo('ensure the role of the space owner cannot be changed through the dropdown');

        $I->amUser('User1');
        $this->submitRoleChange($I, self::SPACE_OWNER_ID, ['group_id' => Space::USERGROUP_MEMBER]);
        $I->seeResponseCodeIs(403);

        Assert::assertSame(Space::USERGROUP_ADMIN, $this->groupIdOf(self::SPACE_OWNER_ID));
    }

    public function testStoredOwnerRoleDoesNotGrantOwnerActions(FunctionalTester $I)
    {
        $I->wantTo('ensure an already stored owner role does not pass owner only checks');

        Yii::$app->db->createCommand()
            ->update(
                'space_membership',
                ['group_id' => Space::USERGROUP_OWNER],
                ['space_id' => self::SPACE_ID, 'user_id' => self::SPACE_ADMIN_ID],
            )->execute();

        $I->amUser('User1');
        $I->amOnSpace(self::SPACE_ID, '/space/manage/member/change-owner');
        $I->dontSeeResponseCodeIs(200);
        $I->amOnSpace(self::SPACE_ID, '/space/manage/default/delete');
        $I->dontSeeResponseCodeIs(200);
    }

    public function testStoredUnknownRoleDoesNotGrantOwnerActions(FunctionalTester $I)
    {
        $I->wantTo('ensure an already stored unknown role does not pass owner only checks');

        Yii::$app->db->createCommand()
            ->update(
                'space_membership',
                ['group_id' => 'notarole'],
                ['space_id' => self::SPACE_ID, 'user_id' => self::SPACE_ADMIN_ID],
            )->execute();

        $I->amUser('User1');
        $I->amOnSpace(self::SPACE_ID, '/space/manage/member/change-owner');
        $I->dontSeeResponseCodeIs(200);
        $I->amOnSpace(self::SPACE_ID, '/space/manage/default/delete');
        $I->dontSeeResponseCodeIs(200);

        // The manipulated role must not keep the space admin rights either.
        $I->amOnSpace(self::SPACE_ID, '/space/manage/member');
        $I->dontSeeResponseCodeIs(200);
    }
}
