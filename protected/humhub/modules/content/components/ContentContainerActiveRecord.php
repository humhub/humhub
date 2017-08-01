<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\libs\BasePermission;
use humhub\modules\content\models\Content;
use Yii;
use humhub\libs\ProfileBannerImage;
use humhub\libs\ProfileImage;
use humhub\modules\user\models\User;
use humhub\components\ActiveRecord;
use humhub\modules\content\models\ContentContainer;

/**
 * ContentContainerActiveRecord for ContentContainer Models e.g. Space or User.
 *
 * Required Methods:
 *      - getProfileImage()
 *      - getUrl()
 *
 * @property integer $id
 * @property integer $visibility
 *
 * @since 1.0
 * @author Luke
 */
abstract class ContentContainerActiveRecord extends ActiveRecord
{

    /**
     * @var ContentContainerPermissionManager
     */
    protected $permissionManager = null;

    /**
     * Returns the Profile Image Object for this Content Base
     *
     * @return ProfileImage
     */
    public function getProfileImage()
    {
        if ($this instanceof \humhub\modules\space\models\Space) {
            return new ProfileImage($this->guid, 'default_space');
        }
        return new ProfileImage($this->guid);
    }

    /**
     * Returns the Profile Banner Image Object for this Content Base
     *
     * @return ProfileBannerImage
     */
    public function getProfileBannerImage()
    {
        return new ProfileBannerImage($this->guid);
    }

    /**
     * Should be overwritten by implementation
     */
    public function getUrl()
    {
        return $this->createUrl();
    }

    /**
     * Creates url in content container scope.
     * E.g. add uguid or sguid parameter to parameters.
     *
     * @param string $route
     * @param array $params
     * @param boolean|string $scheme
     */
    public function createUrl($route = null, $params = array(), $scheme = false)
    {
        return "";
    }

    /**
     * Returns the display name of content container
     *
     * @since 0.11.0
     * @return string
     */
    public function getDisplayName()
    {
        return "Container: " . get_class($this) . " - " . $this->getPrimaryKey();
    }

    /**
     * Checks if the user is allowed to access private content in this container
     *
     * @param User $user
     * @return boolean can access private content
     */
    public function canAccessPrivateContent(User $user = null)
    {
        return false;
    }

    /**
     * Returns the wall output for this content container.
     * This is e.g. used in search results.
     *
     * @return string
     */
    public function getWallOut()
    {
        return "Default Wall Output for Class " . get_class($this);
    }
    
    public static function findByGuid($token)
    {
        return static::findOne(['guid' => $token]);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $contentContainer = new ContentContainer;
            $contentContainer->guid = $this->guid;
            $contentContainer->class = $this->className();
            $contentContainer->pk = $this->getPrimaryKey();
            if ($this instanceof User) {
                $contentContainer->owner_user_id = $this->id;
            } elseif ($this->hasAttribute('created_by')) {
                $contentContainer->owner_user_id = $this->created_by;
            }

            $contentContainer->save();

            $this->contentcontainer_id = $contentContainer->id;
            $this->update(false, ['contentcontainer_id']);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        ContentContainer::deleteAll([
            'pk' => $this->getPrimaryKey(),
            'class' => $this->className()
        ]);

        parent::afterDelete();
    }

    /**
     * Returns the related ContentContainer model (e.g. Space or User)
     *
     * @see ContentContainer
     * @return ContentContainer
     */
    public function getContentContainerRecord()
    {
        return $this->hasOne(ContentContainer::className(), ['pk' => 'id'])
                        ->andOnCondition(['class' => self::className()]);
    }
    
    /**
     * Checks if the current user has the given Permission on this ContentContainerActiveRecord.
     * This is a shortcut for `$this->getPermisisonManager()->can()`.
     *
     * The following example will check if the current user has MyPermission on the given $contentContainer
     *
     * ```php
     * $contentContainer->can(MyPermisison::class);
     * ```
     * 
     * Note: This method is used to verify ContentContainerPermissions and not GroupPermissions.
     * 
     * @param string|string[]|BasePermission $permission
     * @see PermissionManager::can()
     * @return boolean
     * @since 1.2
     */
    public function can($permission, $params = [], $allowCaching = true)
    {
        return $this->getPermissionManager()->can($permission, $params, $allowCaching);
    }

    /**
     * Returns a ContentContainerPermissionManager instance for this ContentContainerActiveRecord as permission object
     * and the given user (or current user if not given) as permission subject.
     *
     * @param User $user
     * @return ContentContainerPermissionManager
     */
    public function getPermissionManager(User $user = null)
    {
        if($user && !$user->is(Yii::$app->user->getIdentity())) {
            return new ContentContainerPermissionManager([
                'contentContainer' => $this,
                'subject' => $user
            ]);
        }

        if ($this->permissionManager !== null) {
            return $this->permissionManager;
        }

        return $this->permissionManager = new ContentContainerPermissionManager([
            'contentContainer' => $this
        ]);
    }

    /**
     * Returns user group for the given $user or current logged in user if no $user instance was provided.
     *
     * @param User|null $user
     * @return string
     */
    public function getUserGroup(User $user = null)
    {
        return "";
    }

    /**
     * Returns user groups
     */
    public function getUserGroups()
    {
        return [];
    }
    
    /**
     * Returns weather or not the contentcontainer is archived. (Default false).
     * @return boolean 
     * @since 1.2
     */
    public function isArchived()
    {
        return false;
    }

    /**
     * Determines the default visibility of this container type.
     *
     * @return int
     */
    public function getDefaultContentVisibility()
    {
        return Content::VISIBILITY_PRIVATE;
    }

    /**
     * Checks the current visibility setting of this ContentContainerActiveRecord
     * @param $visibility
     * @return bool
     */
    public function isVisibleFor($visibility)
    {
        return $this->visibility == $visibility;
    }

}
