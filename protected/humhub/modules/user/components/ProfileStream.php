<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\modules\space\models\Space;
use humhub\modules\stream\actions\ContentContainerStream;
use humhub\modules\user\models\User as UserModel;
use humhub\modules\user\Module;
use Yii;
use yii\base\InvalidConfigException;

/**
 * ProfileStream
 *
 * @package humhub\modules\user\components
 */
class ProfileStream extends ContentContainerStream
{

    /**
     * @inheritdoc
     */
    protected function handleContentContainer()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        if ($module->includeAllUserContentsOnProfile && $this->user !== null) {
            $profileUser = $this->contentContainer;
            if (!$profileUser instanceof UserModel) {
                throw new InvalidConfigException('ContentContainer must be related to a User record.');
            }


            $this->activeQuery->leftJoin('space', 'contentcontainer.pk=space.id AND contentcontainer.class=:spaceClass', [':spaceClass' => Space::class]);
            $this->activeQuery->leftJoin('user cuser', 'contentcontainer.pk=cuser.id AND contentcontainer.class=:userClass', [':userClass' => UserModel::class]);
            $this->activeQuery->leftJoin('space_membership',
                'contentcontainer.pk=space_membership.space_id AND contentcontainer.class=:spaceClass AND space_membership.user_id=:userId',
                [':userId' => $this->user->id, ':spaceClass' => Space::class]
            );

            $this->activeQuery->andWhere([
                'OR',
                ['content.created_by' => $profileUser->id],
                ['content.contentcontainer_id' => $profileUser->contentcontainer_id]
            ]);

            // Build Access Check based on Space Content Container
            $conditionSpace = 'space.id IS NOT NULL AND (';                              // space content
            $conditionSpace .= ' (space_membership.status=3)';                           // user is space member
            $conditionSpace .= ' OR (content.visibility=1 AND space.visibility != 0)';   // visibile space and public content
            $conditionSpace .= ')';

            // Build Access Check based on User Content Container
            $conditionUser = 'cuser.id IS NOT NULL AND (';                  // user content
            $conditionUser .= '   (content.visibility = 1) OR';             // public visible content
            $conditionUser .= '   (content.visibility = 0 AND content.contentcontainer_id=' . $this->user->contentContainerRecord->id . ')';  // private content of user
            if (Yii::$app->getModule('friendship')->getIsEnabled()) {
                $this->activeQuery->leftJoin('user_friendship cff', 'cuser.id=cff.user_id AND cff.friend_user_id=:fuid', [':fuid' => $this->user->id]);
                $conditionUser .= ' OR (content.visibility = 0 AND cff.id IS NOT NULL)';  // users are friends
            }
            $conditionUser .= ')';
            // Created content of is always visible
            $conditionUser .= 'OR content.created_by=' . $this->user->id;

            $this->activeQuery->andWhere("{$conditionSpace} OR {$conditionUser} OR content.contentcontainer_id IS NULL");

            $this->handlePinnedContent();
        } else {
            parent::handleContentContainer();
        }
    }
}
