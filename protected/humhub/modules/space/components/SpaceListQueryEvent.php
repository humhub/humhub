<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\components;

use humhub\events\ActiveQueryEvent;
use humhub\modules\user\models\User;

/**
 * The event {@see SpaceListQuery::EVENT_INIT} is triggered with.
 *
 * @since 1.20
 */
class SpaceListQueryEvent extends ActiveQueryEvent
{
    /**
     * @var ActiveQuerySpace the list's query, filtered and ordered — a handler narrows it
     *      further (`andWhere()`), it does not widen it
     */
    public $query;

    /**
     * @var array every parameter the list was built from, including those the core does not
     *      know itself (e.g. a module's `category`) — for the HTTP API these are the request's
     *      query parameters, the known ones already normalized
     */
    public array $params = [];

    /**
     * @var string|null what the list is for: one of {@see SpaceListQuery::PURPOSES}, `null`
     *      when the caller did not say (neutral)
     */
    public ?string $purpose = null;

    /**
     * @var User|null whose list it is, `null` for a guest
     */
    public ?User $user = null;
}
