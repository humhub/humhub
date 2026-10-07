<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\modules\notification\components\ActiveQueryNotification;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
use humhub\modules\notification\models\Notification;

final class TestGroupedNotification extends BaseNotification
{
    public static function group(): NotificationGroup
    {
        return NotificationGroup::social();
    }

    public function getGroupingQuery(): ?ActiveQueryNotification
    {
        return Notification::find()
            ->andWhere(['notification.class' => self::class])
            ->andWhere(['notification.content_id' => $this->content?->id]);
    }

    protected function getMessage(array $params): string
    {
        return $this->groupCount > 1
            ? "{$params['displayNames']} did {$params['groupCount']} things"
            : "{$params['displayName']} did a thing";
    }
}
