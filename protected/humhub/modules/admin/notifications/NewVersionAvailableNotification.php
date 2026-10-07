<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\notifications;

use humhub\helpers\Html;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
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
    public static function group(): NotificationGroup
    {
        return NotificationGroup::admin();
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
        return Yii::t('AdminModule.notification', 'There is a new HumHub Version ({version}) available.', [
            'version' => $params['version'],
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getMessageParamsPlain(int $maxLength): array
    {
        return array_merge(parent::getMessageParamsPlain($maxLength), [
            'version' => $this->getVersion(),
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getMessageParamsWeb(): array
    {
        return array_merge(parent::getMessageParamsWeb(), [
            'version' => Html::strong(Html::encode($this->getVersion())),
        ]);
    }

    private function getVersion(): string
    {
        return (string)($this->payload['version'] ?? HumHubAPI::getLatestHumHubVersion());
    }
}
