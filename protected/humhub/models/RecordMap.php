<?php

namespace humhub\models;

use humhub\components\ActiveRecord;
use humhub\helpers\DataTypeHelper;
use Yii;
use yii\base\Event;
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
            'rm_' . $ar::class . $ar->getPrimaryKey(),
            function () use ($ar) {
                // ToDo: Check Primary Key is 'int', otherwise throw error
                $record = static::findOne(['model' => $ar::class, 'pk' => (int)$ar->getPrimaryKey()]);
                if ($record) {
                    return $record->id;
                }

                $record = new static();
                $record->model = $ar::class;
                $record->pk = (int)$ar->getPrimaryKey();
                $record->save();

                return $record->id;
            },
        );
    }

    /**
     * @template T
     * @param class-string<T> $classType
     * @return T
     */
    public static function getById(int $recordId, string $classType, bool $logError = true)
    {
        return Yii::$app->runtimeCache->getOrSet(
            'rm_' . $recordId . $classType,
            function () use ($recordId, $classType, $logError) {
                $record = static::findOne(['id' => $recordId]);
                if ($record !== null) {
                    return static::getByModelAndPk($record->model, $record->pk, $classType, $logError);
                }
                return null;
            },
        );
    }

    /**
     * Resolves many record ids at once - one query for the mapping plus one per involved
     * model, instead of two per id the way repeated {@see self::getById()} calls would.
     *
     * Ids that do not exist, or whose record is not of the expected type, are absent from
     * the result; a caller that needs to react to that compares the key sets. The optional
     * `$with` argument is passed to the model query, so a caller that will touch relations
     * of every record (typically `content`) can eager-load them here rather than paying a
     * query per record afterwards.
     *
     * @template T
     * @param int[] $recordIds
     * @param class-string<T> $classType
     * @param string[] $with relations to eager-load
     * @return array<int, T> record id => record, in no particular order
     * @since 1.20
     */
    public static function getByIds(array $recordIds, string $classType, array $with = []): array
    {
        $recordIds = array_values(array_unique(array_map('intval', $recordIds)));

        if ($recordIds === []) {
            return [];
        }

        // model => [pk => record id]
        $pksByModel = [];
        foreach (static::find()->where(['id' => $recordIds])->all() as $mapping) {
            if (!DataTypeHelper::isClassType($mapping->model, $classType)) {
                continue;
            }
            $pksByModel[$mapping->model][(int)$mapping->pk] = (int)$mapping->id;
        }

        $records = [];
        foreach ($pksByModel as $model => $recordIdsByPk) {
            /** @var ActiveRecord $model */
            $query = $model::find()->where(['id' => array_keys($recordIdsByPk)]);

            if ($with !== []) {
                $query->with($with);
            }

            foreach ($query->all() as $record) {
                $records[$recordIdsByPk[(int)$record->getPrimaryKey()]] = $record;
            }
        }

        return $records;
    }

    /**
     * @template T
     * @param class-string<T> $classType
     * @return T
     */
    public static function getByModelAndPk(string $model, string $pk, string $classType, bool $logError = true)
    {
        if (!DataTypeHelper::isClassType($model, $classType)) {
            if ($logError) {
                Yii::warning(
                    'Invalid class type. Got: ' . $model . ' With ID ' . $pk . ' . Expected: ' . $classType,
                );
            }
            return null;
        }

        /** @var ActiveRecord $model */
        return $model::findOne(['id' => $pk]);
    }

    public static function onActiveRecordDelete(Event $event)
    {
        /** @var ActiveRecord $activeRecord */
        $activeRecord = $event->sender;

        $record = static::findOne(['model' => $activeRecord::class, 'pk' => (int)$activeRecord->getPrimaryKey()]);
        if ($record !== null) {
            $record->delete();
        }
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
