<?php

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\RichText;
use yii\codeception\TestCase;

class RichTextTest extends TestCase
{
    public function testTranslateUrl()
    {
        $output = RichText::widget([
            'text' => 'Visit http://apple.com for more info'
        ]);
        $this->assertEquals(
            'Visit <a href="http://apple.com" target="_blank">http://apple.com</a> for more info',
            $output
        );

        $output = RichText::widget([
            'text' => 'Visit https://apple.com for more info'
        ]);
        $this->assertEquals(
            'Visit <a href="https://apple.com" target="_blank">https://apple.com</a> for more info',
            $output
        );
    }
}
