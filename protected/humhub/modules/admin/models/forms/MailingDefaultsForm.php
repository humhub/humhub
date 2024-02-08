<?php

namespace humhub\modules\admin\models\forms;

use Yii;
use yii\base\Model;

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class MailingDefaultsForm extends Model
{
    public $receive_email_activities;
    public $receive_email_notifications;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->receive_email_activities = Yii::$app->getModule('activity')->settings->get('receive_email_activities');
        $this->receive_email_notifications = Yii::$app->getModule('notification')->settings->get('receive_email_notifications');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receive_email_notifications', 'receive_email_activities'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [];
    }

    /**
     * Saves the form
     *
     * @return bool
     */
    public function save()
    {
        Yii::$app->getModule('notification')->settings->set('receive_email_notifications', $this->receive_email_notifications);
        Yii::$app->getModule('activity')->settings->set('receive_email_activities', $this->receive_email_activities);

        return true;
    }

}
