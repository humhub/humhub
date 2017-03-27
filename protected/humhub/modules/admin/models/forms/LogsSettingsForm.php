<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * LogsSettingsForm
 *
 * @since 1.2
 */
class LogsSettingsForm extends \yii\base\Model
{

    public $logsDateLimit;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->settings;
        $this->logsDateLimit = $settingsManager->get('logsDateLimit');
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return [
            '-1 week' => Yii::t('AdminModule.forms_LogsSettingsForm', '1 week'),
            '-2 weeks' => Yii::t('AdminModule.forms_LogsSettingsForm', '2 weeks'),
            '-1 month' => Yii::t('AdminModule.forms_LogsSettingsForm', '1 month'),
            '-3 months' => Yii::t('AdminModule.forms_LogsSettingsForm', '3 months'),
            '-6 months' => Yii::t('AdminModule.forms_LogsSettingsForm', '6 months'),
            '-1 year' => Yii::t('AdminModule.forms_LogsSettingsForm', '1 year'),
            '' => Yii::t('AdminModule.forms_LogsSettingsForm', 'never'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['logsDateLimit', 'in', 'range' => array_keys($this->options)],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'logsDateLimit' => Yii::t('AdminModule.forms_StatisticSettingsForm', 'Maximum allowed age for logs.'),
        ];
    }

    /**
     * Saves the form
     *
     * @return boolean
     */
    public function save()
    {
        $settingsManager = Yii::$app->settings;
        $settingsManager->set('logsDateLimit', $this->logsDateLimit);

        return true;
    }

}
