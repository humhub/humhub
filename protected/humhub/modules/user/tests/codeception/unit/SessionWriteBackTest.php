<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\user\components\Session;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\db\Query;

/**
 * Sessions are not locked: requests of one session run in parallel, each one calls
 * {@see Session::readSession()} when it starts and {@see Session::writeSession()} when it ends.
 */
class SessionWriteBackTest extends HumHubDbTestCase
{
    private const TABLE = 'user_http_session';
    private const TIMEOUT = 1400;

    protected function setUp(): void
    {
        parent::setUp();

        Yii::$app->user->authTimeout = self::TIMEOUT;
        Yii::$app->db->createCommand()->delete(self::TABLE)->execute();
        $this->logout();
    }

    /**
     * For example a service worker request while the first step of the login stores the user name
     */
    public function testRequestChangingNothingKeepsWhatAParallelRequestStored(): void
    {
        $this->addRow('sess-1', 'read');

        $background = new Session();
        $data = $background->readSession('sess-1');

        $parallel = new Session();
        $parallel->readSession('sess-1');
        $parallel->writeSession('sess-1', 'stored in parallel');

        $this->assertTrue($background->writeSession('sess-1', $data));

        $this->assertSame('stored in parallel', $this->getRow('sess-1')['data']);
    }

    public function testRequestChangingNothingRenewsTheExpiry(): void
    {
        $this->addRow('sess-1', 'read', time() + 10);

        $session = new Session();
        $this->assertTrue($session->writeSession('sess-1', $session->readSession('sess-1')));

        $this->assertEqualsWithDelta(time() + self::TIMEOUT, (int)$this->getRow('sess-1')['expire'], 2);
    }

    public function testRequestChangingNothingKeepsTheUserOfTheRowCurrent(): void
    {
        $this->addRow('sess-1', 'read');

        $session = new Session();
        $data = $session->readSession('sess-1');
        $user = $this->becomeUser('User1');
        $this->assertTrue($session->writeSession('sess-1', $data));

        $this->assertSame($user->id, (int)$this->getRow('sess-1')['user_id']);
    }

    public function testChangedDataIsWritten(): void
    {
        $this->addRow('sess-1', 'read', time() + 10);

        $session = new Session();
        $session->readSession('sess-1');
        $this->assertTrue($session->writeSession('sess-1', 'changed'));

        $row = $this->getRow('sess-1');
        $this->assertSame('changed', $row['data']);
        $this->assertEqualsWithDelta(time() + self::TIMEOUT, (int)$row['expire'], 2);
    }

    public function testNewSessionIsCreated(): void
    {
        $session = new Session();
        $this->assertSame('', $session->readSession('sess-new'));
        $this->assertTrue($session->writeSession('sess-new', 'data'));

        $this->assertSame('data', $this->getRow('sess-new')['data']);
    }

    public function testNewSessionWithoutDataIsCreated(): void
    {
        $session = new Session();
        $session->readSession('sess-new');
        $this->assertTrue($session->writeSession('sess-new', ''));

        $this->assertNotNull($this->getRow('sess-new'));
    }

    public function testExpiredSessionIsTakenOverWithTheDataOfTheRequest(): void
    {
        $this->addRow('sess-1', 'expired', time() - 10);

        $session = new Session();
        $this->assertSame('', $session->readSession('sess-1'));
        $this->assertTrue($session->writeSession('sess-1', 'data'));

        $row = $this->getRow('sess-1');
        $this->assertSame('data', $row['data']);
        $this->assertGreaterThan(time(), (int)$row['expire']);
    }

    /**
     * {@see Session::regenerateID()}: PHP reads the new ID, so the comparison must not apply to it
     */
    public function testSameDataUnderAnotherIdIsWritten(): void
    {
        $this->addRow('sess-old', 'data');

        $session = new Session();
        $data = $session->readSession('sess-old');
        $this->assertTrue($session->writeSession('sess-new', $data));

        $this->assertSame('data', $this->getRow('sess-new')['data']);
    }

    private function addRow(string $id, string $data, ?int $expire = null): void
    {
        Yii::$app->db->createCommand()->insert(self::TABLE, [
            'id' => $id,
            'data' => $data,
            'expire' => $expire ?? time() + 60,
        ])->execute();
    }

    private function getRow(string $id): ?array
    {
        return (new Query())->from(self::TABLE)->where(['id' => $id])->one() ?: null;
    }
}
