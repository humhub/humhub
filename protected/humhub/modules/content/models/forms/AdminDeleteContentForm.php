<?php

namespace humhub\modules\content\models\forms;

use humhub\modules\content\models\Content;
use humhub\modules\content\notifications\ContentDeleted;
use Yii;
use yii\base\Model;

/**
 * AdminDeleteContentForm is shown when admin deletes someone's content (e.g. post)
 */
class AdminDeleteContentForm extends Model
{
    /**
     * @var Content
     */
    public $content;

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

    public function delete(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        if (!$this->notify()) {
            return false;
        }

        return $this->content->delete();
    }

    public function notify(): bool
    {
        if (!$this->notify) {
            return true;
        }

        $contentDeleted = ContentDeleted::instance()
            ->from(Yii::$app->user->getIdentity())
            ->payload([
                'contentTitle' => (new ContentDeleted)->getContentPlainTextInfo($this->content),
                'reason' => $this->message
            ]);
        if (!$contentDeleted->saveRecord($this->content->createdBy)) {
            $this->addError('message', Yii::t('ContentModule.base', 'Cannot notify the author.'));
            return false;
        }

        $contentDeleted->record->updateAttributes([
            'send_web_notifications' => 1
        ]);

        return true;
    }

    public function getErrorsAsString(): string
    {
        return implode(' ', $this->getErrorSummary(true));
    }
}
