<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\tests\codeception\functional;

use FunctionalTester;
use humhub\modules\space\models\Space;
use PHPUnit\Framework\Assert;

/**
 * The caller's own membership transitions have one way, `POST`/`DELETE /api/v2/space/<id>/membership`:
 * the web actions the membership button island made redundant are gone (see
 * docs/develop/module-migrate-1.20.md), and the header menu's "Cancel Membership" entry calls
 * the endpoint through the `space.leave` client action.
 */
class MembershipWebActionsCest
{
    public function testTheWebMembershipActionsAreGone(FunctionalTester $I)
    {
        $I->wantTo('confirm the membership transitions have one way, the API');

        foreach (['request-membership', 'invite-accept', 'revoke-membership'] as $action) {
            $I->assertSpaceAccessStatus(Space::USERGROUP_MEMBER, 404, '/space/membership/' . $action, [], true);
        }
    }

    public function testTheHeaderMenuLeavesThroughTheApi(FunctionalTester $I)
    {
        $I->wantTo('see the header menu end a membership through the API endpoint');

        $space = $I->loginBySpaceUserGroup(Space::USERGROUP_MEMBER, '/space/space');

        $I->seeElement('membership-button');
        $I->see('Cancel Membership');
        $I->seeElement('a[data-action-click="space.leave"]');
        Assert::assertStringEndsWith(
            'api/v2/space/' . $space->id . '/membership',
            $I->grabAttributeFrom('a[data-action-click="space.leave"]', 'data-action-url'),
        );
    }
}
