<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\InstallationState;
use humhub\components\SettingsManager;
use tests\codeception\_support\HumHubDbTestCase;
use Throwable;
use Yii;
use yii\caching\DummyCache;
use yii\db\Connection;

/**
 * @since 1.19
 */
class InstallationStateTest extends HumHubDbTestCase
{
    /**
     * On a healthy, installed system the state resolves to INSTALLED and the
     * database is never flagged as unreachable.
     */
    public function testHealthyDatabaseIsNotUnreachable()
    {
        $state = InstallationState::instance(true);

        $this->assertFalse($state->isDatabaseUnreachable());
        $this->assertNull($state->getDatabaseConnectionError());
        $this->assertTrue($state->hasState(InstallationState::STATE_INSTALLED));
    }

    /**
     * A configured but unreachable database must be detected as such and must
     * NOT fall back to a lower state (which would present the installer for an
     * already installed instance during a transient outage).
     */
    public function testConfiguredButUnreachableDatabaseDoesNotFallBackToInstaller()
    {
        $originalDb = Yii::$app->get('db');
        $originalCache = Yii::$app->get('cache');
        $originalSettings = Yii::$app->get('settings');

        try {
            // No persistent cache -> the settings manager cannot resolve the
            // stored state and is forced to probe the (dead) database.
            Yii::$app->set('cache', ['class' => DummyCache::class]);
            Yii::$app->set('db', [
                'class' => Connection::class,
                'dsn' => 'mysql:host=127.0.0.1;port=1;dbname=humhub_unreachable',
                'username' => 'humhub',
                'password' => 'humhub',
            ]);
            // Recreate the settings manager so its cached values are dropped and
            // the (failing) load runs against the dead database.
            Yii::$app->set('settings', ['class' => SettingsManager::class, 'moduleId' => 'base']);

            $state = InstallationState::instance(true);

            $this->assertTrue($state->isDatabaseUnreachable());
            $this->assertInstanceOf(Throwable::class, $state->getDatabaseConnectionError());
            $this->assertFalse($state->hasState(InstallationState::STATE_INSTALLED));
        } finally {
            Yii::$app->set('db', $originalDb);
            Yii::$app->set('cache', $originalCache);
            Yii::$app->set('settings', $originalSettings);
            InstallationState::instance(true);
        }
    }
}
