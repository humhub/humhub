<?php

namespace humhub\components\fs;

use League\Flysystem\Filesystem as FlysystemFilesystem;
use League\Flysystem\FilesystemAdapter;
use yii\base\Component;

/**
 * Simple Wrapper for Flysystem
 *
 * @mixin FlysystemFilesystem
 */
abstract class AbstractFs extends Component
{
    private FlysystemFilesystem $flySystem;

    public string $baseUrl = '';

    abstract protected function getAdapter(): FileSystemAdapter;

    public function init(): void
    {
        parent::init();
        $this->flySystem = new FlysystemFilesystem($this->getAdapter());
    }

    public function __call($name, $params)
    {
        if (method_exists($this->flySystem, $name)) {
            return call_user_func_array([$this->flySystem, $name], $params);
        }
        return parent::__call($name, $params);
    }

    public function getOperator()
    {
        return $this->flySystem;
    }
}
