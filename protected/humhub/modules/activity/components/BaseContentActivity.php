<?php

namespace humhub\modules\activity\components;

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
 * @template T of ContentActiveRecord
 */
abstract class BaseContentActivity extends BaseActivity
{
    protected Content $content;

    /**
     * @var T
     */
    protected ContentActiveRecord $contentActiveRecord;

    /**
     * @var class-string<T>
     */
    protected string $contentActiveRecordClass = ContentActiveRecord::class;

    protected ?ContentProvider $contentAddon = null;

    /**
     * @var int Max length of the activity content in Web view
     */
    public int $webContentLength = 60;

    /**
     * @var int Max length of the activity content in Mail messages
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

    public function getUrl(bool $scheme = false): ?string
    {
        if ($this->contentAddon instanceof ContentAddonActiveRecord) {
            return $this->contentAddon->getUrl($scheme);
        }

        return $this->content->getUrl($scheme);
    }

    protected function getMessageParamsWeb(): array
    {
        return array_merge(parent::getMessageParamsWeb(), [
            'content' => ContentHelper::getContentInfo($this->content, true, $this->webContentLength),
            'contentTitle' => ContentHelper::getContentInfo($this->content, false, $this->webContentLength),
        ]);
    }

    protected function getMessageParamsMailText(): array
    {
        return array_merge(parent::getMessageParamsMailText(), [
            'content' => ContentHelper::getContentInfo($this->content, true, $this->mailContentLength),
            'contentTitle' => ContentHelper::getContentInfo($this->content, false, $this->mailContentLength),
        ]);
    }

    protected function getMessageParamsMailHtml(): array
    {
        return array_merge(parent::getMessageParamsMailHtml(), [
            'content' => Html::strong(ContentHelper::getContentInfo($this->content, true, $this->mailContentLength)),
            'contentTitle' => ContentHelper::getContentInfo($this->content, false, $this->mailContentLength),
        ]);
    }
}
