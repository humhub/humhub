<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\notification\models\Notification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ActiveQueryNotificationTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    /**
     * @var array<int, int> the ids of the 18 rows of user 2 by their number 1-18, created one
     * minute apart from 2014-08-08 05:36:05, each its own group
     */
    private array $ids = [];

    /**
     * The rows are inserted here rather than by a fixture: Codeception loads the fixtures once
     * per suite run, so a fixture of this class would leak into every later test class.
     */
    protected function _before()
    {
        parent::_before();

        $maxId = (int)Notification::find()->max('id');
        $rows = [];
        for ($i = 1; $i <= 18; $i++) {
            $rows[] = [TestNotification::class, 2, 1, 1, date('Y-m-d H:i:s', strtotime('2014-08-08 05:35:05') + 60 * $i)];
        }
        Yii::$app->db->createCommand()
            ->batchInsert('notification', ['class', 'user_id', 'priority', 'listed', 'created_at'], $rows)
            ->execute();
        Yii::$app->db->createCommand('UPDATE notification SET grouping_key = id WHERE grouping_key IS NULL')->execute();

        $ids = array_map('intval', Notification::find()->select('id')->where(['>', 'id', $maxId])->orderBy('id')->column());
        $this->assertCount(18, $ids);
        $this->ids = array_combine(range(1, 18), $ids);
    }

    /**
     * @return int[]
     */
    private function ids(int ...$numbers): array
    {
        return array_map(fn(int $n): int => $this->ids[$n], $numbers);
    }

    public function testGroupedListOfUser2()
    {
        $query = Notification::find()->forUser(User::findOne(['id' => 2]))->listed()->grouped();
        $records = $query->limit(6)->all();
        $this->assertCount(6, $records);
        $this->assertSame($this->ids[18], (int)$records[0]->id);
        $this->assertSame(1, $records[0]->group_count);
        $this->assertSame($this->ids[18], $records[0]->group_max_id);
        // cursor paging over the grouping key
        $page2 = (clone $query)->andWhere(['<', 'notification.grouping_key', end($records)->grouping_key])->limit(6)->all();
        $this->assertSame($this->ids[12], (int)$page2[0]->id);
    }

    public function testGroupedRowCarriesTheGroupSizeAndNewestId()
    {
        Notification::updateAll(['grouping_key' => $this->ids[18]], ['id' => $this->ids(16, 17, 18)]);
        $records = Notification::find()->forUser(2)->listed()->grouped()->limit(1)->all();
        $this->assertSame(3, $records[0]->group_count);
        $this->assertSame($this->ids[18], $records[0]->group_max_id);
        $this->assertSame(16, Notification::find()->forUser(2)->listed()->grouped()->count());
    }

    public function testUnseenCountsGroupsOnce()
    {
        Notification::updateAll(['grouping_key' => $this->ids[18]], ['id' => $this->ids(16, 17, 18)]);
        Notification::updateAll(['seen_at' => '2014-08-08 06:00:00'], ['id' => $this->ids(1, 2)]);
        // 16 groups, two of them seen
        $this->assertSame(14, Notification::find()->forUser(2)->listed()->unseen()->grouped()->count());
        // a group with one unseen member is unseen
        Notification::updateAll(['seen_at' => '2014-08-08 06:00:00'], ['id' => $this->ids(16, 17)]);
        $this->assertSame(14, Notification::find()->forUser(2)->listed()->unseen()->grouped()->count());
        Notification::updateAll(['seen_at' => '2014-08-08 06:00:00'], ['id' => $this->ids(18)]);
        $this->assertSame(13, Notification::find()->forUser(2)->listed()->unseen()->grouped()->count());
        $this->assertSame(3, Notification::find()->forUser(2)->listed()->seen()->grouped()->count());
    }

    public function testSeenFilterIndependentOfCallOrder()
    {
        Notification::updateAll(['grouping_key' => $this->ids[18]], ['id' => $this->ids(16, 17, 18)]);
        Notification::updateAll(['seen_at' => '2014-08-08 06:00:00'], ['id' => $this->ids(1, 2, 16, 17)]);
        // groups: grouped() before unseen()/seen() gives the same result as after
        $this->assertSame(14, Notification::find()->forUser(2)->grouped()->unseen()->count());
        $this->assertSame(2, Notification::find()->forUser(2)->grouped()->seen()->count());
        // the partly seen group keeps all of its members
        $records = Notification::find()->forUser(2)->grouped()->unseen()->limit(1)->all();
        $this->assertSame(3, $records[0]->group_count);
        // single rows
        $this->assertSame(14, Notification::find()->forUser(2)->unseen()->count());
        $this->assertSame(4, Notification::find()->forUser(2)->seen()->count());
        // building the query twice does not stack the filter
        $query = Notification::find()->forUser(2)->unseen();
        $this->assertSame(14, $query->count());
        $this->assertCount(14, $query->all());
    }

    public function testGroupedRowCarriesGroupUnseen()
    {
        Notification::updateAll(['grouping_key' => $this->ids[18]], ['id' => $this->ids(16, 17, 18)]);
        Notification::updateAll(['seen_at' => '2014-08-08 06:00:00'], ['id' => $this->ids(15, 16, 17)]);
        $records = Notification::find()->forUser(2)->listed()->grouped()->limit(2)->all();
        // group of 18: 16 and 17 seen, 18 unseen
        $this->assertSame($this->ids[18], $records[0]->group_max_id);
        $this->assertSame(1, $records[0]->group_unseen);
        // group of 15: fully seen
        $this->assertSame($this->ids[15], $records[1]->group_max_id);
        $this->assertSame(0, $records[1]->group_unseen);

        Notification::updateAll(['seen_at' => '2014-08-08 06:00:00'], ['id' => $this->ids(18)]);
        $head = Notification::find()->forUser(2)->listed()->grouped()->unseen()->one();
        $this->assertSame($this->ids[14], $head->group_max_id);
        $this->assertSame(1, $head->group_unseen);
    }

    public function testListedFiltersUnlistedRows()
    {
        Notification::updateAll(['listed' => 0], ['id' => $this->ids(1, 2, 3)]);
        $this->assertSame(15, Notification::find()->forUser(2)->listed()->grouped()->count());
    }

    public function testTimeBucket()
    {
        // the 900 s bucket containing 05:40:05 is 05:30:00 ≤ created_at < 05:45:00: rows 1–9
        $this->assertSame(9, Notification::find()->forUser(2)->timeBucket(900, '2014-08-08 05:40:05')->count());
    }
}
