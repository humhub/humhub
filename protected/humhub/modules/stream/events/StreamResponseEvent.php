<?php

namespace humhub\modules\stream\events;

use humhub\modules\stream\actions\Stream;
use humhub\modules\stream\actions\StreamResponse;
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
