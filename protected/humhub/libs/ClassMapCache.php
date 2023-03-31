<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\models\ClassMap;
use Yii;

/**
 * Class to manage cached storage of class_map table and resolve classIds and classNames accordingly.
 *
 * @author Martin Rüegg
 */
class ClassMapCache
{
    // constants
    public const CACHE_CLASS_MAP = 'class_map';

    //  public properties
    public object $modules;
    public array $classMapById;
    public array $classMapByName;

    // protected properties
    protected static ?self $classMap = null;


    public function __construct()
    {
        $this->modules = (object) [];

        $cache = Yii::$app->cache->get(self::CACHE_CLASS_MAP);

        if ($cache !== false && isset($cache->classMapByName)) {
            $this->classMapByName = &$cache->classMapByName;
            $this->wakeup();

            return;
        }

        $this->load();
    }


    private function addModuleInternal(int $id, string $class_name, string $moduleId): ClassMapCacheModule
    {
        $module = new ClassMapCacheModule($id, $class_name, $moduleId);

        $this->modules->{$module->moduleId}[ $module->id ] = $module->class_name;
        $this->classMapById[ $module->id ]                 = $module;
        $this->classMapByName[ $module->class_name ]       = $module;

        return $module;
    }


    protected function load(): bool
    {
        $this->classMapById   = [];
        $this->classMapByName = [];

        $class_map = ClassMap::find()
                             ->all();

        array_walk(
            $class_map,
            fn (ClassMap $class) => $this->addModuleInternal($class->id, $class->class_name, $class->module_id)
        );

        return $this->save();
    }


    protected function save(): bool
    {

        /**
         * We only actually save the classMapByName to avoid duplicate instances on wakeup and because string array keys
         * are invariable. Its being wrapped in an object to avoid array copying during storage and retrieval.
         */
        return Yii::$app->cache->set(
            self::CACHE_CLASS_MAP,
            (object) [
                'classMapByName' => array_map(static fn ($module) => (object) get_object_vars($module), $this->classMapByName),
            ]
        );
    }


    protected function wakeup(): void
    {
        array_walk($this->classMapByName, static function (&$module, $class, $self) {
            $module = new ClassMapCacheModule($module);

            $self->modules->{$module->moduleId}[ $module->id ] = $module->class_name;
        }, $this);

        $this->classMapById = array_column($this->classMapByName, null, 'id');
    }


    public static function addModule(ClassMap $class): ClassMapCacheModule
    {
        $classMap = static::getClassMap();
        $module   = $classMap->addModuleInternal($class->id, $class->class_name, $class->module_id);

        $classMap->save();

        return $module;
    }


    public static function getClassMap(): self
    {
        return self::$classMap ?? self::$classMap = new ClassMapCache();
    }


    public static function invalidate(): bool
    {
        self::$classMap = null;

        return Yii::$app->cache->delete(self::CACHE_CLASS_MAP);
    }
}
