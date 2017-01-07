<?php

namespace tests\codeception\_support;

use Yii;
use Codeception\TestCase\Test;
use humhub\modules\user\models\User;

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
    }
    
    /**
     * Initializes modules defined in @tests/codeception/config/test.config.php
     * Note the config key in test.config.php is modules and not humhubModules!
     */
    protected function initModules() {
        $cfg = \Codeception\Configuration::config();
        if(!empty($cfg['humhub_modules'])) {
            Yii::$app->moduleManager->enableModules($cfg['humhub_modules']);
        }
    }
    
    public function becomeUser($userName)
    {
        $user = User::findOne(['username' => $userName]);
        Yii::$app->user->switchIdentity($user);
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
            'space' => [ 'class' => \humhub\modules\space\tests\codeception\fixtures\SpaceFixture::className()],
            'space_membership' => [ 'class' => \humhub\modules\space\tests\codeception\fixtures\SpaceMembershipFixture::className()],
            'contentcontainer' => [ 'class' => \humhub\modules\content\tests\codeception\fixtures\ContentContainerFixture::className()],
            'notification' => [ 'class' => \humhub\modules\notification\tests\codeception\fixtures\NotificationFixture::className()],
            'activity' => [ 'class' => \humhub\modules\activity\tests\codeception\fixtures\ActivityFixture::className()],
        ];
    }
}
