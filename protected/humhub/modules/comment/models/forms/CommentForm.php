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
    /**
     * Comment message
     * @var string
     */
    public $message;

    /**
     * The list of files attached to a comment
     * @var array
     */
    public $fileList;

    /**
     * @var Comment The model to comment
     */
    public $comment;

    /**
     * @var Comment|ContentActiveRecord The model to comment or other active content
     */
    public $target;

    public function __construct($target, $comment)
    {
        $this->target = $target;
        $this->comment = $comment;
        parent::__construct();
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['message'], 'required', 'isEmpty' => function ($message) {
                $hasFile = !empty($this->fileList) || (!$this->comment->isNewRecord && $this->comment->fileManager->find()->count());

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
     * Updates the form
     *
     * @return boolean
     */
    public function update()
    {
        $this->comment->message = $this->message;
        return $this->save();
    }


    /**
     * Saves the form
     *
     * @return boolean
     */
    public function save()
    {
        /**@var Comment $this->comment*/
        $this->comment->setPolyMorphicRelation($this->target);

        //check if model saved
        if ($this->comment->save()) {
            $this->comment->fileManager->attach($this->fileList);

            // Reload comment to get populated created_at field
            $this->comment->refresh();
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
