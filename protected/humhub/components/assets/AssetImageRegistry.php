<?php

namespace humhub\components\assets;

use Yii;
use yii\base\Application;
use yii\base\Component;
use yii\base\InvalidConfigException;

class AssetImageRegistry extends Component
{
    public array $definitions = [];

    /**
     * @var array AssetImage[]
     */
    private array $_instances = [];

    /**
     * @var array
     */
    private mixed $_publishedCache;

    public function init()
    {
        parent::init();

        $this->_publishedCache = Yii::$app->cache->get('assetImageCache') ?: [];
        Yii::$app->on(Application::EVENT_AFTER_REQUEST, function ($event) {
            $modified = false;
            /** @var AssetImage $assetImage */
            foreach ($this->_instances as $name => $assetImage) {
                if ($assetImage->cachePublishedDirty) {
                    $this->_publishedCache[$name] = $assetImage->cachePublished;
                    $modified = true;
                }
            }
            if ($modified) {
                Yii::$app->cache->set('assetImageCache', $this->_publishedCache);
            }
        });
    }

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

            if (isset($this->_publishedCache[$name])) {
                $this->_instances[$name]->cachePublished = $this->_publishedCache[$name];
            }
        }

        return $this->_instances[$name];
    }
}
