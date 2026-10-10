<?php

namespace humhub\modules\activity\components;

use humhub\components\message\MessageParam;
use humhub\helpers\Html;
use humhub\models\RecordMap;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\helpers\ContentHelper;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\content\models\Content;
use yii\base\InvalidValueException;

/**
 * Base class of an activity about a content, see {@see BaseActivity}. Adds the built-in message
 * parameters `content` (type and preview of the content: post "Release notes") and `contentTitle`
 * (the preview alone).
 *
 * @template T of ContentActiveRecord
 * @api
 */
abstract class BaseContentActivity extends BaseActivity
{
    /**
     * @api
     */
    protected Content $content;

    /**
     * @var T
     * @api
     */
    protected ContentActiveRecord $contentActiveRecord;

    /**
     * @var class-string<T> the record class the content must be of
     * @api
     */
    protected string $contentActiveRecordClass = ContentActiveRecord::class;

    /**
     * @var ContentProvider|null the content addon of the activity, e.g. a comment
     * @api
     */
    protected ?ContentProvider $contentAddon = null;

    /**
     * @var int Max length of the activity content in Web view
     * @api
     */
    public int $webContentLength = 60;

    /**
     * @var int Max length of the activity content in Mail messages
     * @api
     */
    public int $mailContentLength = 300;

    public function __construct(Activity $record, $config = [])
    {
        parent::__construct($record, $config);

        if ($record->content === null) {
            throw new InvalidValueException('Content is null.');
        }
        $this->content = $record->content;

        if (!$this->content->polymorphicRelation instanceof $this->contentActiveRecordClass) {
            throw new InvalidValueException(
                'Content must be type of ' . $this->contentActiveRecordClass . ', ' . ($this->content->polymorphicRelation !== null ? $this->content->polymorphicRelation::class : self::class) . ' given.',
            );
        }
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->contentActiveRecord = $this->content->polymorphicRelation;

        if ($record->content_addon_record_id !== null) {
            $this->contentAddon = RecordMap::getById($record->content_addon_record_id, ContentProvider::class);
        }
    }

    /**
     * The content addon, else the content.
     *
     * @inheritdoc
     */
    public function getUrl(bool $scheme = false): ?string
    {
        if ($this->contentAddon instanceof ContentAddonActiveRecord) {
            return $this->contentAddon->getUrl($scheme);
        }

        return $this->content->getUrl($scheme);
    }

    /**
     * The max length of a content preview in the output being rendered: {@see $webContentLength} for
     * the activity box, {@see $mailContentLength} for mails. For a preview of your own in
     * {@see getMessageParams()}.
     *
     * @api
     * @since 1.20
     */
    protected function getPreviewLength(): int
    {
        return $this->isRenderingMail() ? $this->mailContentLength : $this->webContentLength;
    }

    /**
     * Adds `content` (type and preview of the content, in `<strong>` in an HTML mail) and
     * `contentTitle` (the preview alone).
     *
     * @inheritdoc
     * @internal
     */
    protected function getBuiltInMessageParams(): array
    {
        $length = $this->getPreviewLength();
        // getContentInfo() returns HTML; plain text mails get it as it is, as up to 1.19
        $content = ContentHelper::getContentInfo($this->content, true, $length);
        $contentTitle = ContentHelper::getContentInfo($this->content, false, $length);

        return array_merge(parent::getBuiltInMessageParams(), [
            'content' => MessageParam::html($this->isRenderingMail() ? Html::strong($content) : $content, $content),
            'contentTitle' => MessageParam::html($contentTitle, $contentTitle),
        ]);
    }
}
