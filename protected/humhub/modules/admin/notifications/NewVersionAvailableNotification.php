<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\notifications;

use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;
use Yii;
use yii\helpers\Url;

/**
 * Notifies the administrators that a new HumHub version is available. Without originator and
 * source; the version is passed as the `version` payload, notifications without one (e.g. from
 * before 1.20) show the latest version known to the {@see HumHubAPI}.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `NewVersionAvailable` (since 0.11).
 *
 * @since 1.20
 */
final class NewVersionAvailableNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public static function category(): NotificationCategory
    {
        return NotificationCategory::admin();
    }

    /**
     * The about page with the version information.
     *
     * @inheritdoc
     */
    public function getUrl(bool $scheme = false): ?string
    {
        return Url::to(['/admin/information/about'], $scheme);
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return Yii::t('AdminModule.notification', 'There is a new HumHub Version ({version}) available.', $params);
    }

    /**
     * @inheritdoc
     */
    protected function getMessageParams(): array
    {
        return ['version' => (string)($this->payload['version'] ?? HumHubAPI::getLatestHumHubVersion())];
    }
}
