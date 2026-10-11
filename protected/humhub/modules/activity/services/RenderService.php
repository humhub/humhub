<?php

namespace humhub\modules\activity\services;

use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\notification\components\NotificationPrivacy;
use Yii;

/**
 * Renders an activity record for the summary mail - with the full sentence, or under
 * {@see NotificationPrivacy::Redacted} the sentence without content
 * ({@see BaseActivity::asRedactedHtml()}) and without the Space.
 *
 * @internal
 */
class RenderService
{
    private readonly BaseActivity $activity;

    public function __construct(Activity $record, private readonly NotificationPrivacy $privacy = NotificationPrivacy::Full)
    {
        $this->activity = ActivityManager::load($record);
    }

    public function getMailText(): string
    {
        return Yii::$app->getView()->renderFile(
            '@activity/views/layouts/mail_plaintext.php',
            array_merge(
                $this->getViewParams(),
                [
                    'message' => $this->privacy === NotificationPrivacy::Full ? $this->activity->asMailText() : $this->activity->asRedactedText(),
                    'url' => $this->activity->getUrl(true),
                ],
            ),
        );
    }

    public function getMailHtml(): string
    {
        return Yii::$app->getView()->renderFile(
            '@activity/views/layouts/mail.php',
            array_merge(
                $this->getViewParams(),
                [
                    'message' => $this->privacy === NotificationPrivacy::Full ? $this->activity->asMailHtml() : $this->activity->asRedactedHtml(),
                    'url' => $this->activity->getUrl(true),
                ],
            ),
        );
    }

    private function getViewParams(): array
    {
        return [
            'url' => $this->activity->getUrl(),
            // the layouts name the Space; a Space name may tell too much when content is hidden
            'contentContainer' => $this->privacy === NotificationPrivacy::Full ? $this->activity->contentContainer : null,
            'createdAt' => $this->activity->createdAt,
            'user' => $this->activity->user,
        ];
    }
}
