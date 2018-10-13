<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\activity\actions;

use humhub\modules\stream\actions\ContentContainerStream;
use humhub\modules\user\models\User;

class ActivityStreamAction extends ContentContainerStream
{
    const CHANNEL_ACTIVITY = 'activity';

    public $activity = true;

    protected function setActionSettings()
    {
        parent::setActionSettings();
        if ($this->activity) {
            $this->streamQuery->channel(static::CHANNEL_ACTIVITY);
            if ($this->streamQuery->user) {
                $this->streamQuery->query()->andWhere(['!=', 'user.status', User::STATUS_NEED_APPROVAL]);
                $this->streamQuery->query()->andWhere('content.created_by != :userId', [':userId' => $this->streamQuery->user->id]);
            }
        }
    }
}
