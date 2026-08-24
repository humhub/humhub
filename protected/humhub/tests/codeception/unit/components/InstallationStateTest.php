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
     * A configured database whose SERVER is unreachable (connection refused) is
     * a real outage: it must NOT fall back to a lower state that presents the
     * installer for an already installed instance.
     */
    public function testUnreachableServerIsAnOutage()
    {
        $this->withDatabase(
            fn(Connection $db) => [
                'class' => Connection::class,
                'dsn' => 'mysql:host=127.0.0.1;port=1;dbname=humhub_unreachable',
                'username' => 'humhub',
                'password' => 'humhub',
            ],
            function (InstallationState $state) {
                $this->assertTrue($state->isDatabaseUnreachable());
                $this->assertInstanceOf(Throwable::class, $state->getDatabaseConnectionError());
                $this->assertFalse($state->hasState(InstallationState::STATE_INSTALLED));
            },
        );
    }

    /**
     * Server reachable but the database/schema does not exist yet (fresh Docker
     * install, MySQL 1049): not an outage — keep showing the installer, which
     * creates the database.
     */
    public function testMissingDatabaseIsNotUnreachable()
    {
        $this->withDatabase(
            fn(Connection $db) => [
                'class' => Connection::class,
                'dsn' => preg_replace('/dbname=[^;]*/', 'dbname=humhub_missing_db_test', $db->dsn),
                'username' => $db->username,
                'password' => $db->password,
                'charset' => $db->charset,
            ],
            function (InstallationState $state) {
                $this->assertFalse($state->isDatabaseUnreachable(), 'A missing database is not an outage');
                $this->assertNull($state->getDatabaseConnectionError());
                $this->assertFalse($state->hasState(InstallationState::STATE_INSTALLED));
            },
        );
    }

    /**
     * Server reachable but the credentials are wrong (incomplete config, MySQL
     * 1045/1698): the server responded, so it is not an outage — keep showing
     * the installer rather than returning a 503.
     */
    public function testAuthenticationFailureIsNotUnreachable()
    {
        $this->withDatabase(
            fn(Connection $db) => [
                'class' => Connection::class,
                'dsn' => $db->dsn,
                'username' => $db->username,
                'password' => $db->password . '_wrong_xyz',
                'charset' => $db->charset,
            ],
            function (InstallationState $state) {
                $this->assertFalse($state->isDatabaseUnreachable(), 'An auth failure is not an outage');
                $this->assertFalse($state->hasState(InstallationState::STATE_INSTALLED));
            },
        );
    }

    /**
     * An incomplete DSN without a database name: the connection opens but no
     * database is selected (MySQL 1046). This must be handled gracefully (no
     * uncaught error) and treated as a not-yet-configured install.
     */
    public function testMissingDatabaseNameIsNotUnreachable()
    {
        $this->withDatabase(
            fn(Connection $db) => [
                'class' => Connection::class,
                'dsn' => preg_replace('/;?dbname=[^;]*/', '', $db->dsn),
                'username' => $db->username,
                'password' => $db->password,
                'charset' => $db->charset,
            ],
            function (InstallationState $state) {
                $this->assertFalse($state->isDatabaseUnreachable(), 'A missing database name is not an outage');
                $this->assertFalse($state->hasState(InstallationState::STATE_INSTALLED));
            },
        );
    }

    /**
     * Runs $assertions against a fresh InstallationState built on top of a
     * temporary database configuration, then restores the real components.
     * Caching is disabled so the settings manager cannot resolve the stored
     * state and is forced to probe the (broken) database.
     *
     * @param callable $dbConfigFactory fn(Connection $originalDb): array
     * @param callable $assertions      fn(InstallationState $state): void
     */
    private function withDatabase(callable $dbConfigFactory, callable $assertions): void
    {
        $originalDb = Yii::$app->get('db');
        $originalCache = Yii::$app->get('cache');
        $originalSettings = Yii::$app->get('settings');

        try {
            Yii::$app->set('cache', ['class' => DummyCache::class]);
            Yii::$app->set('db', $dbConfigFactory($originalDb));
            Yii::$app->set('settings', ['class' => SettingsManager::class, 'moduleId' => 'base']);

            $assertions(InstallationState::instance(true));
        } finally {
            Yii::$app->set('db', $originalDb);
            Yii::$app->set('cache', $originalCache);
            Yii::$app->set('settings', $originalSettings);
            InstallationState::instance(true);
        }
    }
}
