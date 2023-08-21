<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

/**
 * Event is the base class for all event classes.
 *
 * @since 1.2.3
 * @deprecated since 1.16; Use \humhub\events\Event.
 * @see \humhub\events\Event
 *
 * @property mixed $result Deprecated. Use $value instead.
 */
class Event extends \humhub\events\Event
{
    /**
     * @param mixed $value
     * @return static
     * @deprecated since 1.15. Use static::setValue()
     * @see static::setValue()
     * @noinspection PhpUnused
     */
    public function setResult($value): Event
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     * @see static::getValue()
     * @deprecated since 1.15. Use static::getValue()
     */
    public function getResult()
    {
        return $this->value;
    }
}
