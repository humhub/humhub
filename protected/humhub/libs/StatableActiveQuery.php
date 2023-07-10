<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use yii\db\ActiveQuery;

/**
 * @noinspection MissingActiveRecordInActiveQueryInspection
 */


/**
 * @property int[]|null $returnedStates
 */
class StatableActiveQuery extends ActiveQuery implements StatableActiveQueryInterface
{
    use StatableActiveQueryTrait;

    public string $stateColumn = 'state';

    protected ?array $returnedStates
        = [
            StatableInterface::STATE_PUBLISHED,
        ];
}
