<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\MessageParam;
use humhub\modules\notification\components\NotificationCategory;
use Yii;
use yii\helpers\Url;

/**
 * Tells a user that a credential for their account was created: a personal access token or
 * a connected app. Security relevant, so it is a direct notification.
 *
 * The grant is deliberately no source record: revoking it must not delete the notification.
 *
 * @since 1.20
 */
final class GrantCreatedNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public static function category(): NotificationCategory
    {
        return NotificationCategory::direct();
    }

    /**
     * @inheritdoc
     */
    public function getUrl(bool $scheme = false): ?string
    {
        return Url::to(['/user/security/sessions'], $scheme);
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return ($this->payload['type'] ?? null) === 'pat'
            ? Yii::t('OauthModule.base', 'A personal access token {name} was created for your account.', $params)
            : Yii::t('OauthModule.base', '{name} was connected to your account.', $params);
    }

    /**
     * @inheritdoc
     */
    protected function getRedactedMessage(array $params): ?string
    {
        return Yii::t('OauthModule.base', 'A new access to your account was created.');
    }

    /**
     * @inheritdoc
     */
    protected function getMessageParams(): array
    {
        return ['name' => MessageParam::emphasis((string)($this->payload['name'] ?? ''), 100)];
    }
}
