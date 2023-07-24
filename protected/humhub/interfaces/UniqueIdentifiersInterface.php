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
interface UniqueIdentifiersInterface
{
    /**
     * Returns a unique id for this record/model
     *
     * @return String Unique Id of this record
     */
    public function getUniqueId(): string;

    /**
     * Returns a unique id for this record/model
     *
     * @return array of unique ISs of this record
     */
    public function getUniqueIDs(?array $keys = null): array;
}
