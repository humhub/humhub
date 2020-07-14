<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\activity\actions;

use humhub\modules\stream\actions\ContentContainerStream;
use humhub\modules\stream\models\ContentContainerStreamQuery;
use humhub\modules\user\models\User;

/**
 * This action can be used as container related wall- and activity stream. This stream action can be used as wall stream
 * action by setting the $activity flag to true. By default this stream action will serve activity stream channel content
 * only until the $activity flag is set to false.
 *
 * This action type is useful for streams types supporting both, a wall as well as activity stream.
 *
 * @package humhub\modules\activity\actions
 */
class ActivityStreamAction extends ContentContainerStream
{
    const CHANNEL_ACTIVITY = 'activity';

    /**
     * @var bool if true the stream will search for activity content
     */
    public $activity = true;

    /**
     * @inheritDoc
     */
    protected function setActionSettings()
    {
        parent::setActionSettings();

        if(!$this->activity) {
            return;
        }

        if($this->streamQuery instanceof ContentContainerStreamQuery) {
            $this->streamQuery->pinnedContentSupport = false;
        }

        $this->streamQuery->channel(static::CHANNEL_ACTIVITY);

        if ($this->streamQuery->user) {
            $this->streamQuery->query()->andWhere(['!=', 'user.status', User::STATUS_NEED_APPROVAL]);
            $this->streamQuery->query()->andWhere('content.created_by != :userId', [':userId' => $this->streamQuery->user->id]);
        }
    }
}
