<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit;

use humhub\modules\user\components\Session;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\db\Query;

class SessionTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    private const UA = 'Mozilla/5.0 (X11; Linux x86_64; rv:131.0) Gecko/20100101 Firefox/131.0';

    public function _before()
    {
        parent::_before();
        Yii::$app->db->createCommand()->delete('user_http_session')->execute();
        Yii::$app->request->headers->set('User-Agent', self::UA);
    }

    public function testGuestSessionHasNoLoginInfo(): void
    {
        $this->logout();
        $this->assertTrue((new Session())->writeSession('sess-guest', ''));

        $row = $this->getRow('sess-guest');
        $this->assertNull($row['user_id']);
        $this->assertNull($row['logged_in_at']);
        $this->assertNull($row['user_agent']);
    }

    public function testLoginIsRecordedOnUserChange(): void
    {
        $session = new Session();
        $this->logout();
        $session->writeSession('sess-1', '');

        $user = $this->becomeUser('User1');
        $session->writeSession('sess-1', '');

        $row = $this->getRow('sess-1');
        $this->assertSame($user->id, (int)$row['user_id']);
        $this->assertEqualsWithDelta(time(), (int)$row['logged_in_at'], 2);
        $this->assertSame(self::UA, $row['user_agent']);
    }

    public function testLoginTimeIsKeptOnFollowingRequests(): void
    {
        $session = new Session();
        $this->becomeUser('User1');
        $session->writeSession('sess-1', '');
        Yii::$app->db->createCommand()->update('user_http_session', ['logged_in_at' => 1000], ['id' => 'sess-1'])->execute();

        Yii::$app->request->headers->set('User-Agent', 'Other');
        $session->writeSession('sess-1', 'data');

        $row = $this->getRow('sess-1');
        $this->assertSame(1000, (int)$row['logged_in_at']);
        $this->assertSame(self::UA, $row['user_agent']);
    }

    public function testUserAgentIsTruncated(): void
    {
        Yii::$app->request->headers->set('User-Agent', str_repeat('x', 400));
        $this->becomeUser('User1');
        (new Session())->writeSession('sess-1', '');

        $this->assertSame(255, mb_strlen($this->getRow('sess-1')['user_agent']));
    }

    private function getRow(string $id): array
    {
        return (new Query())->from('user_http_session')->where(['id' => $id])->one();
    }
}
