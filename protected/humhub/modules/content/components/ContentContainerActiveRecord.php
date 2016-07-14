<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use Yii;
use humhub\modules\user\models\User;
use humhub\components\ActiveRecord;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\content\models\Wall;
use humhub\models\Setting;

/**
 *
 * Required Attributes:
 *      - wall_id
 *      - guid
 *
 * Required Methods:
 *      - getProfileImage()
 *      - getUrl()
 *
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
            return new \humhub\libs\ProfileImage($this->guid, 'default_space');
        }
        return new \humhub\libs\ProfileImage($this->guid);
    }

    /**
     * Returns the Profile Banner Image Object for this Content Base
     *
     * @return ProfileBannerImage
     */
    public function getProfileBannerImage()
    {

        return new \humhub\libs\ProfileBannerImage($this->guid);
    }

    /**
     * Should be overwritten by implementation
     */
    public function getUrl()
    {
        return $this->createUrl();
    }

    /**
     * Check write permissions on content container.
     * Overwrite this with your own implementation.
     *
     * @param type $userId
     * @return boolean
     */
    public function canWrite($userId = "")
    {
        return false;
    }

    /**
     * Creates url in content container scope.
     * E.g. add uguid or sguid parameter to parameters.
     *
     * @param type $route
     * @param type $params
     * @param type $ampersand
     */
    public function createUrl($route, $params = array(), $ampersand = '&')
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

    public function canAccessPrivateContent(User $user = null)
    {
        return false;
    }

    public function getWallOut()
    {
        return "Default Wall Output for Class " . get_class($this);
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

        return parent::afterSave($insert, $changedAttributes);
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
     * Returns the related ContentContainer model
     *
     * @see ContentContainer
     * @return \yii\db\ActiveQuery
     */
    public function getContentContainerRecord()
    {
        return $this->hasOne(ContentContainer::className(), ['id' => 'content_container_id']);
    }

    public function getPermissionManager()
    {
        if ($this->permissionManager !== null) {
            return $this->permissionManager;
        }

        $this->permissionManager = new ContentContainerPermissionManager;
        $this->permissionManager->contentContainer = $this;
        return $this->permissionManager;
    }

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

?>
