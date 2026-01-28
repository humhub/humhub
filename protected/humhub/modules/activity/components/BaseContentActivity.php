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

    public function __construct(Activity $record, $config = [])
    {
        parent::__construct($record, $config);

        if ($record->content === null) {
            throw new InvalidValueException('Content is null.');
        }
        $this->content = $record->content;

        if (!$this->content->polymorphicRelation instanceof $this->contentActiveRecordClass) {
            throw new InvalidValueException('Content must be type of ' . $this->contentActiveRecordClass);
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

    protected function getMessageParamsText(): array
    {
        return array_merge(parent::getMessageParamsText(), [
            'contentTitle' => ContentHelper::getContentInfo($this->content),
        ]);
    }

    protected function getMessageParamsHtmlMail(): array
    {
        return array_merge(parent::getMessageParamsHtmlMail(), [
            'contentTitle' => Html::strong(ContentHelper::getContentInfo($this->content)),
        ]);
    }
}
