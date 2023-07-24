<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use ArrayAccess;
use Countable;
use Iterator;
use Traversable;

/**
 * @since 1.15
 */
interface ArrayLikeInterface extends ArrayAccess, Countable, Traversable
{
    public function append($value): self;

    /**
     * @param int|string|null $column_key
     * @param int|string|null $index_key
     *
     * @return array
     */
    public function column($column_key, $index_key = null): array;

    public function reset(): self;

    /**
     * @param array|object $array
     */
    public function exchangeArray($array);

    /**
     * @return array
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getArrayCopy();

    /**
     * @return Iterator
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getIterator();

    /**
     * @return string
     */
    public function getIteratorClass();
    public function setIteratorClass(string $iteratorClass);
}
