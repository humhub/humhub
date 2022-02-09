<?php

namespace humhub\modules\comment\models\forms;

use humhub\modules\comment\models\Comment;
use Yii;

/**
 * AdminCommentDeleteForm is shown when admin deletes someone's comment
 */
class AdminDeleteCommentForm extends yii\base\Model
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
            'message' => Yii::t('CommentModule.base', 'Message')
        ];
    }
}
