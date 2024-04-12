<?php

namespace humhub\modules\admin\models\forms;

use Yii;
use yii\base\Model;

/**
 * SettingsForm
 *
 * @since 0.5
 */
class StatisticSettingsForm extends Model
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
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'trackingHtmlCode' => Yii::t('AdminModule.settings', 'Inserted script tags must contain a nonce. e.g. {code}', [
                'code' => '<code>&lt;script nonce={{nonce}}&gt;</code>',
            ]),
        ];
    }

    /**
     * Saves the form
     *
     * @return bool
     */
    public function save()
    {
        $settingsManager = Yii::$app->settings;
        $settingsManager->set('trackingHtmlCode', $this->trackingHtmlCode);

        return true;
    }

}
