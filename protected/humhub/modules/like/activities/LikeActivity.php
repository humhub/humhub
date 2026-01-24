<?php

namespace humhub\modules\like\activities;

use humhub\helpers\Html;
use humhub\modules\activity\components\BaseContentActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\helpers\ContentHelper;
use humhub\modules\like\models\Like;
use Yii;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use yii\base\InvalidValueException;

class LikeActivity extends BaseContentActivity implements ConfigurableActivityInterface
{
    private Like $like;

    public function __construct(Activity $record, $config = [])
    {
        parent::__construct($record, $config);

        if ($this->contentAddon === null) {
            throw new InvalidValueException('No content addon has been set.');
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

    public function asText(array $params = []): string
    {
        $defaultParams = [
            'displayName' => $this->user->displayName,
            'contentTitle' => ContentHelper::getContentInfo($this->like->getContentOwnerObject())
        ];

        return Yii::t(
            'CommentModule.base',
            '{userDisplayName} likes {contentTitle}.',
            array_merge($defaultParams, $params)
        );
    }

    public function asHtml(): string
    {
        return $this->asText([
            'displayName' => Html::strong(Html::encode($this->user->displayName)),
        ]);
    }

    public function asHtmlMail(): string
    {
        return $this->asHtml();
    }
}
