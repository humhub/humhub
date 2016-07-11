<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\interfaces;

/**
 * Interface for Searchable Models
 *
 * @package humhub.interfaces
 * @since 0.5
 * @author Luke
 */
interface Searchable
{

    const EVENT_SEARCH_ADD = 'searchadd';

    public function getWallOut();

    public function getSearchAttributes();
}
