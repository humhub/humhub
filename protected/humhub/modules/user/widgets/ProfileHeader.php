<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\controllers\ImageController;
use humhub\modules\user\models\User;
use Yii;

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
        /* @var $imageController ImageController */
        $imageController = new ImageController('image-controller', null, ['user' => $this->user]);

        return $this->render('profileHeader', array(
            'user' => $this->user,
            'isProfileOwner' => $this->isProfileOwner,
            'allowModifyProfileImage' => $imageController->allowModifyProfileImage,
            'allowModifyProfileBanner' => $imageController->allowModifyProfileBanner,
            // Deprecated variables below (will removed in future versions)
            'friendshipsEnabled' => Yii::$app->getModule('friendship')->getIsEnabled(),
            'followingEnabled' => !Yii::$app->getModule('user')->disableFollow,
            'countFriends' => -1,
            'countFollowers' => -1,
            'countFollowing' => -1,
            'countSpaces' => -1,
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
