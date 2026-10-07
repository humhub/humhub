<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\notifications;

use humhub\helpers\Html;
use humhub\modules\notification\components\BaseNotification;

/**
 * Adds the `spaceName` message parameter - the name of {@see BaseNotification::getSpace()} - to
 * the space notifications: plain in the plain-text channels, HTML-encoded in `<strong>` on the
 * web and in HTML mails.
 *
 * @mixin BaseNotification
 * @internal
 * @since 1.20
 */
trait SpaceNotificationTrait
{
    /**
     * @inheritdoc
     */
    protected function getMessageParamsPlain(int $maxLength): array
    {
        return array_merge(parent::getMessageParamsPlain($maxLength), [
            'spaceName' => $this->getSpace()?->displayName ?? '',
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getMessageParamsWeb(): array
    {
        return array_merge(parent::getMessageParamsWeb(), [
            'spaceName' => Html::strong(Html::encode($this->getSpace()?->displayName ?? '')),
        ]);
    }
}
