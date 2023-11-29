<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace tests\codeception\_support;


trait DebugTrait
{
    /**
     * Print a debug message to the screen.
     *
     * @param $message
     */
    protected function debug($message): string
    {
        if (is_string($message)) {
            $message = $this->debugString($message);
        }

        codecept_debug($message);

        return $message;
    }

    /**
     * Print a debug message with a title
     */
    protected function debugSection($message, $title)
    {
        $this->debug(sprintf("[%s] %s", $title, $this->debugString($message)));
    }

    /**
     * Convert $variable tp string
     */
    protected function debugString($variable): string
    {
        if (is_array($variable) || is_object($variable)) {
            try {
                return stripslashes(json_encode($variable, JSON_THROW_ON_ERROR));
            } catch (\JsonException $e) {
                return serialize($variable);
            }
        }

        if (!is_string($variable) || is_int($variable)) {
            return var_export($variable, true);
        }

        return (string)$variable;
    }
}
