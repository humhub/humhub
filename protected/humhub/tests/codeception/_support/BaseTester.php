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
    /**
     * @var Space[]
     */
    private $spaces;

    public function getFixtureSpace(int $index) : ?Space
    {
        if (method_exists($this, 'haveFixtures') && method_exists($this, 'grabFixture')) {
            $this->haveFixtures(['space' => SpaceFixture::class]);
            return $this->grabFixture('space', $index);
        } else {
            // Acceptance tests have no the methods above, try to get spaces from DB instead:
            if (!isset($this->spaces)) {
                $this->spaces = Space::find()->orderBy('id')->all();
            }
            return isset($this->spaces[$index]) ? $this->spaces[$index] : null;
        }
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
