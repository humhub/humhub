<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\tests\codeception\unit;

use humhub\modules\content\components\ContentContainerModuleManager;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\content\models\ContentContainerModuleState;
use humhub\modules\content\models\ContentContainerPermission;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ContentContainerModuleManagerTest extends HumHubDbTestCase
{
    private const MODULE_ID = 'testContainerModule';

    protected function setUp(): void
    {
        parent::setUp();

        Yii::$app->moduleManager->markAsEnabled(self::MODULE_ID);
        Yii::$app->moduleManager->register(__DIR__, [
            'id' => self::MODULE_ID,
            'class' => TestContainerModule::class,
        ]);
    }

    public function testDefaultEnabledExcludesStoredDisabled()
    {
        $this->setDefaults(ContentContainerModuleState::STATE_ENABLED, ContentContainerModuleState::STATE_ENABLED);
        $space = Space::findOne(['id' => 1]);
        $this->storeState($space, ContentContainerModuleState::STATE_DISABLED);

        $ids = $this->queryIds();

        $this->assertNotContains($space->contentcontainer_id, $ids);
        $this->assertContains(Space::findOne(['id' => 2])->contentcontainer_id, $ids);
        $this->assertFalse($this->isEnabled($space));
    }

    public function testForceEnabledDefaultFollowsStoredDisabled()
    {
        $this->setDefaults(ContentContainerModuleState::STATE_FORCE_ENABLED, ContentContainerModuleState::STATE_FORCE_ENABLED);
        $space = Space::findOne(['id' => 1]);
        $this->storeState($space, ContentContainerModuleState::STATE_DISABLED);

        // The stored state wins over the default, also over "always activated"
        $this->assertFalse($this->isEnabled($space));
        $this->assertNotContains($space->contentcontainer_id, $this->queryIds());
    }

    public function testNotAvailableDefaultExcludesStaleStoredEnabled()
    {
        $this->setDefaults(ContentContainerModuleState::STATE_NOT_AVAILABLE, ContentContainerModuleState::STATE_ENABLED);
        $space = Space::findOne(['id' => 1]);
        $this->storeState($space, ContentContainerModuleState::STATE_ENABLED);

        $this->assertFalse($this->isEnabled($space));
        $this->assertNotContains($space->contentcontainer_id, $this->queryIds());
    }

    public function testUnsupportedContainerTypeIsExcluded()
    {
        Yii::$app->getModule(self::MODULE_ID)->containerTypes = [Space::class];
        $this->setDefaults(ContentContainerModuleState::STATE_ENABLED, ContentContainerModuleState::STATE_ENABLED);
        $user = User::findOne(['id' => 1]);
        $this->storeState($user, ContentContainerModuleState::STATE_ENABLED);

        $classes = ContentContainer::find()
            ->where(['id' => $this->queryIds()])
            ->select('class')->distinct()->column();

        $this->assertSame([Space::class], $classes);
        $this->assertFalse($this->isEnabled($user));
    }

    public function testDefaultDisabledIncludesStoredEnabled()
    {
        $this->setDefaults(ContentContainerModuleState::STATE_DISABLED, ContentContainerModuleState::STATE_DISABLED);
        $space = Space::findOne(['id' => 1]);
        $this->storeState($space, ContentContainerModuleState::STATE_ENABLED);

        $this->assertSame([$space->contentcontainer_id], $this->queryIds());
    }

    public function testDefaultEnabledIncludesContainerWithoutStoredState()
    {
        $this->setDefaults(ContentContainerModuleState::STATE_ENABLED, null);

        $ids = $this->queryIds();

        $this->assertContains(Space::findOne(['id' => 1])->contentcontainer_id, $ids);
        $this->assertNotContains(User::findOne(['id' => 1])->contentcontainer_id, $ids);
    }

    public function testDisableModuleRemovesPermissionsOfSkippedContainers()
    {
        $this->setDefaults(ContentContainerModuleState::STATE_NOT_AVAILABLE, ContentContainerModuleState::STATE_ENABLED);
        $space = Space::findOne(['id' => 1]);
        $user = User::findOne(['id' => 1]);

        foreach ([$space, $user] as $container) {
            $permission = new ContentContainerPermission([
                'permission_id' => 'test_permission',
                'contentcontainer_id' => $container->contentcontainer_id,
                'group_id' => 'member',
                'module_id' => self::MODULE_ID,
                'class' => 'TestPermission',
                'state' => 1,
            ]);
            $this->assertTrue($permission->save());
        }

        // The space is not part of the query, the user is
        $ids = $this->queryIds();
        $this->assertNotContains($space->contentcontainer_id, $ids);
        $this->assertContains($user->contentcontainer_id, $ids);

        Yii::$app->getModule(self::MODULE_ID)->disable();

        $this->assertSame(0, (int)ContentContainerPermission::find()->where(['module_id' => self::MODULE_ID])->count());
    }

    /**
     * The query matches what each container's module manager reports, for every combination of
     * supported types, defaults and stored states.
     */
    public function testQueryMatchesGetEnabledForEveryContainer()
    {
        $defaults = [
            null,
            ContentContainerModuleState::STATE_DISABLED,
            ContentContainerModuleState::STATE_ENABLED,
            ContentContainerModuleState::STATE_FORCE_ENABLED,
            ContentContainerModuleState::STATE_NOT_AVAILABLE,
        ];
        $storedStates = [
            null,
            ContentContainerModuleState::STATE_DISABLED,
            ContentContainerModuleState::STATE_ENABLED,
            ContentContainerModuleState::STATE_FORCE_ENABLED,
        ];
        $typeSets = [[Space::class, User::class], [Space::class], [User::class], []];

        // Every stored state (and none) for each container class
        $containers = ContentContainer::find()->orderBy('id')->all();
        $perClass = [];
        foreach ($containers as $container) {
            $i = $perClass[$container->class] = ($perClass[$container->class] ?? -1) + 1;
            $this->storeState($container->getPolymorphicRelation(), $storedStates[$i % count($storedStates)]);
        }

        foreach ($typeSets as $types) {
            Yii::$app->getModule(self::MODULE_ID)->containerTypes = $types;
            foreach ($defaults as $spaceDefault) {
                foreach ($defaults as $userDefault) {
                    $this->setDefaults($spaceDefault, $userDefault);

                    $expected = [];
                    foreach ($containers as $container) {
                        if ($this->isEnabled($container->getPolymorphicRelation())) {
                            $expected[] = $container->id;
                        }
                    }

                    $this->assertSame(
                        $expected,
                        $this->queryIds(),
                        sprintf('types [%s], space default %s, user default %s', implode(', ', $types), var_export($spaceDefault, true), var_export($userDefault, true)),
                    );
                }
            }
        }
    }

    private function setDefaults(?int $spaceDefault, ?int $userDefault): void
    {
        $settings = Yii::$app->getModule(self::MODULE_ID)->settings;
        foreach (['Space' => $spaceDefault, 'User' => $userDefault] as $shortName => $state) {
            if ($state === null) {
                $settings->delete('moduleManager.defaultState.' . $shortName);
            } else {
                $settings->set('moduleManager.defaultState.' . $shortName, $state);
            }
        }
    }

    private function storeState($container, ?int $state): void
    {
        ContentContainerModuleState::deleteAll([
            'module_id' => self::MODULE_ID,
            'contentcontainer_id' => $container->contentcontainer_id,
        ]);

        if ($state !== null) {
            $record = new ContentContainerModuleState([
                'module_id' => self::MODULE_ID,
                'contentcontainer_id' => $container->contentcontainer_id,
                'module_state' => $state,
            ]);
            $this->assertTrue($record->save());
        }
    }

    private function isEnabled($container): bool
    {
        $manager = new ContentContainerModuleManager(['contentContainer' => $container]);

        return in_array(self::MODULE_ID, $manager->getEnabled(), true);
    }

    /**
     * @return int[] the sorted ids of the containers the query returns
     */
    private function queryIds(): array
    {
        $ids = array_map('intval', ContentContainerModuleManager::getContentContainerQueryByModule(self::MODULE_ID)
            ->select('contentcontainer.id')->column());
        sort($ids);

        return $ids;
    }
}
