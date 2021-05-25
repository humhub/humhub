<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\activity\actions;

use humhub\modules\activity\stream\ActivityStreamQuery;
use humhub\modules\content\widgets\stream\StreamEntryOptions;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\stream\actions\ContentContainerStream;

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
    /**
     * @var bool if true the stream will search for activity content
     */
    public $activity = true;

    /**
     * @inheritDoc
     */
    public $streamQueryClass = ActivityStreamQuery::class;

    /**
     * @inheritDoc
     */
    public function initQuery($options = [])
    {
        $options['activity'] = $this->activity;
        return parent::initQuery($options);
    }

    /**
     * @return StreamEntryOptions
     */
    public function initStreamEntryOptions()
    {
        return $this->activity
            ? new StreamEntryOptions()
            : new WallStreamEntryOptions();
    }
}
