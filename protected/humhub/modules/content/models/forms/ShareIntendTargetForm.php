<?php

namespace humhub\modules\content\models\forms;

use Yii;
use yii\base\Model;
use yii\helpers\Url;

class ShareIntendTargetForm extends Model
{
    public $targetContainerGuid;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['targetContainerGuid'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'targetContainerGuid' => Yii::t('ContentModule.base', 'Add to'),
        ];
    }

    public function getContainerSearchUrl(): string
    {
        return Url::to(['container-search-json']);
    }
}
