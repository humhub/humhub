<?php

namespace tests\codeception\_support;

use Yii;
use Codeception\TestCase\Test;

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
            'user' => ['class' => \tests\codeception\fixtures\UserFixture::className()],
            'profile' => ['class' => \tests\codeception\fixtures\ProfileFixture::className()],
            'settings' => ['class' => \tests\codeception\fixtures\SettingFixture::className()],
            'space' => [ 'class' => \tests\codeception\fixtures\SpaceFixture::className()],
            'space_membership' => [ 'class' => \tests\codeception\fixtures\SpaceMembershipFixture::className()],
            'contentcontainer' => [ 'class' => \tests\codeception\fixtures\ContentContainerFixture::className()],
            'notification' => [ 'class' => \tests\codeception\fixtures\NotificationFixture::className()],
        ];
    }
}
