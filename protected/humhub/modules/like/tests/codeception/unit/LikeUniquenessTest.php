<?php

namespace tests\codeception\unit\modules\like;

use humhub\modules\like\models\Like;
use humhub\modules\like\services\LikeService;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use yii\db\Expression;
use yii\db\IntegrityException;

class LikeUniquenessTest extends HumHubDbTestCase
{
    public function testDuplicateContentLikeIsRejectedByDatabase()
    {
        $this->becomeUser('User2');

        $post = Post::findOne(['id' => 1]);
        $this->assertTrue((new LikeService($post))->like());

        $duplicate = new Like([
            'content_id' => $post->content->id,
            'content_addon_record_id' => new Expression('NULL'),
        ]);

        $this->expectException(IntegrityException::class);
        $duplicate->save(false);
    }
}
