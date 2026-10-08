<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\modules\admin\jobs\DisableModuleJob;
use humhub\modules\admin\jobs\RemoveModuleJob;
use humhub\modules\queue\driver\Instant;
use humhub\modules\queue\helpers\QueueHelper;
use humhub\modules\queue\models\QueueExclusive;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\queue\Queue;

/**
 * Tests for the per-request caching of Module::getIsEnabled()
 *
 * @since 1.18.7
 */
class ModuleIsEnabledTest extends HumHubDbTestCase
{
    private const MODULE_ID = 'like';

    private $originalQueue;

    protected function setUp(): void
    {
        parent::setUp();

        // The Instant queue reports every job as done, emulate a queue with waiting jobs
        $this->originalQueue = Yii::$app->get('queue');
        Yii::$app->set('queue', new class extends Instant {
            public function status($id)
            {
                return Queue::STATUS_WAITING;
            }
        });

        QueueExclusive::deleteAll();
        Yii::$app->runtimeCache->flush();
    }

    protected function tearDown(): void
    {
        QueueExclusive::deleteAll();
        Yii::$app->runtimeCache->flush();
        Yii::$app->set('queue', $this->originalQueue);

        parent::tearDown();
    }

    public function testResultIsCachedPerRequest()
    {
        $module = Yii::$app->getModule(self::MODULE_ID);
        $this->assertTrue($module->getIsEnabled());

        // Change the DB state directly, bypassing all cache invalidation
        (new QueueExclusive(['id' => 'module.' . self::MODULE_ID . '.disable', 'job_message_id' => 1]))->save(false);
        $this->assertTrue($module->getIsEnabled(), 'The cached result must be used');

        Yii::$app->moduleManager->flushCache();
        $this->assertFalse($module->getIsEnabled(), 'ModuleManager::flushCache() must invalidate the cached result');
    }

    public function testCacheIsInvalidatedOnQueuedDisableJob()
    {
        $module = Yii::$app->getModule(self::MODULE_ID);
        $this->assertTrue($module->getIsEnabled());

        QueueHelper::markAsQueued(1, new DisableModuleJob(['moduleId' => self::MODULE_ID]));
        $this->assertFalse($module->getIsEnabled());
    }

    public function testCacheIsInvalidatedOnQueuedRemoveJob()
    {
        $module = Yii::$app->getModule(self::MODULE_ID);
        $this->assertTrue($module->getIsEnabled());

        QueueHelper::markAsQueued(1, new RemoveModuleJob(['moduleId' => self::MODULE_ID]));
        $this->assertFalse($module->getIsEnabled());
    }
}
