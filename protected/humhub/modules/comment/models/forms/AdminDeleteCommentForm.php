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
     * @var Comment
     */
    public $comment;

    /**
     * @var integer
     */
    public $comment_id;

    /**
     * @var string
     */
    public $message;

    public function init()
    {
        if (!empty($this->comment)) {
            $this->comment_id = $this->comment->id;
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['comment_id'], 'required'],
            [['comment_id'], 'integer'],
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
