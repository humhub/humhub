<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The rows of the administration's module table (`humhub\modules\admin\controllers\api\ModuleController`,
 * `GET /api/v2/admin/module`): the installed modules, a row's id the module's id.
 */
class AdminModuleApiCest
{
    public function testListsTheInstalledModules(ApiTester $I)
    {
        $I->wantTo('list the installed modules');
        $I->amLoggedInAs(1);
        $modules = Yii::$app->moduleManager->getModules();

        $I->sendGet('admin/module', ['pageSize' => 100]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['total' => count($modules)]);
        $ids = $I->grabDataFromResponseByJsonPath('$.results[*].id');
        Assert::assertEqualsCanonicalizing(array_slice(array_keys($modules), 0, 100), $ids);
    }

    public function testFiltersAndSorts(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        $disabled = array_keys(array_filter(Yii::$app->moduleManager->getModules(), static fn($module) => !$module->getIsEnabled()));

        $I->sendGet('admin/module', ['status' => 'disabled', 'pageSize' => 100]);
        $I->seeResponseContainsJson(['total' => count($disabled)]);
        foreach ($I->grabDataFromResponseByJsonPath('$.results[*].cells.status[0].value') as $status) {
            Assert::assertSame('disabled', $status);
        }

        $I->sendGet('admin/module', ['sort' => 'name', 'pageSize' => 100]);
        $names = $I->grabDataFromResponseByJsonPath('$.results[*].cells.name.text');
        $sorted = $names;
        usort($sorted, 'strnatcasecmp');
        Assert::assertSame($sorted, $names);

        $I->sendGet('admin/module', ['status' => 'unknown']);
        $I->seeResponseCodeIs(422);
    }

    public function testRequiresTheModuleAdministration(ApiTester $I)
    {
        $I->amLoggedInAs(2);
        $I->sendGet('admin/module');
        $I->seeResponseCodeIs(403);
    }
}
