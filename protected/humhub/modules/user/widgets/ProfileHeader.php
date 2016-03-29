<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

/**
 * Displays the profile header of a user
 * 
 * @since 0.5
 * @author Luke
 */
class ProfileHeader extends \yii\base\Widget
{

    /**
     * @var User
     */
    public $user;

    /**
     * @var boolean is owner of the current profile 
     */
    protected $isProfileOwner = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        /**
         * Try to autodetect current user by controller
         */
        if ($this->user === null) {
            $this->user = $this->getController()->getUser();
        }

        // Check if profile header can be edited
        if (!Yii::$app->user->isGuest) {
            if (Yii::$app->user->getIdentity()->isSystemAdmin() && Yii::$app->params['user']['adminCanChangeProfileImages']) {
                $this->isProfileOwner = true;
            } elseif (Yii::$app->user->id == $this->user->id) {
                $this->isProfileOwner = true;
            }
        }

        $this->isProfileOwner = (Yii::$app->user->id == $this->user->id);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $friendshipsEnabled = Yii::$app->getModule('friendship')->getIsEnabled();

        $countFriends = 0;
        if ($friendshipsEnabled) {
            $countFriends = \humhub\modules\friendship\models\Friendship::getFriendsQuery($this->user)->count();
        }

        $countFollowing = $this->user->getFollowingCount(User::className()) + $this->user->getFollowingCount(Space::className());

        return $this->render('profileHeader', array(
                    'user' => $this->user,
                    'isProfileOwner' => $this->isProfileOwner,
                    'friendshipsEnabled' => $friendshipsEnabled,
                    'countFriends' => $countFriends,
                    'countFollowers' => $this->user->getFollowerCount(),
                    'countFollowing' => $countFollowing,
                    'countSpaces' => count($this->user->spaces),
        ));
    }

}

?>
