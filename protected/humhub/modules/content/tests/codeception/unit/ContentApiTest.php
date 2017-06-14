<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\post\models\Post;

use humhub\modules\space\models\Space;
use humhub\modules\content\models\Content;
use humhub\modules\stream\actions\ContentContainerStream;

class ContentApiTest extends HumHubDbTestCase
{

    use Specify;

    public function testConstructor()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 2]);

        $post1 = new Post($space, Content::VISIBILITY_PUBLIC);
        $this->assertEquals($space->id, $post1->content->container->id);
        $this->assertEquals(Content::VISIBILITY_PUBLIC, $post1->content->visibility);

        $post2 = new Post($space, Content::VISIBILITY_PRIVATE, ['message' => 'Hello!']);
        $this->assertEquals($space->id, $post2->content->container->id);
        $this->assertEquals(Content::VISIBILITY_PRIVATE, $post2->content->visibility);
        $this->assertEquals('Hello!', $post2->message);

        $post3 = new Post(['message' => 'Hello!']);
        $this->assertEquals('Hello!', $post3->message);
    }
}
