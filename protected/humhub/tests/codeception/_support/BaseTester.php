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
        if (isset($this->spaces[$index])) {
            return $this->spaces[$index];
        }

        if (method_exists($this, 'haveFixtures') && method_exists($this, 'grabFixture')) {
            if (!isset($this->spaces)) {
                // Don't try to load spaces twice because it is delete all space records from related tables
                $this->haveFixtures(['space' => SpaceFixture::class]);
            }
            $this->spaces[$index] = $this->grabFixture('space', $index);
        } else if (!isset($this->spaces)) {
            // Acceptance tests have no the methods above, try to get spaces from DB instead:
            $this->spaces = Space::find()->orderBy('id')->all();
            if (!isset($this->spaces[$index])) {
                $this->spaces[$index] = null;
            }
        }

        return $this->spaces[$index];
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
            $space->moduleManager->enable($moduleId);
            $space->moduleManager->flushCache();
            Yii::$app->moduleManager->flushCache();
        }
    }
}
