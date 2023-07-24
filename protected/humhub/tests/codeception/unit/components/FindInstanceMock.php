<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\FindInstanceTrait;
use humhub\interfaces\FindInstanceInterface;
use humhub\modules\marketplace\widgets\Settings;
use yii\db\ActiveQuery;

class FindInstanceMock implements FindInstanceInterface
{
    use FindInstanceTrait;

    public array $args;

    public function __construct(...$args)
    {
        $this->args = $args;
    }

    public static function find()
    {
        return new ActiveQuery(Settings::class);
    }

    public static function findOne($condition): ?self
    {
        return new static($condition);
    }

    public static function findInstance($identifier, array $config = []): ?self
    {
        return static::findInstanceHelper($identifier, $config);
    }
}
