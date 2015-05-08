<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Formatter;

use DateTime;
use Traversable;
use Zend\Stdlib\ErrorHandler;

class Base implements FormatterInterface
{
    /**
     * Format specifier for DateTime objects in event data (default: ISO 8601)
     *
     * @see http://php.net/manual/en/function.date.php
     * @var string
     */
    protected $dateTimeFormat = self::DEFAULT_DATETIME_FORMAT;

    /**
     * Class constructor
     *
     * @see http://php.net/manual/en/function.date.php
     * @param null|string|array|Traversable $dateTimeFormat Format for DateTime objects
     */
    public function __construct($dateTimeFormat = null)
    {
        if ($dateTimeFormat instanceof Traversable) {
            $dateTimeFormat = iterator_to_array($dateTimeFormat);
        }

        if (is_array($dateTimeFormat)) {
            $dateTimeFormat = isset($dateTimeFormat['dateTimeFormat'])? $dateTimeFormat['dateTimeFormat'] : null;
        }

        if (null !== $dateTimeFormat) {
            $this->dateTimeFormat = $dateTimeFormat;
        }
    }

    /**
     * Formats data to be written by the writer.
     *
     * @param array $event event data
     * @return array
     */
    public function format($event)
    {
        foreach ($event as $key => $value) {
            // Keep extra as an array
            if ('extra' === $key) {
                $event[$key] = self::format($value);
            } else {
                $event[$key] = $this->normalize($value);
            }
        }

        return $event;
    }

    /**
     * Normalize all non-scalar data types (except null) in a string value
     *
     * @param mixed $value
     * @return mixed
     */
    protected function normalize($value)
    {
        if (is_scalar($value) || null === $value) {
            return $value;
        }

        // better readable JSON
        static $jsonFlags;
        if ($jsonFlags === null) {
            $jsonFlags = 0;
            $jsonFlags |= defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0;
            $jsonFlags |= defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0;
        }

        ErrorHandler::start();
        if ($value instanceof DateTime) {
            $value = $value->format($this->getDateTimeFormat());
        } elseif ($value instanceof Traversable) {
            $value = json_encode(iterator_to_array($value), $jsonFlags);
        } elseif (is_array($value)) {
            $value = json_encode($value, $jsonFlags);
        } elseif (is_object($value) && !method_exists($value, '__toString')) {
            $value = sprintf('object(%s) %s', get_class($value), json_encode($value));
        } elseif (is_resource($value)) {
            $value = sprintf('resource(%s)', get_resource_type($value));
        } elseif (!is_object($value)) {
            $value = gettype($value);
        }
        ErrorHandler::stop();

        return (string) $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormat()
    {
        return $this->dateTimeFormat;
    }

    /**
     * {@inheritDoc}
     */
    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->dateTimeFormat = (string) $dateTimeFormat;
        return $this;
    }
}
