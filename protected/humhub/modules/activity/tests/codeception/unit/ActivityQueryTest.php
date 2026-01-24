<?php

namespace activity\unit;

use Codeception\Specify;
use humhub\modules\activity\models\Activity;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\activity\tests\codeception\activities\TestGroupActivity;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;

class ActivityQueryTest extends HumHubDbTestCase
{
    use Specify;


    public function testTimebucket()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        ActivityManager::dispatch(TestGroupActivity::class, $post);
        $lastActivity = Activity::find()->where(['activity.class' => TestGroupActivity::class])->orderBy(
            'created_at DESC',
        )->one();
        $lastActivity->updateAttributes(
            [
                'created_at' => (new \DateTimeImmutable($lastActivity->created_at))->modify('-1 month')->format(
                    'Y-m-d H:i:s',
                ),
            ],
        );

        ActivityManager::dispatch(TestGroupActivity::class, $post);
        ActivityManager::dispatch(TestGroupActivity::class, $post);
        ActivityManager::dispatch(TestGroupActivity::class, $post);
        ActivityManager::dispatch(TestGroupActivity::class, $post);
        // Except a group of 4 not 5 TestGroupActivities
    }
}
