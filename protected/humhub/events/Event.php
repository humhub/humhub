<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\events;

/**
 * Event is the base class for all event classes.
 *
 * @since 1.16
 */
class Event extends \yii\base\Event
{
    /**
     * @var mixed an optional result which can be manipulated by the event handler.
     * Note that this varies according to which event is currently executing.
     */
    protected $value;

    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): Event
    {
        $this->data = $data;

        return $this;
    }

   /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return static
     */
    public function setValue($value): Event
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param array|null $config
     *
     * @return static
     * @noinspection PhpMissingParamTypeInspection
     */
    public static function create($config = []): Event
    {
        return new static($config);
    }
}
