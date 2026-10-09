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
 * category switches.
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
        foreach (array_keys(m261006_100100_settings::CATEGORY_MAP) as $category) {
            $mobileOff['notification.' . $category . '_mobile'] = '0';
        }
        $this->seedGlobal([
            // social by e-mail is off: comments and followed explicitly, likes by default
            'notification.comments_email' => '0',
            'notification.followed_email' => '0',
            // likes in the web list are still on by default: social stays on in the web list
            'notification.comments_web' => '0',
            // dropped: a category of a module
            'notification.tasks_email' => '0',
        ] + $mobileOff);
        // explicitly on under a global "off": the user gets the positive keys
        $this->seedUser($user2, [
            'notification.comments_email' => '1',
            'notification.mentioned_mobile' => '1',
        ]);
        // a web key: comments in the web list are off globally, so social is off in the web list
        $this->seedUser($user3, [
            'notification.like_web' => '0',
        ]);
        $userOff = [];
        foreach (array_keys(m261006_100100_settings::CATEGORY_MAP) as $category) {
            $userOff['notification.' . $category . '_email'] = '0';
        }
        // every mapped category off - the direct category stays on, a module category is dropped
        $this->seedUser($user4, $userOff + ['notification.tasks_email' => '1']);

        (new m261006_100100_settings(['compact' => true]))->safeUp();

        $this->assertEquals([
            'email.category.social' => '0',
            'mobile.category.admin' => '0',
            'mobile.category.content' => '0',
            'mobile.category.social' => '0',
        ], $this->globalSettings());

        $this->assertEquals([
            'email.category.social' => '1',
        ], $this->userSettings($user2));

        $this->assertEquals(['web.category.social' => '0'], $this->userSettings($user3));

        $this->assertEquals([
            'email.category.admin' => '0',
            'email.category.content' => '0',
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
        // one key only: the other old categories of the category were on by default
        $this->seedUser($user3, [
            'notification.comments_mobile' => '0',
        ]);

        (new m261006_100100_settings(['compact' => true]))->safeUp();

        $this->assertEquals([], $this->globalSettings());
        $this->assertEquals(['email.category.social' => '0'], $this->userSettings($user2));
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

        $this->assertEquals([
            'mobile.category.admin' => '0',
            'mobile.category.content' => '0',
            'mobile.category.social' => '0',
        ], $this->globalSettings(), 'the admin category is decided by admin_mobile alone; new followers by push are off by default');
        $this->assertSame('0', $this->userSettings($user2)['email.category.admin'] ?? null);
    }

    public function testStoredSpaceCreatedKeyCounts()
    {
        $user2 = User::findOne(['id' => 2])->contentcontainer_id;
        $this->seedUser($user2, $this->allOffExceptSpaceCreated('email') + ['notification.space_created_email' => '1']);

        (new m261006_100100_settings(['compact' => true]))->safeUp();

        $this->assertEquals(['email.category.content' => '0', 'email.category.social' => '0'], $this->userSettings($user2));
    }

    private function allOffExceptSpaceCreated(string $target): array
    {
        $settings = [];
        foreach (array_keys(m261006_100100_settings::CATEGORY_MAP) as $category) {
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
            'notification.content_created_email' => '0',
            'email.category.social' => '1',
        ]);

        (new m261006_100100_settings(['compact' => true]))->safeUp();

        $this->assertEquals([
            'email.category.content' => '0',
            'email.category.social' => '1',
        ], $this->userSettings($user2));
    }

    public function testNewFollowersFollowTheNewDefaultsUnlessStored()
    {
        $user2 = User::findOne(['id' => 2])->contentcontainer_id;
        $user3 = User::findOne(['id' => 3])->contentcontainer_id;
        $this->seedGlobal(['notification.followed_web' => '0']);
        // explicitly on: kept, although new followers by e-mail are off by default now
        $this->seedUser($user2, ['notification.followed_email' => '1']);
        // nothing stored for followed: the 1.20 default applies
        $this->seedUser($user3, ['notification.comments_email' => '0']);

        (new m261006_100100_settings(['compact' => true]))->safeUp();

        $this->assertEquals(['web.category.followers' => '0'], $this->globalSettings());
        $this->assertEquals(['email.category.followers' => '1'], $this->userSettings($user2));
        $this->assertEquals(['email.category.social' => '0'], $this->userSettings($user3), 'likes by e-mail were off by default');
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
            ->andWhere(['LIKE', 'name', '%.category.%', false])
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
            ->andWhere(['LIKE', 'name', '%.category.%', false])
            ->orderBy('name')
            ->indexBy('name')
            ->column();
    }
}
