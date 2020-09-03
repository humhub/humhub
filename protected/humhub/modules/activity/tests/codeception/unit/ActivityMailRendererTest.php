<?php
/**
 * Created by PhpStorm.
 * User: kingb
 * Date: 26.09.2018
 * Time: 18:59
 */

namespace humhub\modules\activity\tests\codeception\unit;


use humhub\modules\activity\components\ActivityMailRenderer;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\activities\ContentCreated;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;

class ActivityMailRendererTest extends HumHubDbTestCase
{
    public function testEncoding()
    {
        $this->becomeUser('Admin');

        $space = Space::findOne(1);
        $post = new Post($space, ['message' => 'This > <b>Test</b> <scrip>asdf</scrip>']);
        $this->assertTrue($post->save());

        $activity = Activity::findOne(['object_model' => Post::class, 'object_id' => $post->id, 'class' => ContentCreated::class]);
        $this->assertNotNull($activity);


        $mailRenderer = new ActivityMailRenderer();
        $html = $mailRenderer->render($activity->getActivityBaseClass());
        $this->assertContains('2&gt;&1', $html);
    }
}
