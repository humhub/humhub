<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\components\ActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
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
        ];
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
            if ($newBlockedUser->is($contentContainer)) {
                continue;
            }
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
}
