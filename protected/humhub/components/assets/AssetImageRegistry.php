<?php

namespace humhub\components\assets;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class AssetImageRegistry extends Component
{
    public array $definitions = [];
    private array $_instances = [];

    public function __get($name)
    {
        if (!isset($this->_instances[$name])) {
            if (!isset($this->definitions[$name])) {
                throw new InvalidConfigException("Image '$name' is not defined in ImageRegistry.");
            }

            $this->_instances[$name] = Yii::$container->get(
                AssetImage::class,
                [],
                $this->definitions[$name],
            );
        }

        return $this->_instances[$name];
    }
}
