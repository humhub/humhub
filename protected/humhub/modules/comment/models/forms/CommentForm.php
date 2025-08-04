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
     * The list of files attached to a comment
     * @var array
     */
    public $fileList;

    /**
     * @var Comment The model to comment
     */
    public $comment;

    /**
     * @param Comment|ContentActiveRecord $target
     */
    public function __construct(/**
     * @var Comment|ContentActiveRecord The model to comment or other active content
     */
        public $target,
        $comment = null,
    ) {
        $this->comment = $comment ?? new Comment();
        parent::__construct();
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['fileList'], 'safe'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function load($data, $formName = null)
    {
        return parent::load($data, $formName) | $this->comment->load($data);
    }

    /**
     * @inheritdoc
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        if (!empty($attributeNames)) {
            return parent::validate($attributeNames, $clearErrors);
        }

        if (!$this->comment->validate() || !parent::validate($attributeNames, $clearErrors)) {
            $this->comment->addError('message', Yii::t('CommentModule.base', 'Comment could not be saved!'));
            return false;
        }

        if (!empty($this->comment->message)) {
            return true;
        }

        // Allow empty message only With attachments
        if (!empty($this->fileList) || (!$this->comment->isNewRecord && $this->comment->fileManager->find()->count())) {
            return true;
        }

        $this->comment->addError('message', Yii::t('CommentModule.base', 'The comment must not be empty!'));
        return false;
    }

    /**
     * Saves the form
     *
     * @return bool
     * @throws ServerErrorHttpException
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $this->comment->setPolyMorphicRelation($this->target);

        //check if model saved
        if ($this->comment->save()) {
            $this->comment->fileManager->attach($this->fileList);
            return true;
        }

        $this->comment->addError('message', Yii::t('CommentModule.base', 'Comment could not be saved!'));

        return false;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}
