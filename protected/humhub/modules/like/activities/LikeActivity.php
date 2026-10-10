<?php

namespace humhub\modules\like\activities;

use humhub\components\message\MessageParam;
use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\components\BaseContentActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\helpers\ContentHelper;
use humhub\modules\like\models\Like;
use Yii;
use yii\base\InvalidValueException;

class LikeActivity extends BaseContentActivity implements ConfigurableActivityInterface
{
    private readonly Like $like;

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
        if ($this->groupCount > 1) {
            return Yii::t('LikeModule.base', '{displayNames} like {content}.', $params);
        } else {
            return Yii::t('LikeModule.base', '{displayName} likes {content}.', $params);
        }
    }

    /**
     * `content` is the liked content or comment.
     *
     * @inheritdoc
     */
    protected function getMessageParams(): array
    {
        $content = ContentHelper::getContentInfo($this->like->getContentOwnerObject(), true, $this->getPreviewLength());

        return [
            // HTML, also in plain text mails, as up to 1.19
            'content' => MessageParam::html($content, $content),
        ];
    }

    public function getGroupingQuery(): ?ActiveQueryActivity
    {
        return Activity::find()
            ->andWhere(['activity.class' => static::class])
            ->leftJoin('record_map', 'activity.content_addon_record_id=record_map.id')
            ->leftJoin('like', 'record_map.pk=like.id')
            ->andWhere(['activity.content_id' => $this->record->content->id])
            ->andWhere([
                'like.content_addon_record_id' => $this->contentAddon->content_addon_record_id,
                'like.content_id' => $this->contentAddon->content_id,
            ]);
    }

}
