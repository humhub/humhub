<?php

namespace humhub\modules\content\components;

use humhub\components\ActiveRecord;
use humhub\interfaces\DeletableInterface;
use humhub\interfaces\EditableInterface;
use humhub\interfaces\ViewableInterface;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\content\models\Content;
use humhub\modules\user\helpers\UserHelper;
use yii\base\InvalidCallException;
use yii\db\ActiveQuery;

/**
 * ContentAddonActiveRecord
 *
 * Content addons are content types like Comments or Likes.
 * These are always belongs to a Content object and uses the permissions and states of the assigned Content object.
 *
 * Mandatory model attributes:
 * - content_id
 * - updated_by
 * - updated_at
 *
 * @property-read Content $content
 */
abstract class ContentAddonActiveRecord extends ActiveRecord implements
    ViewableInterface,
    EditableInterface,
    DeletableInterface,
    ContentProvider
{
    /**
     * @var bool also update underlying contents last update stream sorting
     */
    protected $updateContentStreamSort = true;

    /**
     * @var bool automatic following of the addon creator to the related content
     */
    protected $automaticContentFollowing = true;


    public function beforeSave($insert): bool
    {
        if (!$this->content) {
            throw new InvalidCallException('Could not save ContentAddonActiveRecord without content.');
        }

        if ($insert && !$this->content->getStateService()->isPublished()) {
            throw new InvalidCallException('Could not save ContentAddonActiveRecord for unpublished state.');
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes): void
    {
        if ($this->automaticContentFollowing) {
            $this->content->model->follow($this->created_by);
        }

        if ($this->updateContentStreamSort) {
            $this->content->updateStreamSortTime();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function canDelete($user = null): bool
    {
        $user = UserHelper::getUserByParam($user);
        if ($user !== null && $this->created_by == $user->id) {
            return true;
        }

        return false;
    }

    public function canView($user = null): bool
    {
        return $this->content->canView($user);
    }

    public function canEdit($user = null): bool
    {
        $user = UserHelper::getUserByParam($user);

        if ($user === null) {
            return false;
        }

        if ($this->created_by === $user->id) {
            return true;
        }

        return $user->canManageAllContent();
    }

    public function getContent(): ActiveQuery
    {
        return $this->hasOne(Content::class, ['id' => 'content_id']);
    }
}
