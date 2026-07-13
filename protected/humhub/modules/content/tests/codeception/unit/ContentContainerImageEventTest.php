<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content;

use humhub\components\assets\AssetImage;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\events\ContentContainerImageEvent;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;

class ContentContainerImageEventTest extends HumHubDbTestCase
{
    protected function _after()
    {
        Event::off(Space::class, ContentContainerActiveRecord::EVENT_INIT_PROFILE_IMAGE);
        Event::off(Space::class, ContentContainerActiveRecord::EVENT_INIT_BANNER_IMAGE);
        parent::_after();
    }

    public function testProfileImageCanBeReplacedByEvent()
    {
        Event::on(Space::class, ContentContainerActiveRecord::EVENT_INIT_PROFILE_IMAGE, function (ContentContainerImageEvent $event) {
            $event->image = new AssetImage(['file' => '/tests/custom-profile.png'] + $event->config);
        });

        $space = Space::findOne(['id' => 1]);
        $this->assertSame('/tests/custom-profile.png', $space->getImage()->file);
        // `$event->config` lets handlers reuse the core defaults for their replacement
        $this->assertSame(150, $space->getImage()->defaultOptions['width']);

        // The banner image and other container classes are unaffected
        $this->assertSame('/profile_image/banner/' . $space->guid . '.jpg', $space->getBannerImage()->file);
        $user = User::findOne(['id' => 1]);
        $this->assertSame('/profile_image/' . $user->guid . '.jpg', $user->getImage()->file);
    }

    public function testBannerImageCanBeCustomizedByEvent()
    {
        Event::on(Space::class, ContentContainerActiveRecord::EVENT_INIT_BANNER_IMAGE, function (ContentContainerImageEvent $event) {
            $event->image->defaultFile = Yii::getAlias('@humhub/resources/img/default_space.jpg');
        });

        $space = Space::findOne(['id' => 1]);
        $this->assertStringEndsWith('default_space.jpg', $space->getBannerImage()->defaultFile);
    }

    public function testInitImageEventIsTriggeredOncePerRecord()
    {
        $calls = 0;
        Event::on(Space::class, ContentContainerActiveRecord::EVENT_INIT_PROFILE_IMAGE, function () use (&$calls) {
            $calls++;
        });

        $space = Space::findOne(['id' => 1]);
        $this->assertSame($space->getImage(), $space->getImage());
        $this->assertSame(1, $calls);
    }
}
