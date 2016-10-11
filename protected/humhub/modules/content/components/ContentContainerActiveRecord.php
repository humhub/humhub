<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\libs\ProfileBannerImage;
use humhub\libs\ProfileImage;
use humhub\modules\user\models\User;
use humhub\components\ActiveRecord;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\content\models\Wall;

/**
 * ContentContainerActiveRecord for ContentContainer Models e.g. Space or User.
 *
 * Required Attributes:
 *      - wall_id
 *      - guid
 *
 * Required Methods:
 *      - getProfileImage()
 *      - getUrl()
 *
 * @property integer $id
 * @since 1.0
 * @author Luke
 */
class ContentContainerActiveRecord extends ActiveRecord
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

            $wall = new Wall();
            $wall->object_model = $this->className();
            $wall->object_id = $this->id;
            $wall->save();
            $this->wall_id = $wall->id;
            $this->update(false, ['wall_id']);

            $contentContainer = new ContentContainer;
            $contentContainer->guid = $this->guid;
            $contentContainer->class = $this->className();
            $contentContainer->pk = $this->getPrimaryKey();
            $contentContainer->wall_id = $this->wall_id;
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
     * @return \yii\db\ActiveQuery
     */
    public function getContentContainerRecord()
    {
        return $this->hasOne(ContentContainer::className(), ['pk' => 'id'])
                        ->andOnCondition(['class' => self::className()]);
    }

    /**
     * Returns the permissionManager of this container
     *
     * @return ContentContainerPermissionManager
     */
    public function getPermissionManager()
    {
        if ($this->permissionManager !== null) {
            return $this->permissionManager;
        }

        $this->permissionManager = new ContentContainerPermissionManager;
        $this->permissionManager->contentContainer = $this;
        return $this->permissionManager;
    }

    /**
     * Returns current users group
     *
     * @return string
     */
    public function getUserGroup()
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

}
