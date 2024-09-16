<?php

namespace humhub\modules\admin\models\forms;

use humhub\modules\queue\helpers\QueueHelper;
use humhub\modules\topic\jobs\ConvertTopicsToGlobalJob;
use Yii;
use yii\base\Model;

class GlobalTopicSettingForm extends Model
{
    public $restrictAdditionalTopics;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['restrictAdditionalTopics'], 'boolean'],
            [['restrictAdditionalTopics'], 'validateRestrictAdditionalTopics'],
        ];
    }

    public function validateRestrictAdditionalTopics($attribute)
    {
        if ($this->{$attribute} && QueueHelper::isQueued(new ConvertTopicsToGlobalJob)) {
            $this->{$attribute} = false;
            $this->addError($attribute, Yii::t('AdminModule.settings', 'Topics conversion has not been completed yet. Please retry in a few minutes.'));
        }
    }

    public function init()
    {
        parent::init();

        $this->restrictAdditionalTopics = Yii::$app->settings->get('restrictAdditionalTopics', 0);
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'restrictAdditionalTopics' => Yii::t('AdminModule.settings', 'Additional topics cannot be created at space/profile level'),
        ];
    }

    public function attributeHints()
    {
        return [
            'restrictAdditionalTopics' => Yii::t('AdminModule.settings', 'Warning: By checking this, existing topics at the space/profile level will be automatically converted to global. This process cannot be undone.'),
        ];
    }

    public function save()
    {
        Yii::$app->settings->set('restrictAdditionalTopics', $this->restrictAdditionalTopics);

        if ($this->restrictAdditionalTopics) {
            Yii::$app->queue->push(new ConvertTopicsToGlobalJob());
        }
    }
}
