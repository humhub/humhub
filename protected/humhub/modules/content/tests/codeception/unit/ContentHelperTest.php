<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content;

use humhub\modules\content\helpers\ContentHelper;
use tests\codeception\_support\HumHubDbTestCase;
use humhub\modules\post\models\Post;

class ContentHelperTest extends HumHubDbTestCase
{
    public function testGetContentInfoWithPreview()
    {
        $post = Post::findOne(['id' => 1]);

        $this->assertEquals('post "' . $post->message . '"', ContentHelper::getContentInfo($post));
        $this->assertEquals($post->message, ContentHelper::getContentInfo($post, false));
    }

    public function testGetContentInfoFallsBackToContentNameOnEmptyPreview()
    {
        $post = Post::findOne(['id' => 1]);
        $post->updateAttributes(['message' => '']);

        $this->assertEquals('post', ContentHelper::getContentInfo($post));
        $this->assertEquals('post', ContentHelper::getContentInfo($post, false));
    }

    public function testGetContentPlainTextInfoFallsBackToContentNameOnEmptyPreview()
    {
        $post = Post::findOne(['id' => 1]);
        $post->updateAttributes(['message' => '']);

        $this->assertEquals('post', ContentHelper::getContentPlainTextInfo($post));
        $this->assertEquals('post', ContentHelper::getContentPlainTextInfo($post, false));
    }
}
