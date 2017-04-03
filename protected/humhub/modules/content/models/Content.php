<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\permissions\ManageContent;

/**
 * This is the model class for table "content".
 *
 *
 * The followings are the available columns in table 'content':
 * @property integer $id
 * @property string $guid
 * @property string $object_model
 * @property integer $object_id
 * @property integer $visibility
 * @property integer $pinned
 * @property string $archived
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @since 0.5
 */
class Content extends ContentDeprecated
{

    /**
     * A array of user objects which should informed about this new content.
     *
     * @var Array User
     */
    public $notifyUsersOfNewContent = [];

    /**
     * @var int The private visibility mode (e.g. for space member content or user profile posts for friends)
     */
    const VISIBILITY_PRIVATE = 0;

    /**
     * @var int Public visibility mode, e.g. content which are visibile for followers
     */
    const VISIBILITY_PUBLIC = 1;

    /**
     * @var int Owner visibility mode, only visible for contentContainer + content owner
     */
    const VISIBILITY_OWNER = 2;

    /**
     * @var ContentContainerActiveRecord the Container (e.g. Space or User) where this content belongs to.
     */
    protected $_container = null;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \humhub\components\behaviors\PolymorphicRelation::className(),
                'mustBeInstanceOf' => array(ContentActiveRecord::className()),
            ],
            [
                'class' => \humhub\components\behaviors\GUID::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'visibility', 'pinned'], 'integer'],
            [['archived'], 'safe'],
            [['guid'], 'string', 'max' => 45],
            [['object_model'], 'string', 'max' => 100],
            [['object_model', 'object_id'], 'unique', 'targetAttribute' => ['object_model', 'object_id'], 'message' => 'The combination of Object Model and Object ID has already been taken.'],
            [['guid'], 'unique']
        ];
    }

    /**
     * Returns a Content Object by given Class and ID
     *
     * @param string $className Class Name of the Content
     * @param int $id Primary Key
     */
    public static function Get($className, $id)
    {
        $content = self::findOne(['object_model' => $className, 'object_id' => $id]);
        if ($content != null) {
            return $className::findOne(['id' => $id]);
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->object_model == "" || $this->object_id == "") {
            throw new Exception("Could not save content with object_model or object_id!");
        }


        // Set some default values
        if (!$this->archived) {
            $this->archived = 0;
        }
        if (!$this->visibility) {
            $this->visibility = self::VISIBILITY_PRIVATE;
        }
        if (!$this->pinned) {
            $this->pinned = 0;
        }

        if ($insert) {
            if ($this->created_by == "") {
                $this->created_by = Yii::$app->user->id;
            }
        }

        $this->stream_sort_date = new \yii\db\Expression('NOW()');

        if ($this->created_by == "") {
            throw new Exception("Could not save content without created_by!");
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $contentSource = $this->getPolymorphicRelation();

        foreach ($this->notifyUsersOfNewContent as $user) {
            $contentSource->follow($user->id);
        }

        if ($insert && !$contentSource instanceof \humhub\modules\activity\models\Activity) {

            if ($this->container !== null) {
                $notifyUsers = array_merge($this->notifyUsersOfNewContent, Yii::$app->notification->getFollowers($this));

                \humhub\modules\content\notifications\ContentCreated::instance()
                        ->from($this->user)
                        ->about($contentSource)
                        ->sendBulk($notifyUsers);

                \humhub\modules\content\activities\ContentCreated::instance()
                        ->about($contentSource)->save();


                Yii::$app->live->send(new \humhub\modules\content\live\NewContent([
                    'sguid' => ($this->container instanceof Space) ? $this->container->guid : null,
                    'uguid' => ($this->container instanceof User) ? $this->container->guid : null,
                    'originator' => $this->user->guid,
                    'contentContainerId' => $this->container->contentContainerRecord->id,
                    'visibility' => $this->visibility,
                    'contentId' => $this->id
                ]));
            }
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        // Try delete the underlying object (Post, Question, Task, ...)
        $this->resetPolymorphicRelation();
        if ($this->getPolymorphicRelation() !== null) {
            $this->getPolymorphicRelation()->delete();
        }

        parent::afterDelete();
    }

    /**
     * Returns the visibility of the content object
     *
     * @return Integer
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Checks if the content visiblity is set to public.
     *
     * @return boolean
     */
    public function isPublic()
    {
        return $this->visibility == self::VISIBILITY_PUBLIC;
    }
    
    /**
     * Checks if the content visiblity is set to private.
     *
     * @return boolean
     */
    public function isPrivate()
    {
        return $this->visibility == self::VISIBILITY_PRIVATE;
    }

    /**
     * Checks if the content object is pinned
     *
     * @return Boolean
     */
    public function isPinned()
    {
        return ($this->pinned);
    }

    /**
     * Pins the content object
     */
    public function pin()
    {
        $this->pinned = 1;
        //This prevents the call of beforesave, and the setting of update_at
        $this->updateAttributes(['pinned']);
    }

    /**
     * Unpins the content object
     */
    public function unpin()
    {

        $this->pinned = 0;
        $this->updateAttributes(['pinned']);
    }

    /**
     * Checks if the user can pin this content.
     * This is only allowed for workspace owner.
     *
     * @return boolean
     */
    public function canPin()
    {
        if ($this->isArchived()) {
            return false;
        }

        return $this->getContainer()->permissionManager->can(new ManageContent());
    }

    /**
     * Creates a list of pinned content objects of the wall
     *
     * @return Int
     */
    public function countPinnedItems()
    {
        return Content::find()->where(['content.contentcontainer_id' => $this->contentcontainer_id, 'content.pinned' => 1])->count();
    }

    /**
     * Checks if current content object is archived
     *
     * @return boolean
     */
    public function isArchived()
    {
        return $this->archived || ($this->getContainer() !== null && $this->getContainer()->isArchived());
    }

    /**
     * Checks if the current user can archive this content.
     * The content owner and the workspace admin can archive contents.
     *
     * @return boolean
     */
    public function canArchive()
    {
        // Disabled on user profiles, there is no stream filter available yet.
        if ($this->getContainer() instanceof User) {
            return false;
        }

        return $this->getContainer()->permissionManager->can(new ManageContent());
    }

    /**
     * Archives the content object
     */
    public function archive()
    {
        if ($this->canArchive()) {

            if ($this->isPinned()) {
                $this->unpin();
            }

            $this->archived = 1;
            if (!$this->save()) {
                throw new Exception("Could not archive content!" . print_r($this->getErrors(), 1));
            }
        }
    }

    /**
     * Unarchives the content object
     */
    public function unarchive()
    {
        if ($this->canArchive()) {

            $this->archived = 0;
            $this->save();
        }
    }

    /**
     * Returns the url of this content.
     *
     * By default is returns the url of the wall entry.
     *
     * Optionally it's possible to create an own getUrl method in the underlying
     * HActiveRecordContent (e.g. Post) to overwrite this behavior.
     * e.g. in case there is no wall entry available for this content.
     *
     * @since 0.11.1
     */
    public function getUrl()
    {
        if (method_exists($this->getPolymorphicRelation(), 'getUrl')) {
            return $this->getPolymorphicRelation()->getUrl();
        }

        return Url::toRoute(['/content/perma', 'id' => $this->id]);
    }

    /**
     * Sets container (e.g. space or user record) for this content.
     *
     * @param ContentContainerActiveRecord $container
     * @throws Exception
     */
    public function setContainer(ContentContainerActiveRecord $container)
    {
        $this->contentcontainer_id = $container->contentContainerRecord->id;
        $this->_container = $container;
    }

    /**
     * Returns the content container (e.g. space or user record) of this content
     *
     * @return ContentContainerActiveRecord
     * @throws Exception
     */
    public function getContainer()
    {
        if ($this->_container != null) {
            return $this->_container;
        }

        if ($this->contentContainer !== null) {
            $this->_container = $this->contentContainer->getPolymorphicRelation();
        }

        return $this->_container;
    }

    /**
     * Relation to ContentContainer model
     * Note: this is not a Space or User instance!
     *
     * @since 1.1
     * @return \yii\db\ActiveQuery
     */
    public function getContentContainer()
    {
        return $this->hasOne(ContentContainer::className(), ['id' => 'contentcontainer_id']);
    }

    /**
     * Checks if user can edit this content
     *
     * @todo create possibility to define own canEdit in ContentActiveRecord
     * @todo also check content containers canManage content permission
     * @since 1.1
     * @param User $user
     * @return boolean can edit this content
     */
    public function canEdit($user = null)
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        if ($user === null) {
            $user = Yii::$app->user->getIdentity();
        }

        // Only owner can edit his content
        if ($user !== null && $this->created_by == $user->id) {
            return true;
        }

        if ($this->getContainer() !== null && $this->getContainer()->permissionManager->can(new ManageContent())) {
            return true;
        }

        // Global Admin can edit/delete arbitrarily content
        if (Yii::$app->getModule('content')->adminCanEditAllContent && Yii::$app->user->getIdentity()->isSystemAdmin()) {
            return true;
        }

        // Check if underlying content implements own canEdit method
        // ToDo: Implement this as interface 
        if (method_exists($this->getPolymorphicRelation(), 'canEdit') && $this->getPolymorphicRelation()->canEdit($user)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if user can view this content
     *
     * @since 1.1
     * @param User $user
     * @return boolean can view this content
     */
    public function canView($user = null)
    {
        if ($user === null && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        }

        // Check Guest Visibility
        if ($user === null) {
            if ($this->isPublic() && Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess')) {
                // Check container visibility for guests
                if (($this->container instanceof Space && $this->container->visibility == Space::VISIBILITY_ALL) ||
                        ($this->container instanceof User && $this->container->visibility == User::VISIBILITY_ALL)) {
                    return true;
                }
            }
            return false;
        }

        // Public visible content
        if ($this->isPublic()) {
            return true;
        }

        // Check Superadmin can see all content option
        if ($user->isSystemAdmin() && Yii::$app->getModule('content')->adminCanViewAllContent) {
            return true;
        }

        if ($this->isPrivate() && $this->getContainer()->canAccessPrivateContent($user)) {
            return true;
        }

        return false;
    }

    /**
     * Updates the wall/stream sorting time of this content for "updated at" sorting
     */
    public function updateStreamSortTime()
    {
        $this->updateAttributes(['stream_sort_date' => new \yii\db\Expression('NOW()')]);
    }

}
