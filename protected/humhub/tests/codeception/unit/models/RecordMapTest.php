<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\models;

use humhub\models\RecordMap;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\log\Logger;

class RecordMapTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    public function testManyRecordsGetTheirIdsInOneGo()
    {
        $spaces = Space::find()->orderBy('id')->limit(3)->all();
        $user = User::findOne(['id' => 1]);
        $known = RecordMap::getId($spaces[0]);
        Yii::$app->runtimeCache->flush();

        $ids = RecordMap::getIds(['first' => $spaces[0], 'second' => $spaces[1], 'third' => $spaces[2], 'user' => $user]);

        $this->assertSame(['first', 'second', 'third', 'user'], array_keys($ids));
        $this->assertSame($known, $ids['first']);
        $this->assertCount(4, array_unique($ids));

        // getId() asks no more for them
        $this->assertSame(0, $this->queriesOf(function () use ($spaces, $user, $ids) {
            $this->assertSame($ids['second'], RecordMap::getId($spaces[1]));
            $this->assertSame($ids['user'], RecordMap::getId($user));
        }));
    }

    public function testTheQueriesDoNotGrowWithTheRecords()
    {
        $spaces = Space::find()->orderBy('id')->all();
        RecordMap::getIds($spaces);
        Yii::$app->runtimeCache->flush();

        $one = $this->queriesOf(fn() => RecordMap::getIds(array_slice($spaces, 0, 1)));
        Yii::$app->runtimeCache->flush();
        $all = $this->queriesOf(fn() => RecordMap::getIds($spaces));

        $this->assertSame($one, $all);
    }

    private function queriesOf(callable $call): int
    {
        $logger = Yii::getLogger();
        Yii::setLogger(new Logger(['flushInterval' => 0]));
        try {
            $call();
            return count(Yii::getLogger()->getProfiling(['yii\db\Command::query', 'yii\db\Command::execute']));
        } finally {
            Yii::setLogger($logger);
        }
    }
}
