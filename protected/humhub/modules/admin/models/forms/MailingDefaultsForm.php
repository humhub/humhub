<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class MailingDefaultsForm extends \yii\base\Model
{

    public $receive_email_activities;
    public $receive_email_notifications;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array(['receive_email_notifications', 'receive_email_activities'], 'integer'),
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
        );
    }

}
