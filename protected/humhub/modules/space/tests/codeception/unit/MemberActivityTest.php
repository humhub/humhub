<?php

namespace tests\codeception\unit\modules\space;

use humhub\modules\activity\models\Activity;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\activities\MemberAddedActivity;
use humhub\modules\space\activities\MemberRemovedActivity;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use ReflectionClass;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class MemberActivityTest extends HumHubDbTestCase
{
    public function testMemberAddedInSpaceContextLinksToMemberProfile()
    {
        $this->becomeUser('Admin');

        $space = Space::findOne(['id' => 2]);
        $user = User::findOne(['id' => 2]);

        $activity = ActivityManager::dispatch(MemberAddedActivity::class, $space, $user);

        // Viewing the space's own activity stream: the space link is redundant.
        $this->enterSpaceContext($space);

        $this->assertSame(1, $activity->groupCount);
        $this->assertSame($user->getUrl(false), $activity->getUrl(false));
    }

    public function testMemberRemovedInSpaceContextLinksToMemberProfile()
    {
        $this->becomeUser('Admin');

        $space = Space::findOne(['id' => 2]);
        $user = User::findOne(['id' => 2]);

        $activity = ActivityManager::dispatch(MemberRemovedActivity::class, $space, $user);

        $this->enterSpaceContext($space);

        $this->assertSame(1, $activity->groupCount);
        $this->assertSame($user->getUrl(false), $activity->getUrl(false));
    }

    public function testMemberAddedOutsideSpaceLinksToSpace()
    {
        $this->becomeUser('Admin');

        $space = Space::findOne(['id' => 2]);
        $user = User::findOne(['id' => 2]);

        $activity = ActivityManager::dispatch(MemberAddedActivity::class, $space, $user);

        // Global/dashboard stream (no space context): the entry highlights the
        // space ("joined the Space {spaceName}"), so it keeps the space link.
        $this->assertFalse(Yii::$app->controller instanceof ContentContainerController);
        $this->assertSame(1, $activity->groupCount);
        $this->assertSame($space->getUrl(false), $activity->getUrl(false));
    }

    public function testGroupedMemberAddedLinksToSpaceEvenInSpaceContext()
    {
        $this->becomeUser('Admin');

        $space = Space::findOne(['id' => 2]);

        // Two members joining within the same time bucket are grouped, so the
        // entry references multiple users and must keep the space as link
        // target — even when viewed inside the space.
        ActivityManager::dispatch(MemberAddedActivity::class, $space, User::findOne(['id' => 2]));
        ActivityManager::dispatch(MemberAddedActivity::class, $space, User::findOne(['id' => 3]));

        $record = Activity::find()
            ->enableGrouping()
            ->andWhere(['activity.class' => MemberAddedActivity::class])
            ->andWhere(['activity.contentcontainer_id' => $space->contentContainerRecord->id])
            ->orderBy(['activity.grouping_key' => SORT_DESC])
            ->one();

        $activity = ActivityManager::load($record);

        $this->enterSpaceContext($space);

        $this->assertSame(2, $activity->groupCount);
        $this->assertSame($space->getUrl(false), $activity->getUrl(false));
    }

    /**
     * Simulates running inside a content container controller (e.g. the space
     * activity stream) so that BaseSpaceActivity::inSpaceContext() returns true.
     */
    private function enterSpaceContext(Space $space): void
    {
        $controller = (new ReflectionClass(ContentContainerController::class))
            ->newInstanceWithoutConstructor();
        $controller->contentContainer = $space;
        Yii::$app->controller = $controller;
    }
}
