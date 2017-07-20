<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content;

use humhub\modules\comment\permissions\CreateComment;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentContainerPermission;
use humhub\modules\content\permissions\ManageContent;
use humhub\modules\content\tests\codeception\unit\TestContent;
use humhub\modules\content\tests\codeception\unit\TestContentManagePermission;
use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\post\models\Post;

use humhub\modules\space\models\Space;
use humhub\modules\content\models\Content;
use humhub\modules\stream\actions\ContentContainerStream;

class ContentActiveRecordTest extends HumHubDbTestCase
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

    public function testManagePermission()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 3]);

        $model = new TestContent($space, Content::VISIBILITY_PUBLIC);
        $model->setManagePermission([ManageContent::class]);

        $this->becomeUser('User1');

        $this->assertFalse($model->content->canEdit());

        $this->setPermission($space, Space::USERGROUP_MEMBER, new ManageContent, 1);

        $this->assertTrue($model->content->canEdit());

        $model->setManagePermission(new TestContentManagePermission);

        $this->assertFalse($model->content->canEdit());

        $model->setManagePermission([ManageContent::class, TestContentManagePermission::class]);

        $this->assertTrue($model->content->canEdit());
    }

    function setPermission(ContentContainerActiveRecord $contentContianer, $groupId, $permission, $state = 1)
    {
        $groupPermission = new ContentContainerPermission();
        $groupPermission->permission_id = $permission->id;
        $groupPermission->group_id = $groupId;
        $groupPermission->contentcontainer_id = $contentContianer->contentContainerRecord->id;
        $groupPermission->module_id = $permission->moduleId;
        $groupPermission->class = $permission->className();
        $groupPermission->state = $state;
        $groupPermission->save();
        $contentContianer->getPermissionManager()->clear();
    }
}
