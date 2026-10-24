<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing;

/**
 * What the filters of a {@see FilterableList} narrow: an `ActiveQuery` for a database list
 * ({@see QueryListBuilder}), an array where the data is not in the database
 * ({@see ArrayListBuilder}, e.g. the marketplace's modules from humhub.com). The filters of a
 * list know their builder type.
 *
 * @since 1.20
 */
interface ListBuilder
{
}
