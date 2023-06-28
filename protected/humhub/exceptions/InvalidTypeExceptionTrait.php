<?php

/**
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\exceptions;

trait InvalidTypeExceptionTrait
{
    /**
     * @param string $method
     * @param int|array $parameter = [
     *                                     int => string,       // position, or [ position => name ] of the  argument
     *                                     ]
     * @param array|string|null $valid
     * @param null $given
     */
    public function __construct(
        $method = '',
        $parameter = null,
        $valid = [],
        $given = null,
        $nullable = false,
        $suffix = null,
        $code = 0,
        $previous = null
    ) {
        $valid = (array)($valid ?? ['mixed']);

        if ($nullable && !in_array('null', $this->valid, true)) {
            $valid[] = 'null';
        }

        $givenType = get_debug_type($given);
        if (is_scalar($given)) {
            $givenType .= " ($given)";
        }

        parent::__construct($method, $parameter, $valid, $givenType, $suffix, $code, $previous);
    }

    protected function formatValid(): string
    {
        return 'of type ' . implode(', ', $this->valid);
    }

    public function getName(): string
    {
        if (method_exists(parent::class, 'getName')) {
            return parent::getName() . " Type";
        }

        return 'Invalid Type';
    }
}
