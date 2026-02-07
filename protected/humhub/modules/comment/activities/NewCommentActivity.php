<?php

namespace humhub\modules\comment\activities;

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

    protected function getMessage(array $params): string
    {
        return Yii::t('CommentModule.base', '{displayName} wrote a new comment {comment}.', $params);
    }

    protected function getMessageParamsText(): array
    {
        return array_merge(
            parent::getMessageParamsText(),
            [
                'comment' => "\n" . '"' . RichTextToPlainTextConverter::process($this->comment->message) . '"',
            ],
        );
    }

    protected function getMessageParamsHtml(): array
    {
        return array_merge(
            parent::getMessageParamsHtml(),
            [
                'comment' => '"' . RichText::preview($this->comment->message, 100) . '"',
            ],
        );
    }
}
