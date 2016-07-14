<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;

/**
 * ProfileHeader
 * 
 * @since 0.5
 * @author Luke
 */
class ProfileHeader extends \yii\base\Widget
{

    /**
     * @var \humhub\modules\user\models\User the user of this header
     */
    public $user;

    /**
     * @var boolean can this header edited by current user
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
            if (Yii::$app->user->getIdentity()->super_admin === 1 && Yii::$app->params['user']['adminCanChangeProfileImages']) {
                $this->isProfileOwner = true;
            } elseif (Yii::$app->user->id == $this->user->id) {
                $this->isProfileOwner = true;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('profileHeader', array(
                    'user' => $this->user,
                    'isProfileOwner' => $this->isProfileOwner
        ));
    }

}

?>
