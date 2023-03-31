<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\components\ActiveRecord;
use humhub\components\Migration;
use humhub\models\ClassMap;
use yii\base\ErrorException;
use yii\db\Exception;

/**
 * Supporting the migration of class/object_model fiels into class_mao table reference.
 *
 * @author Martin Rüegg
 */
class ClassMapReferenceMigrationHelper extends Migration
{
    /**
     * @param string|ActiveRecord $tableOrModel
     * @param string $oldColumn
     * @param string|null $newColumn
     * @param string|null $pk
     * @param bool $notNull
     * @param bool $dropOriginal
     * @param array|null $dropIndices
     *
     * @return void
     * @throws Exception
     * @throws \ReflectionException
     * @throws ErrorException
     */
    public function migrateReference(string $tableOrModel, string $oldColumn, ?string $newColumn = null, ?string $pk = 'id', ?bool $notNull = true, ?bool $dropOriginal = true, ?array $dropIndices = []): void
    {
        $newColumn ??= $oldColumn . "_id";
        $pk ??= 'id';
        $notNull ??= true;
        $dropOriginal ??= true;
        $dropIndices ??= [];

        if ((false === strpos($tableOrModel, '\\')) && !class_exists($tableOrModel, false)) {
            $model = new class extends ActiveRecord {
                public static string $tableName = '';


                /**
                 * @inheridoc
                 * @noinspection PhpMissingReturnTypeInspection
                 * @noinspection ReturnTypeCanBeDeclaredInspection
                 */
                public static function tableName()
                {
                    return self::$tableName;
                }
            };

            $table = $model::$tableName = $tableOrModel;
        } else {
            $model = $tableOrModel;
            $table = $tableOrModel::tableName();
        }

        $index = "fk-$table-$newColumn";
        $self = $this;

        array_walk($dropIndices, static function ($index) use ($self, $table) {
            $self->dropIndex($index, $table);
        });

        $this->safeAddColumn(
            $table,
            $newColumn,
            $this->integerReferenceKeyUnsigned()
                ->after($oldColumn)
        );

        $records = $model::find()
            ->all();

        array_walk($records, static function (ActiveRecord $record) use ($oldColumn, $newColumn, $pk) {
            $record->$newColumn = ClassMap::getIdByName($record->$oldColumn);
            if (!$record->save()) {
                throw new Exception(sprintf('Could not save %s', $record->$pk));
            }
        });

        $this->safeAddForeignKey(
            $index,
            $table,
            $newColumn,
            'class_map',
            'id',
            'CASCADE',
            'CASCADE'
        );

        if ($notNull) {
            $this->alterColumn(
                $table,
                $newColumn,
                $this->integerReferenceKeyUnsigned()
                    ->notNull()
            );
        }

        if ($dropOriginal) {
            $this->dropColumn($table, $oldColumn);
        }
    }
}
