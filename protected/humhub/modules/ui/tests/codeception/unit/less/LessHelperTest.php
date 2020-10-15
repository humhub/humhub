<?php

namespace tests\codeception\unit;

use humhub\modules\ui\view\helpers\LessHelper;
use tests\codeception\_support\HumHubDbTestCase;
use yii\helpers\ArrayHelper;

class LessHelperTest extends HumHubDbTestCase
{
    /**
     * Make sure value of less variables like @firstColor is replaced with real value like #FFF of the less variable with name firstColor
     */
    public function testParseLinkedLessVariables()
    {
        // Get less variables from two sample files:
        $variables = ArrayHelper::merge(
            LessHelper::parseLessVariables(__dir__ . DIRECTORY_SEPARATOR . 'first.less'),
            LessHelper::parseLessVariables(__dir__ . DIRECTORY_SEPARATOR . 'second.less'));
        // Update variables linked between two different files:
        $variables = LessHelper::updateLinkedLessVariables($variables);

        // Compare variables with same color #FF6600:
        $this->assertEquals($variables['firstColor'], $variables['firstLinkedColor']);
        $this->assertEquals($variables['firstColor'], $variables['firstLinked2Color']);
        $this->assertEquals($variables['firstLinkedColor'], $variables['firstLinked2Color']);
        $this->assertEquals($variables['firstColor'], $variables['secondLinkedColor']);
        $this->assertEquals($variables['secondLinkedColor'], $variables['secondLinked2Color']);

        // Compare variables with same color #0099DD:
        $this->assertEquals($variables['anotherFirstColor'], $variables['anotherFirstLinkedColor']);
        $this->assertEquals($variables['anotherFirstLinkedColor'], $variables['anotherSecondColor']);
        $this->assertEquals($variables['anotherFirstLinkedColor'], $variables['anotherSecondLinkedColor']);

        // Compare variables with different colors #FF6600 and #0099DD:
        $this->assertNotEquals($variables['firstColor'], $variables['anotherFirstColor']);
        $this->assertNotEquals($variables['firstLinkedColor'], $variables['anotherFirstLinkedColor']);
        $this->assertNotEquals($variables['firstLinked2Color'], $variables['anotherSecondColor']);
        $this->assertNotEquals($variables['secondLinkedColor'], $variables['anotherSecondLinkedColor']);
        $this->assertNotEquals($variables['secondLinked2Color'], $variables['anotherFirstLinkedColor']);
    }
}
