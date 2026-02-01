<?php

namespace humhub\modules\like\activities;

use humhub\modules\activity\components\BaseContentActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\helpers\ContentHelper;
use humhub\modules\like\models\Like;
use Yii;
use yii\base\InvalidValueException;

class LikeActivity extends BaseContentActivity implements ConfigurableActivityInterface
{
    private Like $like;

    public function __construct(Activity $record, $config = [])
    {
        parent::__construct($record, $config);

        if ($this->contentAddon === null) {
            throw new InvalidValueException('No content addon has been set.' . $this->record->id);
        }

        if (!$this->contentAddon instanceof Like) {
            throw new InvalidValueException('Content addon is not a valid like object.');
        }

        $this->like = $this->contentAddon;
    }

    public static function getTitle(): string
    {
        return Yii::t('LikeModule.activities', 'Likes');
    }

    public static function getDescription(): string
    {
        return Yii::t('LikeModule.activities', 'Whenever someone likes something (e.g. a post or comment).');
    }

    protected function getMessage(array $params): string
    {
        return Yii::t('LikeModule.base', '{displayName} likes {content}.', $params);
    }

    protected function getMessageParamsText(): array
    {
        return array_merge(
            parent::getMessageParamsText(),
            [
                'contentTitle' => ContentHelper::getContentInfo($this->like->getContentOwnerObject()),
            ],
        );
    }
}
