<?php

namespace humhub\components\assets;

use humhub\helpers\TrackableArray;
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
    private mixed $_cache;

    public function init()
    {
        parent::init();

        /**
         * We're caching published URLs and existence of default Asset Images (e.g. Logo/Icon) to avoid
         * Filesystem lookups
         */
        $this->_cache = Yii::$app->cache->get('assetImageRegistry') ?: [];
        Yii::$app->on(Application::EVENT_AFTER_REQUEST, function ($event): void {
            $modified = false;
            /** @var AssetImage $assetImage */
            foreach ($this->_instances as $name => $assetImage) {
                if ($assetImage->cachePublish->hasChanged()) {
                    $this->_cache[$name]['published'] = $assetImage->cachePublish->toArray();
                    $modified = true;
                }
                if (!isset($this->_cache[$name]['fileExists']) ?? $this->_cache[$name]['fileExists'] !== $assetImage->fileExists) {
                    $this->_cache[$name]['fileExists'] = $assetImage->fileExists;
                    $modified = true;
                }
            }
            if ($modified) {
                Yii::$app->cache->set('assetImageRegistry', $this->_cache);
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
                array_merge(
                    $this->definitions[$name],
                    [
                        'cachePublish' => new TrackableArray($this->_cache[$name]['published'] ?? []),
                        'fileExists' => $this->_cache[$name]['fileExists'] ?? null,
                    ],
                ),
            );
        }

        return $this->_instances[$name];
    }
}
