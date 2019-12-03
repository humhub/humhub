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
        return [
            ['trackingHtmlCode', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'trackingHtmlCode' => Yii::t('AdminModule.settings', 'HTML tracking code'),
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
        $settingsManager->set('trackingHtmlCode', $this->trackingHtmlCode);

        return true;
    }

}
