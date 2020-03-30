<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\assets\AppAsset;
use humhub\assets\CoreBundleAsset;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\FileHelper;
use yii\web\AssetBundle;

/**
 * AssetManager
 *
 * @inheritdoc
 * @author Luke
 */
class AssetManager extends \yii\web\AssetManager
{
    private $_published = [];

    /**
     * Clears all currently published assets
     */
    public function clear()
    {
        if ($this->basePath == '') {
            return;
        }

        foreach (scandir($this->basePath) as $file) {
            if (substr($file, 0, 1) === '.') {
                continue;
            }
            FileHelper::removeDirectory($this->basePath . DIRECTORY_SEPARATOR . $file);
        }
    }

    /**
     * Workaround for modules not merged to HumHub v1.5.
     * HumHub v1.5 introduced a deferred CoreBundleAsset.
     * This workaround adds 'defer' and the CoreBundleAsset dependency to all non core and non migrated AssetBundles which
     * is the default in humhubs base AssetBundle.
     *
     * @param string $name
     * @param array $config
     * @param bool $publish
     * @return AssetBundle
     * @throws \yii\base\InvalidConfigException
     */
    protected function loadBundle($name, $config = [], $publish = true)
    {
        $bundle = parent::loadBundle($name, $config, $publish);
        $bundleClass = get_class($bundle);

        if($bundleClass !== AppAsset::class
           && !in_array($bundleClass, AppAsset::STATIC_DEPENDS)
           && !in_array($bundleClass, CoreBundleAsset::STATIC_DEPENDS)
           && !is_subclass_of($bundleClass, assets\AssetBundle::class)) {
            array_unshift($bundle->depends, CoreBundleAsset::class);
            $bundle->jsOptions['defer'] = 'defer';
        }

        return $bundle;
    }

    public function forcePublish(AssetBundle $bundle, $options = [])
    {
        $options['forceCopy'] = true;

        if ($bundle->sourcePath !== null && !isset($bundle->basePath, $bundle->baseUrl)) {
            $path = Yii::getAlias($bundle->sourcePath);

            if (!is_string($path) || ($src = realpath($path)) === false) {
                throw new InvalidArgumentException("The file or directory to be published does not exist: $path");
            }

            if (is_file($src)) {
                return $this->publishFile($src);
            } else {
                return $this->publishDirectory($src, $options);
            }
        } else {
            $bundle->publish($this);
        }
    }
}
