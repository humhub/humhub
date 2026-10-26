<?php

namespace tests\codeception\unit;

use humhub\modules\ui\helpers\models\ItemDrop;
use humhub\modules\user\models\Group;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ItemDropTest extends HumHubDbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([1, 2, 3, 4] as $sortOrder => $id) {
            Group::updateAll(['sort_order' => $sortOrder], ['id' => $id]);
        }
    }

    public function testMoveItem()
    {
        $itemDrop = new GroupItemDrop(['id' => 4, 'index' => 0]);
        $transactionLevel = $this->getTransactionLevel();

        $this->assertTrue($itemDrop->save());
        $this->assertEquals([4, 1, 2, 3], $this->getGroupIdsBySortOrder());
        $this->assertEquals($transactionLevel, $this->getTransactionLevel());
    }

    public function testMoveItemToSameIndex()
    {
        $itemDrop = new GroupItemDrop(['id' => 2, 'index' => 1]);
        $transactionLevel = $this->getTransactionLevel();

        $this->assertTrue($itemDrop->save());
        $this->assertEquals([1, 2, 3, 4], $this->getGroupIdsBySortOrder());
        $this->assertEquals($transactionLevel, $this->getTransactionLevel());
    }

    private function getTransactionLevel(): int
    {
        return Yii::$app->db->getTransaction()?->level ?? 0;
    }

    private function getGroupIdsBySortOrder(): array
    {
        return array_map('intval', Group::find()
            ->where(['id' => [1, 2, 3, 4]])
            ->orderBy(['sort_order' => SORT_ASC])
            ->select('id')
            ->column());
    }
}

class GroupItemDrop extends ItemDrop
{
    public $modelClass = Group::class;
}
