<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\user;

use humhub\components\listing\ListEvent;
use humhub\modules\user\components\UserList;
use humhub\modules\user\models\User;
use humhub\modules\user\search\UserSearchProvider;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;

/**
 * The meta search's people: the directory's search ({@see UserList}, `purpose=directory`).
 *
 * @since 1.20
 */
class UserSearchProviderTest extends HumHubDbTestCase
{
    /**
     * @inheritdoc
     */
    protected $fixtureConfig = ['default'];

    public function testFindsUsersByKeywordWithTheirTotal(): void
    {
        $this->becomeUser('Admin');

        $provider = new UserSearchProvider();
        $provider->keyword = 'Sara';
        $result = $provider->getResults(5);

        $this->assertSame(1, $result['totalCount']);
        $this->assertSame(['Sara Tester'], array_map(static fn($record) => $record->getTitle(), $result['results']));
    }

    public function testLimitsTheResultsButNotTheTotal(): void
    {
        $this->becomeUser('Admin');

        $provider = new UserSearchProvider();
        $provider->keyword = '';
        $result = $provider->getResults(1);

        $this->assertCount(1, $result['results']);
        $this->assertSame((int)User::find()->available()->andWhere(['!=', 'visibility', User::VISIBILITY_HIDDEN])->count(), $result['totalCount']);
    }

    public function testAppliesTheDirectoryRestrictionsOfModules(): void
    {
        $this->becomeUser('Admin');
        $purposes = [];
        $handler = function (ListEvent $event) use (&$purposes) {
            $purposes[] = $event->context->purpose;
            $event->builder->query()->andWhere('0 = 1');
        };
        Event::on(UserList::class, UserList::EVENT_BUILD, $handler);

        try {
            $provider = new UserSearchProvider();
            $provider->keyword = '';
            $result = $provider->getResults(5);
        } finally {
            Event::off(UserList::class, UserList::EVENT_BUILD, $handler);
        }

        $this->assertSame([UserList::PURPOSE_DIRECTORY], $purposes);
        $this->assertSame(0, $result['totalCount']);
        $this->assertSame([], $result['results']);
    }
}
