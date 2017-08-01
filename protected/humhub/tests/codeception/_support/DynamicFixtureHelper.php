<?php

namespace tests\codeception\_support;

use Codeception\Module;
use humhub\modules\friendship\tests\codeception\fixtures\FriendshipFixture;
use humhub\modules\user\tests\codeception\fixtures\UserFullFixture;
use yii\test\FixtureTrait;
use yii\test\InitDbFixture;

/**
 * This helper is used to populate the database with needed fixtures before any tests are run.
 * In this example, the database is populated with the demo login user, which is used in acceptance
 * and functional tests.  All fixtures will be loaded before the suite is started and unloaded after it
 * completes.
 */
class DynamicFixtureHelper extends Module
{

    public $beforeTest = true;

    /**
     * Redeclare visibility because codeception includes all public methods that do not start with "_"
     * and are not excluded by module settings, in actor class.
     */
    use FixtureTrait {
        loadFixtures as public;
        fixtures as public;
        globalFixtures as public;
        createFixtures as public;
        unloadFixtures as protected;
        getFixtures as protected;
        getFixture as protected;
    }

    public function _beforeSuite($settings = [])
    {

        //Prevents [ReflectionException] Class db does not exist for included module tests
        include __DIR__ . '/../functional/_bootstrap.php';

        if (!$this->beforeTest) {
            $this->loadFixtures();
        }
    }
    
     public function _afterSuite($settings = [])
    {
        if (!$this->beforeTest) {
            $this->unloadFixtures();
        }
    }

    /**
     * Method called before any suite tests run. Loads User fixture login user
     * to use in acceptance and functional tests.
     * @param array $settings
     */
    public function _before(\Codeception\TestCase $test)
    {
        $this->unloadFixtures();

        if ($this->beforeTest) {
            $this->loadFixtures();
        }
    }

    /**
     * Method is called after all suite tests run
     */
    public function _after(\Codeception\TestCase $test)
    {
        if ($this->beforeTest) {
            $this->unloadFixtures();
        }
    }

    /**
     * @inheritdoc
     */
    public function globalFixtures()
    {
        return [
            InitDbFixture::className(),
        ];
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
            'user' => ['class' => UserFullFixture::class],
            'group_permission' => ['class' => \humhub\modules\user\tests\codeception\fixtures\GroupPermissionFixture::className()],
            'settings' => ['class' => \humhub\tests\codeception\fixtures\SettingFixture::className()],
            'space' => [ 'class' => \humhub\modules\space\tests\codeception\fixtures\SpaceFixture::className()],
            'space_membership' => [ 'class' => \humhub\modules\space\tests\codeception\fixtures\SpaceMembershipFixture::className()],
            'space_module' => ['class' => \humhub\modules\space\tests\codeception\fixtures\SpaceModuleFixture::className()],
            'content' => ['class' => \humhub\modules\content\tests\codeception\fixtures\ContentFixture::className()],
            'notification' => [ 'class' => \humhub\modules\notification\tests\codeception\fixtures\NotificationFixture::className()],
            'activity' => [ 'class' => \humhub\modules\activity\tests\codeception\fixtures\ActivityFixture::className()],
            'friendship' => ['class' => FriendshipFixture::class]
        ];
    }
}
