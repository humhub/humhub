<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\space\models\Space;
use PHPUnit\Framework\Assert;

/**
 * The rows of the administration's space table (`humhub\modules\admin\controllers\api\SpaceController`,
 * `GET /api/v2/admin/space`): every space, whatever its visibility.
 */
class AdminSpaceApiCest
{
    private function ids(ApiTester $I): array
    {
        return $I->grabDataFromResponseByJsonPath('$.results[*].id');
    }

    public function testListsEverySpace(ApiTester $I)
    {
        $I->wantTo('list every space, also the private ones');
        $I->amLoggedInAs(1);

        $I->sendGet('admin/space');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['total' => (int)Space::find()->count()]);
        $space = Space::findOne(['name' => 'Space 1']);
        $I->seeResponseContainsJson(['results' => [[
            'id' => $space->id,
            'cells' => ['name' => ['text' => 'Space 1']],
        ]]]);
        Assert::assertSame(
            ['edit', 'members', 'owner', 'modules', 'open', 'delete'],
            $I->grabDataFromResponseByJsonPath('$.results[0].actions[*].id'),
        );
    }

    public function testFiltersAndSorts(ApiTester $I)
    {
        $I->amLoggedInAs(1);

        $I->sendGet('admin/space', ['visibility' => 'private']);
        Assert::assertEqualsCanonicalizing(
            Space::find()->select('id')->where(['visibility' => Space::VISIBILITY_NONE])->column(),
            $this->ids($I),
        );

        $I->sendGet('admin/space', ['q' => 'Space 2']);
        Assert::assertSame([(int)Space::findOne(['name' => 'Space 2'])->id], $this->ids($I));

        $I->sendGet('admin/space', ['sort' => '-name']);
        Assert::assertSame(
            array_map('intval', Space::find()->select('id')->orderBy(['name' => SORT_DESC])->column()),
            $this->ids($I),
        );

        $I->sendGet('admin/space', ['status' => 'deleted']);
        $I->seeResponseCodeIs(422);
    }

    public function testRequiresTheSpaceAdministration(ApiTester $I)
    {
        $I->amLoggedInAs(2);
        $I->sendGet('admin/space');
        $I->seeResponseCodeIs(403);
    }
}
