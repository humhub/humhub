<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content;

use tests\codeception\_support\HumHubDbTestCase;
use humhub\modules\post\models\Post;

use humhub\modules\space\models\Space;
use humhub\modules\content\models\Content;

class ContentEditTest extends HumHubDbTestCase
{

    public function testNewContentIsNotEdited()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 2]);

        $post1 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test']);
        $this->assertTrue($post1->save());
        $this->assertFalse($post1->content->isUpdated());

        // Reload content
        $post1 = Post::findOne(['id' => $post1->id]);
        $this->assertFalse($post1->content->isUpdated());
    }

    public function testEditedContentIsEdited()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 2]);

        $post1 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test']);
        $this->assertTrue($post1->save());

        // Wait a second in order to prevent created_at = edited_at
        sleep(1);

        // Reload content
        $post1 = Post::findOne(['id' => $post1->id]);
        $post1->message = 'Updated Message';
        $this->assertTrue($post1->save());


        $this->assertTrue($post1->content->isUpdated());

        // Reload content
        $post1 = Post::findOne(['id' => $post1->id]);
        $this->assertTrue($post1->content->isUpdated());
    }

}
