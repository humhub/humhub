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
 * @author Luke
 *
 * @property mixed $result Deprecated. Use $value instead.
 */
class Event extends \yii\base\Event
{
    /**
     * @var mixed an optional result which can be manipulated by the event handler.
     * Note that this varies according to which event is currently executing.
     */
    protected $value;


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
