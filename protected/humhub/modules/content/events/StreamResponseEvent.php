<?php

namespace humhub\modules\content\events;

use humhub\modules\content\actions\Stream;
use humhub\modules\content\actions\StreamResponse;
use yii\base\Event;

class StreamResponseEvent extends Event
{
    /**
     * @var Stream
     */
    public $sender;

    /**
     * @var StreamResponse
     */
    public $response;
}
