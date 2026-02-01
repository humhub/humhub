<?php

namespace humhub\modules\activity\services;

use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;
use Yii;

class RenderService
{
    private BaseActivity $activity;

    public function __construct(Activity $record)
    {
        $this->activity = ActivityManager::load($record);
    }

    public function getWeb(): ?string
    {
        return Yii::$app->getView()->renderFile(
            '@activity/views/layouts/web.php',
            array_merge(
                $this->getViewParams(),
                ['message' => $this->activity->asHtml()],
            ),
        );
    }

    public function getPlaintext()
    {
        return Yii::$app->getView()->renderFile(
            '@activity/views/layouts/mail_plaintext.php',
            array_merge(
                $this->getViewParams(),
                ['message' => $this->activity->asText(), 'url' => $this->activity->getUrl(true)],
            ),
        );
    }

    public function getMail()
    {
        return Yii::$app->getView()->renderFile(
            '@activity/views/layouts/mail.php',
            array_merge(
                $this->getViewParams(),
                ['message' => $this->activity->asHtmlMail(), 'url' => $this->activity->getUrl(true)],
            ),
        );
    }

    private function getViewParams(): array
    {
        return [
            'url' => $this->activity->getUrl(),
            'contentContainer' => $this->activity->contentContainer,
            'createdAt' => $this->activity->createdAt,
            'user' => $this->activity->user,
        ];
    }
}
