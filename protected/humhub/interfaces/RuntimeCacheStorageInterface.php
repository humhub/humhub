<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

/**
 * @since 1.15
 */
interface RuntimeCacheStorageInterface extends ArrayLikeInterface
{
    /**
     * @param callable $callback
     *
     * @return bool
     *
     * @noinspection SpellCheckingInspection
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function uasort($callback);
}
