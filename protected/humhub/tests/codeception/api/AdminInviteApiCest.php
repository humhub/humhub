<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\user\models\Invite;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The pending registrations as rows of their table
 * (`humhub\modules\admin\controllers\api\InviteController`, `GET /api/v2/admin/invite`).
 *
 * The `user_invite` fixture is empty: the invitations are created in `_before()` and removed in
 * `_after()`.
 */
class AdminInviteApiCest
{
    /**
     * @var int[] by e-mail
     */
    private array $ids = [];

    public function _before(): void
    {
        foreach (['a@invite.test' => Invite::SOURCE_INVITE, 'b@invite.test' => Invite::SOURCE_SELF] as $email => $source) {
            $invite = new Invite(['email' => $email, 'source' => $source, 'user_originator_id' => 1]);
            $invite->save(false);
            $this->ids[$email] = (int)$invite->id;
        }
    }

    public function _after(): void
    {
        Invite::deleteAll(['id' => array_values($this->ids)]);
        $this->ids = [];
    }

    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    public function testListsAndFiltersTheInvitations(ApiTester $I)
    {
        $I->amLoggedInAs(1);

        $I->sendGet('admin/invite', ['q' => 'invite.test', 'sort' => 'email']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['a@invite.test', 'b@invite.test'], $I->grabDataFromResponseByJsonPath('$.results[*].cells.email.text'));

        $I->sendGet('admin/invite', ['q' => 'invite.test', 'source' => 'self']);
        Assert::assertSame(['b@invite.test'], $I->grabDataFromResponseByJsonPath('$.results[*].cells.email.text'));
    }

    public function testResendsAndDeletes(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('admin/invite/' . $this->ids['a@invite.test'] . '/resend');
        $I->seeResponseCodeIs(200);
        Assert::assertSame([$this->ids['a@invite.test']], $I->grabDataFromResponseByJsonPath('$.row.id'));

        $I->sendPost('admin/invite/bulk/delete', ['ids' => array_values($this->ids)]);
        $I->seeResponseContainsJson(['done' => 2]);
        Assert::assertSame(0, (int)Invite::find()->where(['id' => array_values($this->ids)])->count());
    }

    public function testRequiresTheUserAdministration(ApiTester $I)
    {
        $I->amLoggedInAs(4);
        $I->sendGet('admin/invite');
        $I->seeResponseCodeIs(403);
    }
}
