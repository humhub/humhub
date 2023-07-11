<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\exceptions;

trait InvalidArgumentExceptionTrait
{
    //  public properties

    public string $methodName;
    public $parameter;

    public array $valid = [];

    /**
     * @var mixed|null
     */
    public $given;

    /**
     * @param string $method
     * @param int|array $parameter = [
     *                                     int => string,       // position, or [ position => name ] of the  argument
     *                                     ]
     * @param null $given
     */
    public function __construct(
        $method = '',
        $parameter = null,
        $valid = [],
        $given = null,
        $suffix = null,
        $code = 0,
        $previous = null
    ) {
        $this->methodName = $method;
        $this->parameter = $parameter;
        $this->valid = (array)$valid;
        $this->given = $given;

        if (is_int($suffix)) {
            $code = $suffix;
            $previous = $code;
            $suffix = '';
        } elseif ($suffix) {
            $suffix = " $suffix";
        }
        $message = sprintf(
            '%s passed to %s must be %s%s, %s given.',
            $this->formatPrologue(func_get_args()),
            $this->methodName,
            $this->formatValid(),
            $suffix,
            $given
        );

        parent::__construct($message, $code, $previous);
    }

    protected function formatValid(): string
    {
        return (count($this->valid) > 1
                ? 'one of '
                : '') . implode(', ', $this->valid);
    }

    public function getName(): string
    {
        if (method_exists(parent::class, 'getName')) {
            return parent::getName() . " Type";
        }

        return 'Invalid Type';
    }

    abstract protected function formatPrologue(array $constructArguments): string;
}
