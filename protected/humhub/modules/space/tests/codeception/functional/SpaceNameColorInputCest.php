<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\tests\codeception\functional;

use FunctionalTester;

/**
 * The colour and the name of a space share one row: the colour field is named by its label, the name field by the
 * one above the row only
 */
class SpaceNameColorInputCest
{
    public function testEachFieldHasOneLabel(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnSpace(1, '/space/manage/default/index');

        $I->seeNumberOfElements('label[for="space-color"]', 1);
        $I->seeNumberOfElements('label[for="space-name"]', 1);
        $I->seeElement('.input-color-group input#space-color[type="color"]');
    }
}
