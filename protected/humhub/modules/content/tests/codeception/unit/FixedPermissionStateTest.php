<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\tests\codeception\unit;

use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainerPermission;
use humhub\modules\content\permissions\ManageContent;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\ForbiddenHttpException;

/**
 * Permission states which are fixed for a group (e.g. "Manage content" for all registered users)
 * must not be writable via the permission grid endpoint.
 */
class FixedPermissionStateTest extends HumHubDbTestCase
{
    private Space $space;
    private User $outsider;
    private Post $post;

    protected function _before()
    {
        parent::_before();

        // Private space 5: only Admin (space admin) and User1 are members
        $this->becomeUser('Admin');
        $this->space = Space::findOne(['id' => 5]);
        $this->outsider = User::findOne(['username' => 'User2']);
        $this->assertFalse($this->space->isMember($this->outsider->id));

        $this->post = new Post($this->space, Content::VISIBILITY_PRIVATE, ['message' => 'Private post']);
        $this->assertTrue($this->post->save());
    }

    public function testFixedPairCannotBeChangedByRequest()
    {
        foreach ([Space::USERGROUP_USER, Space::USERGROUP_MEMBER, Space::USERGROUP_GUEST] as $groupId) {
            try {
                $this->postPermissionState($groupId, ManageContent::class, 'content', 1);
                $this->fail('Expected ForbiddenHttpException for group ' . $groupId);
            } catch (ForbiddenHttpException) {
            }

            $this->assertFalse($this->hasStoredState($groupId));
        }

        $this->assertFalse($this->post->content->canArchive($this->outsider));
    }

    public function testChangeablePairCanStillBeChangedByRequest()
    {
        $this->assertSame([], $this->postPermissionState(Space::USERGROUP_MODERATOR, ManageContent::class, 'content', 0));
        $this->assertTrue($this->hasStoredState(Space::USERGROUP_MODERATOR));
    }

    private function postPermissionState(string $groupId, string $permissionId, string $moduleId, int $state): ?array
    {
        Yii::$app->request->setBodyParams([
            'dropDownColumnSubmit' => 1,
            'permissionId' => $permissionId,
            'moduleId' => $moduleId,
            'state' => $state,
        ]);

        return $this->space->permissionManager->handlePermissionStateChange($groupId);
    }

    private function hasStoredState(string $groupId): bool
    {
        return ContentContainerPermission::find()->where([
            'contentcontainer_id' => $this->space->contentcontainer_id,
            'group_id' => $groupId,
            'permission_id' => (new ManageContent())->getId(),
        ])->exists();
    }
}
