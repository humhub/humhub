<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\modules\friendship\models\Friendship;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\ui\widgets\CounterSetItem;
use humhub\modules\ui\widgets\CounterSet;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Url;


/**
 * Class ProfileHeaderCounter
 *
 * @since 1.3
 * @package humhub\modules\user\widgets
 */
class ProfileHeaderCounterSet extends CounterSet
{

    /**
     * @var User
     */
    public $user;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (Yii::$app->getModule('friendship')->getIsEnabled()) {
            $this->counters[] = new CounterSetItem([
                'label' => Yii::t('UserModule.profile', 'Friends'),
                'value' => Friendship::getFriendsQuery($this->user)->count(),
                'url' => Yii::$app->user->isGuest ? null : '#',
                'linkOptions' => Yii::$app->user->isGuest ? [] : [
                    'data-action-click' => 'ui.modal.load',
                    'data-action-url' => Url::to(['/friendship/list/popup', 'userId' => $this->user->id])
                ]
            ]);
        }

        if (!Yii::$app->getModule('user')->disableFollow) {
            $this->counters[] = new CounterSetItem([
                'label' => Yii::t('UserModule.profile', 'Followers'),
                'value' => $this->user->getFollowersQuery()->count(),
                'url' => Yii::$app->user->isGuest ? null : '#',
                'linkOptions' => Yii::$app->user->isGuest ? [] : [
                    'data-action-click' => 'ui.modal.load',
                    'data-action-url' => Url::to(['/user/profile/follower-list', 'container' => $this->user])
                ]
            ]);

            $this->counters[] = new CounterSetItem([
                'label' => Yii::t('UserModule.profile', 'Following'),
                'value' => $this->user->getFollowingQuery(User::find())->count(),
                'url' => Yii::$app->user->isGuest ? null : '#',
                'linkOptions' => Yii::$app->user->isGuest ? [] : [
                    'data-action-click' => 'ui.modal.load',
                    'data-action-url' => Url::to(['/user/profile/followed-users-list', 'container' => $this->user])
                ]
            ]);
        }

        $spaceMembershipCount = Membership::getUserSpaceQuery($this->user)
            ->andWhere(['!=', 'space.visibility', Space::VISIBILITY_NONE])
            ->andWhere(['space.status' => Space::STATUS_ENABLED])
            ->count();

        $this->counters[] = new CounterSetItem([
            'label' => Yii::t('UserModule.profile', 'Spaces'),
            'value' => $spaceMembershipCount,
            'url' => Yii::$app->user->isGuest ? null : '#',
            'linkOptions' => Yii::$app->user->isGuest ? [] : [
                'data-action-click' => 'ui.modal.load',
                'data-action-url' => Url::to(['/user/profile/space-membership-list', 'container' => $this->user])
            ]
        ]);

        parent::init();
    }

}
