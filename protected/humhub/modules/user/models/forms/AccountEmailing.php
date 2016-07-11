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

namespace humhub\modules\user\models\forms;

use Yii;
use humhub\modules\user\models\User;

/**
 * Form Model for changing e-mail notification settings
 *
 * @package humhub.modules_core.user.forms
 * @since 0.6
 */
class AccountEmailing extends \yii\base\Model
{

    public $user;
    public $receive_email_activities;
    public $receive_email_notifications;
    public $enable_html5_desktop_notifications;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array(['receive_email_activities', 'receive_email_notifications'], 'in',
                'range' => array(
                    User::RECEIVE_EMAIL_NEVER,
                    User::RECEIVE_EMAIL_DAILY_SUMMARY,
                    User::RECEIVE_EMAIL_WHEN_OFFLINE,
                    User::RECEIVE_EMAIL_ALWAYS)
            ),
            array('enable_html5_desktop_notifications', 'in', 'range' => array('0', '1')),
        );
    }
    
    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return [
            'receive_email_notifications' => Yii::t('UserModule.forms_AccountEmailingForm', 'Send notifications?'),
            'receive_email_activities' => Yii::t('UserModule.forms_AccountEmailingForm', 'Send activities?'),
            'enable_html5_desktop_notifications' => Yii::t('UserModule.views_account_emailing', 'Receive desktop notifications when you are online.')
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->user = Yii::$app->user->getIdentity();
        
        $activityModule = Yii::$app->getModule('activity');
        $notificationModule = Yii::$app->getModule('notification');
        
        // Initialize form values
        $this->receive_email_activities = $this->getSettingValue($activityModule, 'receive_email_activities');
        $this->receive_email_notifications = $this->getSettingValue($notificationModule, 'receive_email_notifications');
        $this->enable_html5_desktop_notifications = $this->getSettingValue($notificationModule, 'enable_html5_desktop_notifications');
    }
    
    /**
     * Retrieves the setting of the given $module for the given $settingKey.
     * If existing, this function will return the user specific setting else
     * the default site setting.
     * 
     * @param Module $module Module object
     * @param string $settingKey Setting key value
     * @return boolean
     */
    private function getSettingValue($module, $settingKey)
    {
        
        $result = $module->settings->contentContainer($this->user)->get($settingKey);
        if ($result === null) {
            // Use site default value
            $result = $module->settings->get($settingKey);
        }
        
        return $result;
    }
    
    /**
     * Saves the given email settings
     */
    public function save()
    {
        $activityModule = Yii::$app->getModule('activity');
        $notificationModule = Yii::$app->getModule('notification');
        
        $activityModule->settings->contentContainer($this->user)->set('receive_email_activities', $this->receive_email_activities);
        $notificationModule->settings->contentContainer($this->user)->set('receive_email_notifications', $this->receive_email_notifications);
        $notificationModule->settings->contentContainer($this->user)->set('enable_html5_desktop_notifications', $this->enable_html5_desktop_notifications);
        return true;
    }

}
