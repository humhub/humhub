<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content;

use humhub\libs\BasePermission;
use humhub\modules\content\models\Content;
use humhub\modules\content\permissions\CreatePublicContent;
use humhub\modules\content\tests\codeception\unit\TestContent;
use humhub\modules\space\models\Space;
use modules\content\tests\codeception\_support\ContentModelTest;
use Yii;
use yii\base\Exception;

class ContentVisibilityTest extends ContentModelTest
{
    public function testDefaultVisibilityPrivateSpace()
    {
        $this->space->visibility = Space::VISIBILITY_NONE;

        $newModel = new TestContent($this->space, [
            'message' => 'Test',
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PRIVATE);
    }

    public function testDefaultVisibilityProtectedSpace()
    {
        $this->space->visibility = Space::VISIBILITY_REGISTERED_ONLY;

        $newModel = new TestContent($this->space, [
            'message' => 'Test',
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PRIVATE);
    }

    public function testDefaultVisibilityPublicSpace()
    {
        $this->space->visibility = Space::VISIBILITY_ALL;

        $newModel = new TestContent($this->space, [
            'message' => 'Test',
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PRIVATE);
    }

    public function testCreatePublicContentOnPublicSpace()
    {
        $this->space->visibility = Space::VISIBILITY_ALL;

        $newModel = new TestContent($this->space, Content::VISIBILITY_PUBLIC, [
            'message' => 'Test',
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PUBLIC);
    }

    public function testCreatePublicContentOnProtectedSpace()
    {
        $this->space->addMember(Yii::$app->user->id);
        $this->space->visibility = Space::VISIBILITY_REGISTERED_ONLY;

        $newModel = new TestContent($this->space, Content::VISIBILITY_PUBLIC, [
            'message' => 'Test',
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PUBLIC);
    }

    public function testCreateContentOnDefaultContentVisibilityPublic()
    {
        $this->space->visibility = Space::VISIBILITY_ALL;
        $this->space->default_content_visibility = Content::VISIBILITY_PUBLIC;

        $newModel = new TestContent($this->space, [
            'message' => 'Test',
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PUBLIC);
    }

    public function testCreateContentOnDefaultContentVisibilityPrivate()
    {
        $this->space->visibility = Space::VISIBILITY_ALL;
        $this->space->default_content_visibility = Content::VISIBILITY_PRIVATE;

        $newModel = new TestContent($this->space, [
            'message' => 'Test',
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PRIVATE);
    }

    /**
     * Make sure private spaces can not produce public content
     *
     * Visibility integrity check missing!
     *
     * @skip
     * @throws Exception
     */
    public function testCreatePublicContentOnPrivateSpace()
    {
        $this->space->visibility = Space::VISIBILITY_NONE;

        $newModel = new TestContent($this->space, Content::VISIBILITY_PUBLIC, [
            'message' => 'Test',
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PRIVATE);
    }

    /**
     * Regression test for https://github.com/humhub/humhub/issues/8443
     *
     * Saving an already public record must not silently demote it to private just because the
     * current editor lacks CreatePublicContent in this container - that permission is about
     * *making* content public, not about keeping it public (matches actionToggleVisibility()/
     * VisibilityLink). Before the fix, ANY save of an already-public record (e.g. an unrelated
     * field edit) by such an editor - a system admin editing another user's content for
     * moderation, or a space role that can manage/edit content but not create public content -
     * silently flipped it back to private for everyone else.
     */
    public function testExistingPublicContentStaysPublicWhenEditorLacksCreatePublicContentPermission()
    {
        $this->becomeUser('User1');
        $this->space->visibility = Space::VISIBILITY_REGISTERED_ONLY;

        $newModel = new TestContent($this->space, Content::VISIBILITY_PUBLIC, [
            'message' => 'Test',
        ]);
        $this->assertTrue($newModel->save());
        $this->assertEquals(Content::VISIBILITY_PUBLIC, $newModel->content->visibility);

        // Take away this user's permission to *create* public content in this space ...
        $this->space->permissionManager->setGroupState(Space::USERGROUP_ADMIN, CreatePublicContent::class, BasePermission::STATE_DENY);

        // ... an unrelated re-save must not silently demote the already-public content.
        $newModel->message = 'Updated message';
        $this->assertTrue($newModel->save());
        $this->assertEquals(Content::VISIBILITY_PUBLIC, $newModel->content->visibility);
    }

    /**
     * The permission check must still block an *unauthorized* private -> public change - only the
     * "leave an already-public record alone" case (tested above) is exempted.
     */
    public function testPrivateContentCannotBeEscalatedToPublicByUserWithoutCreatePublicContentPermission()
    {
        $this->becomeUser('User1');
        $this->space->visibility = Space::VISIBILITY_REGISTERED_ONLY;

        $newModel = new TestContent($this->space, Content::VISIBILITY_PRIVATE, [
            'message' => 'Test',
        ]);
        $this->assertTrue($newModel->save());
        $this->assertEquals(Content::VISIBILITY_PRIVATE, $newModel->content->visibility);

        // User1 is the owner of space id=2 (see space fixture created_by), so getUserGroup()
        // resolves to USERGROUP_OWNER rather than USERGROUP_ADMIN - deny both to make sure
        // CreatePublicContent is actually denied regardless of which group applies.
        $this->space->permissionManager->setGroupState(Space::USERGROUP_OWNER, CreatePublicContent::class, BasePermission::STATE_DENY);
        $this->space->permissionManager->setGroupState(Space::USERGROUP_ADMIN, CreatePublicContent::class, BasePermission::STATE_DENY);

        $newModel->content->visibility = Content::VISIBILITY_PUBLIC;
        $this->assertTrue($newModel->content->save());
        $this->assertEquals(Content::VISIBILITY_PRIVATE, $newModel->content->visibility);
    }
}
