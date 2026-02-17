<?php

namespace humhub\components\fs;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class FilesystemManager extends Component
{

    public $fsData = 'data';

    public array $mounts = [];

    /**
     * @var array<string, AbstractFs>
     */
    private array $_instances = [];


    public function data(): AbstractFs
    {
        return $this->disk($this->fsData);
    }

    public function disk(string $id): AbstractFs
    {
        if (isset($this->_instances[$id])) {
            return $this->_instances[$id];
        }

        if (!isset($this->mounts[$id])) {
            throw new InvalidConfigException("Mount '$id' not exists.");
        }

        $this->_instances[$id] = Yii::createObject($this->mounts[$id]);

        return $this->_instances[$id];
    }

    public function __get($name)
    {
        if (isset($this->mounts[$name])) {
            return $this->disk($name);
        }
        return parent::__get($name);
    }
}
