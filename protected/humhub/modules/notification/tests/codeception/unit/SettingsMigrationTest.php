<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\user\models\User;
use m261006_100100_settings;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\db\Query;

require_once __DIR__ . '/../../../migrations/m261006_100100_settings.php';

/**
 * The migration of the 1.19 category switches (`notification.<category>_<target>`) to the
 * channel modes and group switches.
 */
class SettingsMigrationTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testMigratesGlobalAndUserSettings()
    {
        $user2 = User::findOne(['id' => 2])->contentcontainer_id;
        $user3 = User::findOne(['id' => 3])->contentcontainer_id;
        $user4 = User::findOne(['id' => 4])->contentcontainer_id;

        $mobileOff = [];
        foreach (array_keys(m261006_100100_settings::CATEGORY_GROUPS) as $category) {
            $mobileOff['notification.' . $category . '_mobile'] = '0';
        }
        $this->seedGlobal([
            // social by e-mail is off: comments and followed explicitly, likes by default
            'notification.comments_email' => '0',
            'notification.followed_email' => '0',
            // dropped: the web list, and a category of a module
            'notification.comments_web' => '0',
            'notification.tasks_email' => '0',
        ] + $mobileOff);
        // explicitly on under a global "off": the user gets the positive keys
        $this->seedUser($user2, [
            'notification.comments_email' => '1',
            'notification.mentioned_mobile' => '1',
        ]);
        // only a web key: the user follows the global result, no key of its own
        $this->seedUser($user3, [
            'notification.like_web' => '0',
        ]);
        $userOff = [];
        foreach (array_keys(m261006_100100_settings::CATEGORY_GROUPS) as $category) {
            $userOff['notification.' . $category . '_email'] = '0';
        }
        // every mapped category off - a module category on does not keep the channel on
        $this->seedUser($user4, $userOff + ['notification.tasks_email' => '1']);

        (new m261006_100100_settings(['compact' => true]))->safeUp();

        $this->assertEquals([
            'email.group.social' => '0',
            'mobile.group.admin' => '0',
            'mobile.group.content' => '0',
            'mobile.group.social' => '0',
            'mobile.mode' => 'off',
        ], $this->globalSettings());

        $this->assertEquals([
            'email.group.social' => '1',
            'mobile.mode' => 'adaptive',
        ], $this->userSettings($user2));

        $this->assertEquals([], $this->userSettings($user3));

        $this->assertEquals([
            'email.group.admin' => '0',
            'email.group.content' => '0',
            'email.mode' => 'off',
        ], $this->userSettings($user4));

        $this->assertSame(0, (int)(new Query())->from('setting')->where(['module_id' => 'notification'])->andWhere(['LIKE', 'name', 'notification.%', false])->count());
        $this->assertSame(0, (int)(new Query())->from('contentcontainer_setting')->where(['module_id' => 'notification'])->andWhere(['LIKE', 'name', 'notification.%', false])->count());
    }

    public function testFillsInTheOldDefaults()
    {
        $user2 = User::findOne(['id' => 2])->contentcontainer_id;
        $user3 = User::findOne(['id' => 3])->contentcontainer_id;
        // likes by e-mail were off by default: with comments and followed off, social is off
        $this->seedUser($user2, [
            'notification.comments_email' => '0',
            'notification.followed_email' => '0',
        ]);
        // one key only: the other categories of the group were on by default
        $this->seedUser($user3, [
            'notification.comments_mobile' => '0',
        ]);

        (new m261006_100100_settings(['compact' => true]))->safeUp();

        $this->assertEquals([], $this->globalSettings());
        $this->assertEquals(['email.group.social' => '0'], $this->userSettings($user2));
        $this->assertEquals([], $this->userSettings($user3));
    }

    public function testEverythingOffWithoutTheSpaceCreatedKey()
    {
        $user2 = User::findOne(['id' => 2])->contentcontainer_id;
        // what the 1.19 forms stored for "everything off": every mapped category but
        // space_created, which they did not show
        $this->seedGlobal($this->allOffExceptSpaceCreated('mobile'));
        $this->seedUser($user2, $this->allOffExceptSpaceCreated('email'));

        (new m261006_100100_settings(['compact' => true]))->safeUp();

        $this->assertSame('off', $this->globalSettings()['mobile.mode'] ?? null);
        $this->assertSame('0', $this->globalSettings()['mobile.group.admin'] ?? null, 'the admin group is decided by admin_mobile alone');
        $this->assertSame('off', $this->userSettings($user2)['email.mode'] ?? null);
        $this->assertSame('0', $this->userSettings($user2)['email.group.admin'] ?? null);
    }

    public function testStoredSpaceCreatedKeyCounts()
    {
        $user2 = User::findOne(['id' => 2])->contentcontainer_id;
        $this->seedUser($user2, $this->allOffExceptSpaceCreated('email') + ['notification.space_created_email' => '1']);

        (new m261006_100100_settings(['compact' => true]))->safeUp();

        $this->assertEquals(['email.group.content' => '0', 'email.group.social' => '0'], $this->userSettings($user2));
    }

    private function allOffExceptSpaceCreated(string $target): array
    {
        $settings = [];
        foreach (array_keys(m261006_100100_settings::CATEGORY_GROUPS) as $category) {
            if ($category !== 'space_created') {
                $settings['notification.' . $category . '_' . $target] = '0';
            }
        }

        return $settings;
    }

    public function testKeepsANewKeyAlreadyStored()
    {
        $user2 = User::findOne(['id' => 2])->contentcontainer_id;
        $this->seedUser($user2, [
            'notification.comments_email' => '0',
            'notification.followed_email' => '0',
            'email.mode' => 'summary',
        ]);

        (new m261006_100100_settings(['compact' => true]))->safeUp();

        $this->assertEquals([
            'email.group.social' => '0',
            'email.mode' => 'summary',
        ], $this->userSettings($user2));
    }

    private function seedGlobal(array $settings): void
    {
        foreach ($settings as $name => $value) {
            Yii::$app->db->createCommand()->insert('setting', ['module_id' => 'notification', 'name' => $name, 'value' => $value])->execute();
        }
    }

    private function seedUser(int $containerId, array $settings): void
    {
        foreach ($settings as $name => $value) {
            Yii::$app->db->createCommand()->insert('contentcontainer_setting', [
                'module_id' => 'notification',
                'contentcontainer_id' => $containerId,
                'name' => $name,
                'value' => $value,
            ])->execute();
        }
    }

    private function globalSettings(): array
    {
        return (new Query())
            ->select(['value', 'name'])
            ->from('setting')
            ->where(['module_id' => 'notification'])
            ->andWhere(['OR', ['LIKE', 'name', '%.mode', false], ['LIKE', 'name', '%.group.%', false]])
            ->orderBy('name')
            ->indexBy('name')
            ->column();
    }

    private function userSettings(int $containerId): array
    {
        return (new Query())
            ->select(['value', 'name'])
            ->from('contentcontainer_setting')
            ->where(['module_id' => 'notification', 'contentcontainer_id' => $containerId])
            ->andWhere(['OR', ['LIKE', 'name', '%.mode', false], ['LIKE', 'name', '%.group.%', false]])
            ->orderBy('name')
            ->indexBy('name')
            ->column();
    }
}
