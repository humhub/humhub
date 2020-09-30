<?php

namespace humhub\modules\comment\models\forms;

use humhub\modules\comment\models\Comment;
use humhub\modules\content\components\ContentActiveRecord;
use Yii;
use yii\web\ServerErrorHttpException;

/**
 * CommentForm
 * @package humhub\modules\comment\models\forms
 *
 * @since 0.5
 */
class CommentForm extends yii\base\Model
{
    public $message;
    public $fileList;
    public $comment;

    /**
     * @var Comment|ContentActiveRecord The model to comment
     */
    public $target;

    public function __construct($target, $fileList = null)
    {
        $this->target = $target;
        $this->fileList = $fileList;
        parent::__construct();
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['message'], 'required', 'isEmpty' => function ($message) {
                $hasFile = !is_null($this->fileList) && !empty($this->fileList) ? true : false;

                //check if message empty and attached file exists
                if (empty($message) && !$hasFile) {
                    throw new ServerErrorHttpException(Yii::t('CommentModule.base', 'The comment must not be empty!'));
                }
            }],
            [['fileList'], 'safe'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function load($data, $formName = null)
    {
        // When user updates comment $data contain 'Comment', otherwise not
        if (isset($data['Comment'])) {
            $data['message'] = $data['Comment']['message'];
            unset($data['Comment']);
        }

        if (!parent::load($data, $formName)) {
            return false;
        }

        if (!$this->validate()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @inheritDoc
     */
    public function formName()
    {
        return '';
    }

    /**
     * Creates the form
     *
     * @return Comment|boolean
     */
    public function create()
    {
        $comment = new Comment(['message' => $this->message]);
        return $this->save($comment);
    }

    /**
     * Updates the form
     *
     * @param Comment $comment
     * @return Comment|boolean
     */
    public function update($comment)
    {
        $comment->message = $this->message;
        return $this->save($comment);
    }


    /**
     * Saves the form
     *
     * @param Comment $comment
     * @return Comment|boolean
     */
    public function save($comment)
    {
        $comment->setPolyMorphicRelation($this->target);

        //check if model saved
        if ($comment->save()) {
            $comment->fileManager->attach($this->fileList);

            // Reload comment to get populated created_at field
            $comment->refresh();
            $this->comment = $comment;
            return true;
        } else {
            return false;
        }
    }

    public function getComment()
    {
        return $this->comment;
    }

}
