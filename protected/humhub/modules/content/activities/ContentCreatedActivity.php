<?php

namespace humhub\modules\content\activities;

use humhub\helpers\Html;
use humhub\modules\content\helpers\ContentHelper;
use Yii;
use humhub\modules\activity\components\BaseContentActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;

final class ContentCreatedActivity extends BaseContentActivity implements ConfigurableActivityInterface
{
    public static function getTitle(): string
    {
        return Yii::t('ContentModule.activities', 'Contents');
    }

    public static function getDescription(): string
    {
        return Yii::t('ContentModule.activities', 'Whenever a new content (e.g. post) has been created.');
    }

    public function asText(array $params = []): string
    {
        $defaultParams = [
            'displayName' => $this->user->displayName,
            'contentTitle' => ContentHelper::getContentInfo($this->content),
        ];

        return Yii::t(
            'ContentModule.activities',
            '{displayName} created a new {contentTitle}.',
            array_merge($defaultParams, $params),
        );
    }

    public function asHtml(): string
    {
        return $this->asText([
            'displayName' => Html::strong(Html::encode($this->user->displayName)),
            'contentTitle' => ContentHelper::getContentInfo($this->content),
        ]);
    }

    public function asHtmlMail(): string
    {
        return $this->asText([
            'displayName' => Html::strong(Html::encode($this->user->displayName)),
            'contentTitle' => Html::strong(ContentHelper::getContentInfo($this->content)),
        ]);
    }
}
