<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\exceptions;

use Throwable;
use yii\base\InvalidArgumentException;

class InvalidStateException extends InvalidArgumentException
{
    public array $invalidStates;
    public array $availableStates;

    public array $config;

    public function __construct(
        $message = "",
        $code = 0,
        Throwable $previous = null,
        array $invalidStates = [],
        array $availableStates = [],
        array $config = []
    ) {
        $this->invalidStates   = $invalidStates;
        $this->availableStates = $availableStates;
        $this->config          = $config;

        parent::__construct($message, $code, $previous);
    }
}
