<?php

namespace humhub\modules\activity\components;

use humhub\models\RecordMap;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\content\models\Content;
use yii\base\InvalidValueException;

abstract class BaseContentActivity extends BaseActivity
{
    protected Content $content;
    protected ?ContentProvider $contentAddon = null;

    public function __construct(Activity $record, $config = [])
    {
        parent::__construct($record, $config);

        if ($record->content === null) {
            throw new InvalidValueException('Content is null.');
        }

        $this->content = $record->content;
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
}
