<?php

namespace tests\codeception\unit\modules\space;


use humhub\modules\admin\notifications\ExcludeGroupNotification;
use humhub\modules\admin\notifications\IncludeGroupNotification;
use humhub\modules\admin\notifications\php;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class GroupsChangesTest extends HumHubDbTestCase
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testAddUserToGroupNotify()
    {
        /** @var Group $group */
        $group = Group::findOne(['id' => 1]);

        $notify = IncludeGroupNotification::instance();

        $notify
            ->about($group)
            ->from(User::findOne(['id' => 1]))
            ->send(User::findOne(['id' => 2]));

        $this->assertSentEmail(1);
        $this->assertEqualsLastEmailSubject($notify->getMailSubject());
    }

    public function testRemoveUserToGroupNotify()
    {
        $group = Group::findOne(['id' => 1]);
        $notify = ExcludeGroupNotification::instance();

        $notify
            ->about($group)
            ->from(User::findOne(['id' => 1]))
            ->send(User::findOne(['id' => 2]));

        $this->assertSentEmail(1);
        $this->assertEqualsLastEmailSubject($notify->getMailSubject());
    }
}
