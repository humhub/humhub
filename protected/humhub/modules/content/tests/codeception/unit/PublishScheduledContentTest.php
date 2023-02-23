<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\content;

use DateTime;
use humhub\modules\content\jobs\PublishScheduledContents;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\WallCreateContentForm;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class PublishScheduledContentTest extends HumHubDbTestCase
{
    public function testPublishScheduledContent()
    {
        $this->becomeUser('Admin');

        $postA = $this->createScheduledPost('-1 hour');
        $postB = $this->createScheduledPost('now');
        $postC = $this->createScheduledPost('1 hour');
        $postD = $this->createScheduledPost('tomorrow');

        (new PublishScheduledContents())->run();

        $postA = Post::findOne($postA->id);
        $this->assertEquals(Content::STATE_PUBLISHED, $postA->content->state);

        $postB = Post::findOne($postB->id);
        $this->assertEquals(Content::STATE_PUBLISHED, $postB->content->state);

        $postC = Post::findOne($postC->id);
        $this->assertEquals(Content::STATE_SCHEDULED, $postC->content->state);

        $postD = Post::findOne($postD->id);
        $this->assertEquals(Content::STATE_SCHEDULED, $postD->content->state);
    }

    private function createScheduledPost($date): Post
    {
        $datetime = (new DateTime($date))->format('Y-m-d H:i:s');

        $space = Space::findOne(1);
        $post = new Post($space, ['message' => 'Post for test scheduling']);
        Yii::$app->request->setBodyParams([
            'state' => Content::STATE_SCHEDULED,
            'scheduledDate' => $datetime
        ]);

        $result = WallCreateContentForm::create($post, $space);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals(Content::STATE_SCHEDULED, $post->content->state);
        $this->assertEquals($datetime, $post->content->scheduled_at);

        return $post;
    }
}
