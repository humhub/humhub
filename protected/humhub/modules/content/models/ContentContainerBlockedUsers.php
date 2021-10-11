<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\components\ActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;

/**
 * Class ContentContainerBlockedUsers
 *
 * @property integer $contentcontainer_id
 * @property integer $user_id
 *
 * @since 1.10
 */
class ContentContainerBlockedUsers extends ActiveRecord
{
    const BLOCKED_USERS_SETTING = 'blockedUsers';

    public static function tableName()
    {
        return 'contentcontainer_blocked_users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contentcontainer_id', 'user_id'], 'required'],
            [['contentcontainer_id', 'user_id'], 'integer'],
            [['user_id'], 'validateUser'],
        ];
    }

    public function validateUser()
    {
        $blockingUser = $this->getUser();
        $contentContainer = $this->getContentContainer();

        if (!$blockingUser || !$contentContainer) {
            $this->addError('user_id', 'User and container ids should be specified!');
        }

        if ($blockingUser->is($contentContainer)) {
            $this->addError('user_id', Yii::t('ContentModule.base', 'You cannot block the user of the same container!'));
        }

        if (!Yii::$app->user->isGuest && $blockingUser->is(Yii::$app->user->getIdentity())) {
            $this->addError('user_id', Yii::t('ContentModule.base', 'You cannot block yourself!'));
        }

        if (($contentContainer instanceof Space) && $contentContainer->isSpaceOwner($blockingUser)) {
            $this->addError('user_id', Yii::t('ContentModule.base', 'You cannot block the space owner!'));
        }
    }

    /**
     * Get blocked user guids of the Content Container
     *
     * @param ContentContainerActiveRecord $contentContainer
     * @return int[]
     */
    public static function getGuidsByContainer(ContentContainerActiveRecord $contentContainer): array
    {
        return self::find()
            ->select('user.guid')
            ->innerJoin(User::tableName(), 'user.id = user_id')
            ->where([self::tableName() . '.contentcontainer_id' => $contentContainer->contentcontainer_id])
            ->column();
    }

    /**
     * Update blocked users of the Content Container
     *
     * @param ContentContainerActiveRecord $contentContainer
     * @param string[]|null $newBlockedUserGuids
     */
    public static function updateByContainer(ContentContainerActiveRecord $contentContainer, $newBlockedUserGuids = null)
    {
        /* @var Module $moduleUser */
        $moduleUser = Yii::$app->getModule('user');
        if (!$moduleUser->allowBlockUsers()) {
            return;
        }

        self::deleteByContainer($contentContainer);

        if (empty($newBlockedUserGuids)) {
            return;
        }

        $newBlockedUsers = User::find()->where(['IN', 'guid', $newBlockedUserGuids])->all();

        $newBlockedUserIds = [];
        foreach ($newBlockedUsers as $newBlockedUser) {
            /* @var User $newBlockedUser */
            $newBlockedUserRelation = new ContentContainerBlockedUsers();
            $newBlockedUserRelation->contentcontainer_id = $contentContainer->contentcontainer_id;
            $newBlockedUserRelation->user_id = $newBlockedUser->id;
            if ($newBlockedUserRelation->save()) {
                $newBlockedUserIds[] = $newBlockedUser->id;
            }
        }

        $contentContainer->settings->set(self::BLOCKED_USERS_SETTING, empty($newBlockedUserIds) ? null : implode(',', $newBlockedUserIds));
    }

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if (!$this->validate()) {
            return false;
        }

        return parent::save($runValidation, $attributeNames);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->afterBlockUserForUser();
        $this->afterBlockUserForSpace();
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Call additional actions after save a blocked user for a User container
     */
    public function afterBlockUserForUser()
    {
        $containerUser = $this->getContentContainer();
        if (!($containerUser instanceof User)) {
            return;
        }

        if ($blockedUser = $this->getUser()) {
            Friendship::cancel($containerUser, $blockedUser);
            $containerUser->unfollow($blockedUser->id);
        }
    }

    /**
     * Call additional actions after save a blocked user for a Space container
     */
    public function afterBlockUserForSpace()
    {
        $space = $this->getContentContainer();
        if (!($space instanceof Space)) {
            return;
        }

        if ($blockedUser = $this->getUser()) {
            $space->removeMember($blockedUser->id);
            $space->unfollow($blockedUser->id);
        }
    }

    /**
     * Delete blocked user relations of the Content Container
     *
     * @param ContentContainerActiveRecord $contentContainer
     */
    public static function deleteByContainer(ContentContainerActiveRecord $contentContainer)
    {
        $blockedUserRelations = self::findAll(['contentcontainer_id' => $contentContainer->contentcontainer_id]);

        foreach ($blockedUserRelations as $blockedUserRelation) {
            $blockedUserRelation->delete();
        }

        $contentContainer->settings->delete(self::BLOCKED_USERS_SETTING);
    }

    /**
     * Get Content Container(Space/User) of this relation
     *
     * @return ContentContainerActiveRecord|null
     */
    public function getContentContainer(): ?ContentContainerActiveRecord
    {
        if (empty($this->contentcontainer_id)) {
            return null;
        }

        $contentContainer = ContentContainer::findOne($this->contentcontainer_id);

        return $contentContainer ? $contentContainer->getPolymorphicRelation() : null;
    }

    /**
     * Get blocked User of this relation
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return empty($this->user_id) ? null : User::findOne($this->user_id);
    }
}
