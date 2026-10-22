<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit;

use humhub\modules\user\models\User;
use humhub\modules\user\services\UserSessionService;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\db\Query;

class UserSessionServiceTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    private User $user;

    public function _before()
    {
        parent::_before();
        Yii::$app->db->createCommand()->delete('user_http_session')->execute();

        $this->user = $this->becomeUser('User1');
        $this->addSession('current', $this->user->id, time() + 3600, time() - 60);
        $this->addSession('other-a', $this->user->id, time() + 1800, time() - 7200);
        $this->addSession('other-b', $this->user->id, time() + 600, null);
        $this->addSession('expired', $this->user->id, time() - 10, time() - 9000);
        $this->addSession('foreign', User::findOne(['username' => 'User2'])->id, time() + 3600, time());
        $this->addSession('guest', null, time() + 3600, null);
    }

    public function testListsOnlyOwnActiveSessionsByLastActivity(): void
    {
        $sessions = $this->service()->getActiveSessions();

        $this->assertSame(
            [hash('sha256', 'current'), hash('sha256', 'other-a'), hash('sha256', 'other-b')],
            array_map(fn($s) => $s->key, $sessions),
        );
        $this->assertTrue($sessions[0]->isCurrent);
        $this->assertFalse($sessions[1]->isCurrent);
        $this->assertSame('Firefox · Linux', $sessions[0]->device);
        $this->assertNull($sessions[2]->loggedInAt);
    }

    public function testTerminate(): void
    {
        $this->assertTrue($this->service()->terminate(hash('sha256', 'other-a')));
        $this->assertFalse($this->exists('other-a'));
    }

    public function testTerminateRefusesForeignUnknownAndCurrent(): void
    {
        $service = $this->service();

        $this->assertFalse($service->terminate(hash('sha256', 'foreign')));
        $this->assertFalse($service->terminate('unknown'));
        $this->assertFalse($service->terminate(hash('sha256', 'current')));
        $this->assertTrue($this->exists('foreign'));
        $this->assertTrue($this->exists('current'));
    }

    public function testTerminateOthers(): void
    {
        $authKey = $this->user->getAuthKey();

        $this->service()->terminateOthers();

        $this->assertTrue($this->exists('current'));
        $this->assertFalse($this->exists('other-a'));
        $this->assertFalse($this->exists('other-b'));
        $this->assertTrue($this->exists('foreign'));
        $this->assertNotSame($authKey, User::findOne($this->user->id)->getAuthKey());
    }

    public function testDeleteOthersKeepsAuthKey(): void
    {
        $authKey = $this->user->getAuthKey();

        $this->service()->deleteOthers();

        $this->assertTrue($this->exists('current'));
        $this->assertFalse($this->exists('other-a'));
        $this->assertFalse($this->exists('other-b'));
        $this->assertTrue($this->exists('foreign'));
        $this->assertSame($authKey, User::findOne($this->user->id)->getAuthKey());
    }

    public function testRotateAuthKeyKeepsCurrentSessionValid(): void
    {
        $user = Yii::$app->user;
        $enableSession = $user->enableSession;
        $user->enableSession = true;

        try {
            Yii::$app->session->set($user->authKeyParam, $this->user->getAuthKey());

            // A fresh instance of the current identity, as passed in by a service
            $user->rotateAuthKey(User::findOne($this->user->id));

            $authKey = User::findOne($this->user->id)->getAuthKey();
            $this->assertSame($authKey, Yii::$app->session->get($user->authKeyParam));
            $this->assertSame($authKey, $user->getIdentity()->getAuthKey());
        } finally {
            $user->enableSession = $enableSession;
            Yii::$app->session->remove($user->authKeyParam);
        }
    }

    public function testIsNotAvailableWithoutDbSession(): void
    {
        // unit tests run with yii\web\CacheSession
        $this->assertFalse($this->service()->isAvailable());
    }

    private function service(): UserSessionService
    {
        return new UserSessionService($this->user, 'current');
    }

    private function addSession(string $id, ?int $userId, int $expire, ?int $loggedInAt): void
    {
        Yii::$app->db->createCommand()->insert('user_http_session', [
            'id' => $id,
            'user_id' => $userId,
            'expire' => $expire,
            'data' => '',
            'logged_in_at' => $loggedInAt,
            'user_agent' => $loggedInAt === null ? null : 'Mozilla/5.0 (X11; Linux x86_64; rv:131.0) Gecko/20100101 Firefox/131.0',
        ])->execute();
    }

    private function exists(string $id): bool
    {
        return (new Query())->from('user_http_session')->where(['id' => $id])->exists();
    }
}
