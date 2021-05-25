<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\models\UrlOembed;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;


class RichTextLinkExtensionLegacyTest extends HumHubDbTestCase
{

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testScanLinkExtension()
    {
        $match = ProsemirrorRichText::scanLinkExtension('[Text](test:id "title")', 'test');

        static::assertEquals('[Text](test:id "title")', $match[0][0]);
        static::assertEquals('Text', $match[0][1]);
        static::assertEquals('test', $match[0][2]);
        static::assertEquals('id', $match[0][3]);
        static::assertEquals('title', $match[0][4]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testReplaceLinkExtension()
    {
        $resultMatch = [];

        $result = ProsemirrorRichText::replaceLinkExtension('[Text](test:id "title")', 'test', function($match) use (&$resultMatch) {
            $resultMatch = $match;
            return 'tested';
        });

        static::assertEquals('tested', $result);
        static::assertEquals('[Text](test:id "title")', $resultMatch[0]);
        static::assertEquals('Text', $resultMatch[1]);
        static::assertEquals('test', $resultMatch[2]);
        static::assertEquals('id', $resultMatch[3]);
        static::assertEquals('title', $resultMatch[4]);
    }
}
