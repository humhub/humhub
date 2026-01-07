<?php

namespace humhub\models;

use humhub\components\ActiveRecord;
use humhub\helpers\DataTypeHelper;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "record_map".
 *
 * @property int $id
 * @property string $model
 * @property int $pk
 */
class RecordMap extends ActiveRecord
{

    public static function tableName()
    {
        return 'record_map';
    }

    public static function getId(ActiveRecord $ar): int
    {
        if ($ar->isNewRecord) {
            throw new Exception('Could  not getID for new Record!');
        }

        return Yii::$app->runtimeCache->getOrSet(
            'rm_' . $ar::class . $ar->getPrimaryKey(), function () use ($ar) {

            // ToDo: Check Primary Key is 'int', otherwise throw error
            $record = static::findOne(['model' => $ar::class, 'pk' => (int)$ar->getPrimaryKey()]);
            if ($record) {
                return $record->id;
            }

            $record = new static;
            $record->model = $ar::class;
            $record->pk = (int)$ar->getPrimaryKey();
            $record->save();

            return $record->id;
        });
    }

    /**
     * @template T
     * @param class-string<T> $classType
     * @return T
     */
    public static function getById(int $recordId, string $classType)
    {
        return Yii::$app->runtimeCache->getOrSet(
            'rm_' . $recordId . $classType,
            function () use ($recordId, $classType) {
                $record = static::findOne(['id' => $recordId]);

                if (!DataTypeHelper::isClassType($record->model, $classType)) {
                    Yii::warning(
                        'Invalid class type for record id ' . $recordId . ' Got: ' . $record->model . ' . Expected: ' . $classType
                    );
                    return null;
                }

                /** @var ActiveRecord $model */
                $model = $record->model;

                return $model::findOne(['id' => $record->pk]);
            }
        );
    }

    public static function hasId(ActiveRecord $record): bool
    {
        $record = static::findOne(['model' => $record::class, 'pk' => (int)$record->getPrimaryKey()]);
        if ($record) {
            return true;
        }

        return false;
    }
}
