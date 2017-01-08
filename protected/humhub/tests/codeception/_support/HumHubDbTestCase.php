<?php

namespace tests\codeception\_support;

use Yii;
use yii\db\ActiveRecord;
use Codeception\TestCase\Test;
use humhub\modules\user\models\User;
use humhub\modules\notification\models\Notification;
use humhub\modules\activity\models\Activity;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class HumHubDbTestCase extends \yii\codeception\DbTestCase
{

    protected function setUp()
    {
        Test::setUp();
        $this->mockApplication();
        $this->initModules();
        $this->unloadFixtures();
        $this->loadFixtures();
        $this->deleteMails();
    }

    protected function deleteMails()
    {
        $path = Yii::getAlias('@runtime/mail');
        $files = glob($path . '/*'); // get all file names
        foreach ($files as $file) { // iterate files            
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
    }

    /**
     * Initializes modules defined in @tests/codeception/config/test.config.php
     * Note the config key in test.config.php is modules and not humhubModules!
     */
    protected function initModules()
    {
        $cfg = \Codeception\Configuration::config();
        if (!empty($cfg['humhub_modules'])) {
            Yii::$app->moduleManager->enableModules($cfg['humhub_modules']);
        }
    }

    /**
     * @inheritdoc
     */
    public function fixtures()
    {
        $result = [];

        $cfg = \Codeception\Configuration::config();
        if (isset($cfg['fixtures'])) {
            foreach ($cfg['fixtures'] as $fixtureTable => $fixtureClass) {
                if ($fixtureClass === 'default') {
                    $result = array_merge($result, $this->getDefaultFixtures());
                } else {
                    $result[$fixtureTable] = ['class' => $fixtureClass];
                }
            }
        }

        return $result;
    }

    protected function getDefaultFixtures()
    {
        return [
            'user' => ['class' => \humhub\modules\user\tests\codeception\fixtures\UserFixture::className()],
            'group' => ['class' => \humhub\modules\user\tests\codeception\fixtures\GroupFixture::className()],
            'group_permission' => ['class' => \humhub\modules\user\tests\codeception\fixtures\GroupPermissionFixture::className()],
            'settings' => ['class' => \humhub\tests\codeception\fixtures\SettingFixture::className()],
            'space' => ['class' => \humhub\modules\space\tests\codeception\fixtures\SpaceFixture::className()],
            'space_membership' => ['class' => \humhub\modules\space\tests\codeception\fixtures\SpaceMembershipFixture::className()],
            'contentcontainer' => ['class' => \humhub\modules\content\tests\codeception\fixtures\ContentContainerFixture::className()],
            'notification' => ['class' => \humhub\modules\notification\tests\codeception\fixtures\NotificationFixture::className()],
            'activity' => ['class' => \humhub\modules\activity\tests\codeception\fixtures\ActivityFixture::className()],
        ];
    }

    public function assertHasNotification($class, ActiveRecord $source, $originator_id = null, $msg = null)
    {
        $notificationQuery = Notification::find(['class' => $class, 'source_class' => $source->className(), 'source_pk' => $source->getPrimaryKey()]);

        if ($originator_id != null) {
            $notificationQuery->andWhere(['originator_user_id' => $originator_id]);
        }

        $this->assertNotEmpty($notificationQuery->all(), $msg);
    }

    public function assertHasActivity($class, ActiveRecord $source, $msg = null)
    {
        $activity = Activity::findOne(['class' => $class, 'object_model' => $source->className(), 'object_id' => $source->getPrimaryKey()]);
        $this->assertNotNull($activity, $msg);
    }

    public function assertMailSent($count = 0, $msg = null)
    {
        $path = Yii::getAlias('@runtime/mail');
        $mailCount = count(glob($path . '/*.eml'));

        if (!$count) {
            $this->assertTrue($mailCount > 0, $msg);
        } else {
            $this->assertEquals($count, $mailCount, $msg);
        }
    }

    public function becomeUser($userName)
    {
        $user = User::findOne(['username' => $userName]);
        Yii::$app->user->switchIdentity($user);
    }

}
