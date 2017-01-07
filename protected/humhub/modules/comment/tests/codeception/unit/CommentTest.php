<?php

namespace tests\codeception\unit\modules\comment\components;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\post\models\Post;
use humhub\modules\comment\models\Comment;

class ContentContainerStreamTest extends HumHubDbTestCase
{

    use Specify;

    public function testCreateComment()
    {
        $this->becomeUser('User2');
        
        $comment = new Comment([
            'message' => 'User2 comment!',
            'object_model' => Post::className(),
            'object_id' => 11
        ]);

        $comment->save();

        print_r($comment->content->getPolymorphicRelation()->getFollowers(null, true, true)->all());
        die();
        
        $this->assertNotEmpty($comment->id);
        $this->assertNotEmpty($comment->content->getPolymorphicRelation()->getFollowers(null, true, true));
        
        $this->assertNotNull(\humhub\modules\activity\models\Activity::findOne(['source_class' => Comment::class, 'source_pk' => $comment->id]));
        $this->assertNotNull(\humhub\modules\notification\models\Notification::findOne(['source_class' => Comment::class, 'source_pk' => $comment->id]));
    }

}
