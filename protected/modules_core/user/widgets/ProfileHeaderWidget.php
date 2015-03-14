<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * @package humhub.modules_core.user.widgets
 * @since 0.5
 * @author Luke
 */
class ProfileHeaderWidget extends HWidget
{

    public $user;
    protected $isProfileOwner = false;


    public function init()
    {
    
        /**
         * Try to autodetect current user by controller
         */
        if ($this->user === null) {
            $this->user = $this->getController()->getUser();
        }
        
        
        $this->isProfileOwner = (Yii::app()->user->id == $this->user->id);

        // Only include uploading javascripts on own user profiles
        if ($this->isProfileOwner) {
            $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
            Yii::app()->clientScript->registerScriptFile($assetPrefix . '/profileHeaderImageUpload.js');

            Yii::app()->clientScript->setJavascriptVariable('userGuid', $this->user->guid);
            Yii::app()->clientScript->setJavascriptVariable('profileImageUploaderUrl', Yii::app()->createUrl('//user/account/profileImageUpload'));
            Yii::app()->clientScript->setJavascriptVariable('profileHeaderUploaderUrl', Yii::app()->createUrl('//user/account/bannerImageUpload'));

        }
    }

    public function run()
    {
        $this->render('profileHeader', array(
            'user' => $this->user,
            'isProfileOwner' => $this->isProfileOwner
        ));
    }

}

?>
