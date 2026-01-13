<?php

namespace humhub\modules\comment\activities;

use humhub\helpers\Html;
use humhub\modules\activity\components\BaseContentActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;
use humhub\modules\content\widgets\richtext\RichText;
use Yii;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use yii\base\InvalidValueException;

final class NewCommentActivity extends BaseContentActivity implements ConfigurableActivityInterface
{
    private Comment $comment;

    public function __construct(Activity $record, $config = [])
    {
        parent::__construct($record, $config);

        if ($this->contentAddon === null) {
            throw new InvalidValueException('No content addon has been set. Activity: ' . $record->id);
        }

        if (!$this->contentAddon instanceof Comment) {
            throw new InvalidValueException('Content addon is not a valid comment object.');
        }

        $this->comment = $this->contentAddon;
    }

    public static function getTitle(): string
    {
        return Yii::t('CommentModule.base', 'Comments');
    }

    public static function getDescription(): string
    {
        return Yii::t('CommentModule.base', 'Whenever a new comment was written.');
    }

    public function getAsText(array $params = []): string
    {
        $defaultParams = [
            'displayName' => $this->user->displayName,
            'comment' => "\n" . '"' . RichTextToPlainTextConverter::process($this->comment->message) . '"'
        ];

        return Yii::t(
            'CommentModule.base',
            '{displayName} wrote a new comment {comment}.',
            array_merge($defaultParams, $params)
        );
    }

    public function getAsHtml(): string
    {
        return $this->getAsText([
            'displayName' => Html::strong(Html::encode($this->user->displayName)),
            'comment' => '"' . RichText::preview($this->comment->message, 100) . '"'
        ]);
    }

    public function getAsMailHtml(): string
    {
        return $this->getAsText([
            'displayName' => Html::strong(Html::encode($this->user->displayName)),
            'comment' => '<br>' . '"' . RichText::preview($this->comment->message, 0) . '"'
        ]);
    }
}
