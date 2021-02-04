<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\models\UrlOembed;
use humhub\modules\content\widgets\richtext\extensions\oembed\OembedExtension;
use tests\codeception\_support\HumHubDbTestCase;


class RichTextOembedTest extends HumHubDbTestCase
{
    /*
     * Links
     */

    public function _before()
    {
        (new UrlOembed([
            'url' => 'https://www.youtube.com/watch?v=yt1',
            'preview' => 'yt1'
        ]))->save();

        (new UrlOembed([
            'url' => 'https://www.youtube.com/watch?v=yt2',
            'preview' => 'yt2'
        ]))->save();

        parent::_before();
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testParseSingleOembed()
    {
        $text = '[https://www.youtube.com/watch?v=yt1](oembed:https://www.youtube.com/watch?v=yt1)';
        $oembeds = OembedExtension::parseOembeds($text);
        static::assertCount(1, $oembeds);
        static::assertEquals('yt1', $oembeds['https://www.youtube.com/watch?v=yt1']);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testParseMultipleOembeds()
    {
        $text = '[https://www.youtube.com/watch?v=yt1](oembed:https://www.youtube.com/watch?v=yt1)\n\n[https://www.youtube.com/watch?v=yt2](oembed:https://www.youtube.com/watch?v=yt2)';
        $oembeds = OembedExtension::parseOembeds($text);
        static::assertCount(2, $oembeds);
        static::assertEquals('yt1', $oembeds['https://www.youtube.com/watch?v=yt1']);
        static::assertEquals('yt2', $oembeds['https://www.youtube.com/watch?v=yt2']);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testParseOembedsWithMax()
    {
        $text = '[https://www.youtube.com/watch?v=yt1](oembed:https://www.youtube.com/watch?v=yt1)\n\n[https://www.youtube.com/watch?v=yt2](oembed:https://www.youtube.com/watch?v=yt2)';
        $oembeds = OembedExtension::parseOembeds($text, 1);
        static::assertCount(1, $oembeds);
        static::assertEquals('yt1', $oembeds['https://www.youtube.com/watch?v=yt1']);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testParseOembedsWithZeroMax()
    {
        $text = '[https://www.youtube.com/watch?v=yt1](oembed:https://www.youtube.com/watch?v=yt1)\n\n[https://www.youtube.com/watch?v=yt2](oembed:https://www.youtube.com/watch?v=yt2)';
        $oembeds = OembedExtension::parseOembeds($text, 0);
        static::assertCount(0, $oembeds);
    }

}
