<?php

/**
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\models;

use humhub\libs\ClassMapCache;
use humhub\libs\ClassMapCacheModule;
use ReflectionClass;
use ReflectionException;
use Yii;
use yii\base\ErrorException;
use yii\base\InvalidCallException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "module_enabled".
 *
 * @author Martin Rüegg
 *
 * @property int $id
 * @property string $class_name
 * @property ?string $source_file
 * @property-read \yii\db\ActiveQuery $moduleEnable
 * @property string $module_id
 */
class ClassMap extends ActiveRecord
{
    public function getModuleEnable(): ActiveQuery
    {
        return $this->hasOne(ModuleEnabled::class, ['module_id' => 'module_id']);
    }


    public function afterDelete()
    {
        ClassMapCache::invalidate();

        parent::afterDelete();
    }


    public function beforeSave($insert)
    {
        // make sure only the relative paths are saved.
        if (($path = $this->source_file) && $this->isAttributeChanged('source_file')) {
            $root = Yii::getAlias('@webroot');
            if (0 === strpos($path, $root)) {
                $this->source_file = substr($path, strlen($root) + 1);
            } elseif ($root !== ($realpath = realpath($root)) && 0 === strpos($path, $realpath)) {
                $this->source_file = substr($path, 0, strlen($realpath) + 1);
            }
        }

        return parent::beforeSave($insert);
    }


    public function afterSave(
        $insert,
        $changedAttributes
    ) {
        ClassMapCache::invalidate();

        parent::afterSave($insert, $changedAttributes);
    }


    /**
     * @inheritdoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function attributeLabels()
    {
        return [
            'class_name' => 'Class Name',
            'module_id' => 'Module ID',
            'source_file' => 'Source File',
        ];
    }


    /**
     * @inheritdoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function rules()
    {
        return [
            [
                ['id'],
                'integer',
            ],
            [
                [
                    'module_id',
                    'class_name',
                ],
                'required',
            ],
            [
                ['module_id'],
                'string',
                'max' => 100,
            ],
            [
                ['class_name'],
                'string',
                'max' => 668,
            ],
            [
                ['source_file'],
                'string',
                'max' => 768,
            ],
        ];
    }

    public static function getClassById(?int $id): ?string
    {
        if ($id === null) {
            return null;
        }

        $class = ClassMapCache::getClassMap()->classMapById[$id] ?? null;

        return $class->class_name ?? null;
    }

    public static function getClassMap()
    {
        return ClassMapCache::getClassMap();
    }

    /**
     * @param string|string[] $value Class name or names to be translated int their respective ID
     *
     * @return int|int[] ClassId or IDs, depending on the input parameter's type
     * @throws ReflectionException
     * @throws ErrorException
     */
    public static function getIdByName($value)
    {
        return is_array($value)
            ? ClassMap::getIdByManyNames($value)
            : ClassMap::getIdByOneName($value);
    }

    /**
     * @throws ReflectionException
     * @throws ErrorException
     */
    public static function &getIdByManyNames(array $names): array
    {
        array_walk($names, static function (
            &$name
        ) {
            $name = static::getIdByOneName($name);
        });

        return $names;
    }

    /**
     * @throws ReflectionException
     * @throws ErrorException
     */
    public static function getIdByOneName(string $name): int
    {
        $class = ClassMapCache::getClassMap()->classMapByName[$name] ?? static::addClass($name);

        return $class->id;
    }

    /**
     * @throws ReflectionException
     * @throws ErrorException|InvalidCallException
     */
    protected static function addClass(string $name): ClassMapCacheModule
    {
        $reflection = new ReflectionClass($name);

        $classFilePath = $reflection->getFileName();

        if (!$classFilePath) {
            throw new InvalidCallException(
                sprintf('Class %s belongs to %s and is not supported.', $name, $reflection->getExtensionName())
            );
        }

        $module = Yii::$app->moduleManager->getModuleInfoByFilePath($classFilePath);

        if (!$module) {
            throw new InvalidCallException(
                sprintf(
                    'Module for class %s could not be determined based on the given file pat h(%s)',
                    $name,
                    $classFilePath
                )
            );
        }

        $record = new static([
            'class_name' => $name,
            'source_file' => $classFilePath,
            'module_id' => $module->isCoreModule
                ? ModuleEnabled::FAKE_CORE_MODULE_ID
                : $module->moduleId,
        ]);

        if (!$record->save()) {
            throw new ErrorException('Record could not be saved.');
        }

        return ClassMapCache::addModule($record);
    }

    /**
     * @param string|object|string[]|object[] $nameOrObject object or class name or array thereof.
     *
     * @throws ReflectionException
     * @throws ErrorException
     */
    public static function getIdBy($nameOrObject): int
    {
        if (is_object($nameOrObject)) {
            $nameOrObject = get_class($nameOrObject);
        }

        if (is_array($nameOrObject)) {
            array_walk($nameOrObject, static function (&$nameOrObject) {
                $nameOrObject = static::getIdBy($nameOrObject);
            });
        }

        return static::getIdByOneName($nameOrObject);
    }

    /**
     * @param \yii\db\ActiveQuery|null $query
     * @param string $table
     * @param string $sourceColumn
     * @param string|null $targetColumn
     * @param bool $activeOnly
     * @param string|null $type
     * @param string|string[]|array|null $addSelect
     * @param string|null $tableClassMapAlias
     *
     * @return \yii\db\ActiveQuery
     */
    public static function joinClassMap(
        ?Query $query,
        string $table,
        string $sourceColumn,
        ?string $targetColumn = 'id',
        ?bool $activeOnly = true,
        ?string $type = 'INNER',
        $addSelect = 'class_name',
        ?string &$tableClassMapAlias = null
    ): Query {
        static $tableClassMap;
        static $tableModuleEnabled;

        $query ??= (new Query())->from($table);
        $tableClassMap ??= static::tableName();
        $tableModuleEnabled ??= ModuleEnabled::tableName();
        $targetColumn ??= 'id';
        $activeOnly ??= true;
        $type ??= 'INNER';

        $unique_identifier = "{$table}_{$sourceColumn}";
        $pk = "{$unique_identifier}_id";
        $tableClassMapAlias ??= "cm_restricting_{$unique_identifier}";

        $select = [
            $pk => "$tableClassMap.$targetColumn",
        ];

        $subQuery = (new Query())->from($tableClassMap);

        if ($activeOnly) {
            $subQuery
                ->innerJoin(
                    $tableModuleEnabled,
                    "$tableClassMap.module_id = $tableModuleEnabled.module_id AND $tableModuleEnabled.is_paused = 0"
                );
        }

        if ($addSelect) {
            if (is_string($addSelect)) {
                $addSelect = ['class_name' => $addSelect];
            } else {
                $addSelect = (array)$addSelect;
            }

            $addSelect = array_filter(
                $addSelect,
                static function (
                    $alias,
                    $source
                ) use (
                    $subQuery,
                    $tableClassMapAlias,
                    $tableClassMap,
                    &
                    $select
                ) {
                    if (is_int($source)) {
                        $source = $alias;
                    }

                    if (is_array($source) || false !== strpos(trim($source), ' ')) {
                        return true;
                    }

                    if (false === strpos($source, '.')) {
                        //$source = "$tableClassMapAlias.$source";
                        $source = "$tableClassMap.$source";
                    }

                    $select[$alias] = $source;

                    return false;
                },
                ARRAY_FILTER_USE_BOTH
            );
        }

        array_walk(
            $select,
            static fn(
                &$source,
                $alias
            ) => $source = "$source AS $alias"
        );
        $subQuery->select(implode(', ', $select));

        $query
            ->join(
                "$type JOIN",
                [$tableClassMapAlias => $subQuery],
                "$table.$sourceColumn = $tableClassMapAlias.$pk"
            );

        if (!empty($addSelect)) {
            $query->addSelect($addSelect);
        }

        return $query;
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'class_map';
    }
}
