<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use humhub\modules\friendship\models\Friendship;

/**
 * UserFollowButtonWidget
 *
 * @author luke
 * @package humhub.modules_core.user.widgets
 * @since 0.11
 */
class UserFollowButton extends \yii\base\Widget
{

    /**
     * @var \humhub\modules\user\models\User the target user
     */
    public $user;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->user->isCurrentUser() || \Yii::$app->user->isGuest) {
            return;
        }

        if (Yii::$app->getModule('friendship')->getIsEnabled()) {
            // Don't show follow button, when friends
            if (Friendship::getFriendsQuery($this->user)->one() !== null) {
                return;
            }
        }

        return $this->render('followButton', array('user' => $this->user));
    }

}
