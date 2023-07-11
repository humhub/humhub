<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\exceptions\InvalidArgumentException;
use yii\db\ActiveRecord;

class ObjectModel
{
    /**
     * @var ActiveRecord|string|null
     */
    public ?string $object_model = null;
    public ?int $object_class_id = null;
    public ?int $object_id = null;

    /**
     * @param string|int|null $object_model
     * @param int|null $object_id
     */
    public function __construct(?string $object_model, ?int $object_id)
    {
        if (null === $object_class_id = filter_var($object_model, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
            $this->object_model = $object_model;
        } else {
            $this->object_class_id = $object_class_id;
        }
        $this->object_id = $object_id;
    }

    /**
     * @param ActiveRecord|string|null $class
     * @return ActiveRecord|null
     */
    public function getObject(?string $class = null): ?ActiveRecord
    {
        if ($class === null) {
            $class = ActiveRecord::class;
        } elseif (!is_a($class, ActiveRecord::class, true)) {
            throw new InvalidArgumentException(__METHOD__, [1 => '$class'], ActiveRecord::class, $class);
        }

        if ($this->object_id && $this->object_model && is_a($this->object_model, $class, true)) {
            return $this->object_model::findOne(['id' => $this->object_id]);
        }

        return null;
    }
}
