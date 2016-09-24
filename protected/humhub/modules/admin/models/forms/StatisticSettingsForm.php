<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * SettingsForm
 * 
 * @since 0.5
 */
class StatisticSettingsForm extends \yii\base\Model
{

    public $trackingHtmlCode;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->settings;
        $this->trackingHtmlCode = $settingsManager->get('trackingHtmlCode');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            array('trackingHtmlCode', 'safe'),
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'trackingHtmlCode' => Yii::t('AdminModule.forms_StatisticSettingsForm', 'HTML tracking code'),
        );
    }

    /**
     * Saves the form
     * 
     * @return boolean
     */
    public function save()
    {
        $settingsManager = Yii::$app->settings;
        $settingsManager->set('trackingHtmlCode', $this->trackingHtmlCode);

        return true;
    }

}
