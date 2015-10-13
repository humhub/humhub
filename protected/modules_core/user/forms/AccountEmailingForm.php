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
 * Form Model for changing e-mail notification settings
 * 
 * @package humhub.modules_core.user.forms
 * @since 0.6
 */
class AccountEmailingForm extends CFormModel
{

    public $receive_email_activities;
    public $receive_email_notifications;
    public $enable_html5_desktop_notifications;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('receive_email_activities, receive_email_notifications', 'in',
                'range' => array(
                    User::RECEIVE_EMAIL_NEVER,
                    User::RECEIVE_EMAIL_DAILY_SUMMARY,
                    User::RECEIVE_EMAIL_WHEN_OFFLINE,
                    User::RECEIVE_EMAIL_ALWAYS),
                'allowEmpty' => true
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
        return array(
            'receive_email_notifications' => Yii::t('UserModule.forms_AccountEmailingForm', 'Send notifications?'),
            'receive_email_activities' => Yii::t('UserModule.forms_AccountEmailingForm', 'Send activities?'),
            'enable_html5_desktop_notifications' => Yii::t('UserModule.views_account_emailing', 'Get a desktop notification when you are online.')
        );
    }

}
