<?php

namespace tests\codeception\unit;

use humhub\modules\user\controllers\ProfileController;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\ProfileHeader;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\InvalidConfigException;

class ProfileHeaderTest extends HumHubDbTestCase
{
    protected function tearDown(): void
    {
        Yii::$app->controller = null;
        Yii::$app->request->setQueryParams([]);

        parent::tearDown();
    }

    public function testUserIsDetectedFromProfileController()
    {
        $user = User::findOne(['id' => 2]);

        Yii::$app->request->setQueryParams(['cguid' => $user->guid]);
        Yii::$app->controller = new ProfileController('profile', Yii::$app->getModule('user'));

        $widget = new ProfileHeader();

        $this->assertEquals($user->id, $widget->user->id);
    }

    public function testMissingUserThrowsException()
    {
        $this->expectException(InvalidConfigException::class);

        new ProfileHeader();
    }
}
