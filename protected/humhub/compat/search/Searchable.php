<?php

namespace humhub\modules\search\interfaces;

/**
 * @deprecated since 1.16
 */
interface Searchable extends \humhub\modules\content\interfaces\Searchable
{
    public const EVENT_SEARCH_ADD = 'deprecated';
}
