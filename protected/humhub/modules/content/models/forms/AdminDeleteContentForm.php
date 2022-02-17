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
     * @var boolean
     */
    public $notify;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message'], 'required', 'when' => function ($model) {
                return $model->notify;
            }],
            [['message'], 'string'],
            [['notify'], 'boolean']
        ];
    }

    public function attributeLabels()
    {
        return [
            'message' => Yii::t('CommentModule.base', 'Reason'),
            'notify' => Yii::t('CommentModule.base', 'Send a notification to author')
        ];
    }
}
