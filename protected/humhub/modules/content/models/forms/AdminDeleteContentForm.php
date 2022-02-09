<?php

namespace humhub\modules\content\models\forms;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use Yii;
use yii\base\Model;

/**
 * AdminDeleteContentForm is shown when admin deletes someone's content (e.g. post)
 */
class AdminDeleteContentForm extends Model
{
    /**
     * @var string
     */
    public $message;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'message' => Yii::t('ContentModule.base', 'Message')
        ];
    }
}
