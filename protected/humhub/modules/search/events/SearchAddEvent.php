<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\events;

/**
 * This event is used when an object is added to search index
 *
 * @author luke
 * @since 0.21
 */
class SearchAddEvent extends \yii\base\Event
{

    /**
     * @var array Reference to the currently added search attributes
     */
    public $attributes;

    /**
     * @inheritdoc
     */
    public function __construct(&$attributes)
    {
        $this->attributes = &$attributes;
        $this->init();
    }

}
