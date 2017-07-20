<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Membership;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\user\controllers\ImageController;

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

        if (!Yii::$app->user->isGuest && Yii::$app->user->id == $this->user->id) {
            $this->isProfileOwner = true;
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $friendshipsEnabled = Yii::$app->getModule('friendship')->getIsEnabled();

        $countFriends = 0;
        if ($friendshipsEnabled) {
            $countFriends = Friendship::getFriendsQuery($this->user)->count();
        }

        $countFollowing = $this->user->getFollowingCount(User::className());

        /* @var $imageController ImageController  */
        $imageController = new ImageController('image-controller', null, ['user' => $this->user]);

        return $this->render('profileHeader', array(
                    'user' => $this->user,
                    'isProfileOwner' => $this->isProfileOwner,
                    'friendshipsEnabled' => $friendshipsEnabled,
                    'followingEnabled' => !Yii::$app->getModule('user')->disableFollow,
                    'countFriends' => $countFriends,
                    'countFollowers' => $this->user->getFollowerCount(),
                    'countFollowing' => $countFollowing,
                    'countSpaces' => $this->getFollowingSpaceCount(),
                    'allowModifyProfileImage' => $imageController->allowModifyProfileImage,
                    'allowModifyProfileBanner' => $imageController->allowModifyProfileBanner,
        ));
    }

    /**
     * Returns the number of followed public space
     * 
     * @return int the follow count
     */
    protected function getFollowingSpaceCount()
    {
        return Membership::getUserSpaceQuery($this->user)
                        ->andWhere(['!=', 'space.visibility', Space::VISIBILITY_NONE])
                        ->andWhere(['space.status' => Space::STATUS_ENABLED])
                        ->count();
    }

}

?>
