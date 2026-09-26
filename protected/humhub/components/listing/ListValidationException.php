<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing;

use yii\base\InvalidArgumentException;

/**
 * Thrown by {@see FilterableList::build()} for parameters it cannot build a list from; an API
 * controller answers `$this->validationErrors($e->errors)` (`422 {errors}`).
 *
 * @since 1.20
 */
class ListValidationException extends InvalidArgumentException
{
    /**
     * @param array<string, string[]> $errors the messages by request parameter
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('Invalid list parameters: ' . implode(', ', array_keys($errors)) . '.');
    }
}
