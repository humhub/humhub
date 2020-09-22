<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\live;

use humhub\modules\live\components\LiveEvent;

/**
 * Live event for new contents
 *
 * @since 1.2
 */
class NewContent extends LiveEvent
{

    /**
     * @var int the id of the new content
     */
    public $contentId;

    /**
     * @var string space guid for space content container
     */
    public $sguid;

    /**
     * @var string user guid for user content container
     */
    public $uguid;

    /**
     * @var string originator guid
     */
    public $originator;

    /**
     * @var string class of the ContentActiveRecord
     */
    public $sourceClass;

    /**
     * @var int id of the ContentActiveRecord
     */
    public $sourceId;

    /**
     * @var boolean if true it's meant to be a silent content creation
     */
    public $silent;

    /**
     * @var string|null content stream_channel
     * @since 1.5
     */
    public $streamChannel;

    /**
     * @var bool whether or not this was triggered for content creation
     */
    public $insert;

}
