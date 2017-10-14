<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\events;

/**
 * This event is used to collect additional search attributes for a record.
 *
 * The event object holds an reference to the search index attributes.
 * Modules like comments or files can add additional attributes to it.
 * 
 * @author luke
 * @since 1.2.3
 */
class SearchAttributesEvent extends \yii\base\Event
{

    /**
     * @var array Reference to the currently added search attributes
     */
    public $attributes;

    /**
     * @var \humhub\modules\search\interfaces\Searchable the searchable record
     */
    public $record;

    /**
     * @inheritdoc
     */
    public function __construct(&$attributes, $record)
    {
        $this->attributes = &$attributes;
        $this->record = $record;

        $this->init();
    }

}
