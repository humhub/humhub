<?php

use humhub\modules\space\models\Space;
use humhub\modules\space\tests\codeception\fixtures\SpaceFixture;

/**
 * Base class for testers
 *
 * Inherited Methods
 * @method void haveFixtures($fixtures)
 * @method Space grabFixture($fixtureName, $index)
 */
class BaseTester extends \Codeception\Actor
{
    public function getFixtureSpace(int $index) : Space
    {
        $this->haveFixtures(['space' => SpaceFixture::class]);
        return $this->grabFixture('space', $index);
    }

    public function getFixtureSpaceGuid(int $index) : string
    {
        $space = $this->getFixtureSpace($index);
        return ($space instanceof Space ? $space->guid : '');
    }

    public function enableModule($indexOrGuid, $moduleId)
    {
        if (is_int($indexOrGuid)) {
            $space = $this->getFixtureSpace(--$indexOrGuid);
        } else {
            $space = Space::findOne(['guid' => $indexOrGuid]);
        }

        if ($space) {
            $space->enableModule($moduleId);
            Yii::$app->moduleManager->flushCache();
        }
    }
}
