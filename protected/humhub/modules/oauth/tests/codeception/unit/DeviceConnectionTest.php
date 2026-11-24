<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\modules\oauth\components\DeviceConnection;
use humhub\modules\oauth\components\DeviceConnectionRegistry;
use humhub\modules\oauth\events\DeviceConnectionsEvent;
use humhub\modules\oauth\Module;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;

/**
 * The connections modules offer for a device, e.g. a network drive: what it needs to know and
 * the token it signs in with.
 */
class DeviceConnectionTest extends HumHubDbTestCase
{
    /** @var DeviceConnection[] */
    private array $offered = [];

    protected function _before()
    {
        parent::_before();
        Module::instance()->settings->set('allowPersonalAccessTokens', true);
        Event::on(DeviceConnectionRegistry::class, DeviceConnectionRegistry::EVENT_COLLECT_CONNECTIONS, [$this, 'collect']);
    }

    protected function _after()
    {
        Event::off(DeviceConnectionRegistry::class, DeviceConnectionRegistry::EVENT_COLLECT_CONNECTIONS, [$this, 'collect']);
        parent::_after();
    }

    public function collect(DeviceConnectionsEvent $event): void
    {
        foreach ($this->offered as $connection) {
            $event->connections[] = $connection;
        }
    }

    private static function connection(string $id = 'test-drive', array $scopes = ['content:read']): DeviceConnection
    {
        return new DeviceConnection($id, 'Connect a test drive', 'Your files on your computer.', 'Test drive', $scopes, ['Address' => 'https://example.org/dav/']);
    }

    public function testModulesOfferConnectionsForTheUser()
    {
        $this->offered = [self::connection()];
        $user = User::findOne(['id' => 2]);

        $connections = DeviceConnectionRegistry::forUser($user);

        $this->assertSame(['test-drive'], array_keys($connections));
        $this->assertSame('test-drive', DeviceConnectionRegistry::find($user, 'test-drive')?->id);
        $this->assertNull(DeviceConnectionRegistry::find($user, 'other'));
        $this->assertStringContainsString('test-drive', $connections['test-drive']->getUrl());
    }

    public function testThereAreNoneWhileTokensAreSwitchedOff()
    {
        $this->offered = [self::connection()];
        Module::instance()->settings->set('allowPersonalAccessTokens', false);

        $this->assertSame([], DeviceConnectionRegistry::forUser(User::findOne(['id' => 2])));
    }

    public function testInvalidConnectionsAreIgnored()
    {
        $this->offered = [
            self::connection('Not An Id'),
            self::connection('unknown-scope', ['nobody:read']),
            self::connection(),
            self::connection(),
        ];

        $this->assertSame(['test-drive'], array_keys(DeviceConnectionRegistry::forUser(User::findOne(['id' => 2]))));
    }
}
