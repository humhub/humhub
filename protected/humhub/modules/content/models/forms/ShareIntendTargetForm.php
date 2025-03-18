<?php

namespace humhub\modules\content\models\forms;

use yii\base\Model;
use yii\helpers\Url;

class ShareIntendTargetForm extends Model
{
    public $targetSpaceGuid;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['targetSpaceGuid'], 'required'],
        ];
    }

    public function getSpaceSearchUrl(): string
    {
        return Url::to(['space-search-json']);
    }
}
