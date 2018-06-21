<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace enterprise\acceptance\modules\emailwhitelist;

use Yii;
use humhub\modules\space\models\Space;
use FunctionalTester;

class DeleteSpaceCest
{
    
    public function testOwnerDeletion(FunctionalTester $I)
    {
        $I->wantTo('ensure the owner of the space is able to delete the space');
        $I->amUser();
        $space = $this->createSpace();
        $I->amOnRoute('/space/manage/default/delete', ['sguid' => $space->guid]);
        $I->canSeeResponseCodeIs(200);
    }

    public function testMemberDeletion(FunctionalTester $I)
    {
        $I->wantTo('ensure a member of the space is not able to delete the space');
        $I->amUser1();
        // User1 is member of Space3
        $I->amOnRoute('/space/manage/default/delete', ['sguid' =>'5396d499-20d6-4233-800b-c6c86e5fa34c']);
        $I->canSeeResponseCodeIs(403);
    }

    public function testSystemAdminDeletion(FunctionalTester $I)
    {
        $I->wantTo('ensure a system admin is able to delete the space');
        $I->amAdmin();
        // User1 is member of Space3
        $I->amOnRoute('/space/manage/default/delete', ['sguid' =>'5396d499-20d6-4233-800b-c6c86e5fa34c']);
        $I->canSeeResponseCodeIs(200);
    }

    public function testAdminDeletion(FunctionalTester $I)
    {
        $I->wantTo('ensure a simple space admin is not able to delete the space');
        $I->amUser1();
        // User1 is admin of Space4
        $I->amOnRoute('/space/manage/default/delete', ['sguid' =>'5396d499-20d6-4233-800b-c6c86e5fa34d']);
        $I->canSeeResponseCodeIs(403);
    }
    
    private function createSpace()
    {
        $space = new Space([
            'name' => 'DeleteSpaceTest'
        ]);

        $space->created_by = Yii::$app->user->getId();
        $space->save();

        return $space;
    }


}