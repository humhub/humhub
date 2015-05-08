<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc\Value;

use Zend\XmlRpc\Exception;

class DateTime extends AbstractScalar
{
    /**
     * PHP compatible format string for XML/RPC datetime values
     *
     * @var string
     */
    protected $phpFormatString = 'Ymd\\TH:i:s';

    /**
     * ISO compatible format string for XML/RPC datetime values
     *
     * @var string
     */
    protected $isoFormatString = 'yyyyMMddTHH:mm:ss';

    /**
     * Set the value of a dateTime.iso8601 native type
     *
     * The value is in iso8601 format, minus any timezone information or dashes
     *
     * @param mixed $value Integer of the unix timestamp or any string that can be parsed
     *                     to a unix timestamp using the PHP strtotime() function
     * @throws Exception\ValueException if unable to create a DateTime object from $value
     */
    public function __construct($value)
    {
        $this->type = self::XMLRPC_TYPE_DATETIME;

        if ($value instanceof \DateTime) {
            $this->value = $value->format($this->phpFormatString);
        } elseif (is_numeric($value)) { // The value is numeric, we make sure it is an integer
            $this->value = date($this->phpFormatString, (int) $value);
        } else {
            try {
                $dateTime = new \DateTime($value);
            } catch (\Exception $e) {
                throw new Exception\ValueException($e->getMessage(), $e->getCode(), $e);
            }

            $this->value = $dateTime->format($this->phpFormatString); // Convert the DateTime to iso8601 format
        }
    }

    /**
     * Return the value of this object as iso8601 dateTime value
     *
     * @return int As a Unix timestamp
     */
    public function getValue()
    {
        return $this->value;
    }
}
