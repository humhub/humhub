<?php

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\RichText;
use yii\codeception\TestCase;

class RichTextTest extends TestCase
{
    public function testTranslateDomainName()
    {
        $output = RichText::widget([
            'text' => 'Visit apple.com for more info'
        ]);
        $this->assertEquals(
            'Visit <a href="http://apple.com" target="_blank">apple.com</a> for more info',
            $output
        );

        $output = RichText::widget([
            'text' => 'Visit non-existing-host.com for more info'
        ]);
        $this->assertEquals(
            'Visit non-existing-host.com for more info',
            $output
        );
    }
}
