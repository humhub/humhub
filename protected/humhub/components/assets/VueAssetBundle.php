<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\assets;

use humhub\assets\CoreVueAsset;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Base class of the asset bundle shipping a module's compiled Vue components — the committed
 * artifact `resources/js/humhub.<module>.vue.js` that `grunt build-vue --module=<module>`
 * writes (see docs/develop/ui-js-vuejs.md).
 *
 * The module a bundle belongs to follows from its class: the [[\humhub\components\ModuleManager]]
 * knows every registered module's namespace ({@see \humhub\components\ModuleManager::getModuleIdByClass()}),
 * so a subclass declares nothing - source path (`@<module>/resources`) and artifact file
 * (`js/humhub.<module>.vue.js`) are derived. A bundle class therefore has to live inside its
 * module's namespace, which is where every module keeps its asset bundles anyway.
 * The bundle always depends on [[CoreVueAsset]], and the dependency is load-bearing rather
 * than cosmetic: the artifact registers its components against the runtime that bundle
 * provides, and Yii emits bundles in registration order — a widget in a view registers its
 * bundle before the layout registers the core bundle, so without the edge the module script
 * would run first and fail. Components a module's templates reference by tag only
 * (`<LikeButton>`, `<UserImage>`, …) must likewise be registered before the artifact runs,
 * or Vue warns "Failed to resolve component" and renders nothing there — list the providing
 * modules' `*VueAsset` bundles in [[$depends]]; the core dependency is prepended automatically.
 *
 * Module artifacts are loaded on demand (only pages rendering one of the module's islands
 * register the bundle) and must not be added to the compressed core bundle. A bundle whose
 * components go into another module's islands (extension slots, menu entries, filter types)
 * is declared an extension of that module's bundle in the module's `config.php`
 * (`vueAssetExtensions`, see {@see \humhub\components\ModuleManager::addVueAssetExtension()}),
 * and is then registered wherever that bundle is.
 *
 * @since 1.20
 */
abstract class VueAssetBundle extends AssetBundle
{
    /**
     * @var string[] message categories preloaded before any island of a page registering this
     * bundle mounts, in addition to the `i18nCategories` the mounted component declares itself.
     * The mounter only consults the top-level component of an island, so this is where a bundle
     * declares the categories of the components it contributes to other islands — an extension
     * slot entry, a menu entry, a filter type — which are rendered nested in a host component
     * that knows nothing of them.
     * @since 1.20
     */
    public array $i18nCategories = [];
    /**
     * @inheritdoc
     */
    public function init()
    {
        $moduleId = Yii::$app->moduleManager->getModuleIdByClass(static::class);

        if ($moduleId === null) {
            throw new InvalidConfigException(
                static::class . ' belongs to no registered module: a Vue asset bundle has to live in its module\'s namespace.',
            );
        }

        $this->sourcePath ??= '@' . $moduleId . '/resources';

        if ($this->js === []) {
            $this->js = ['js/humhub.' . $moduleId . '.vue.js'];
        }

        if (!in_array(CoreVueAsset::class, $this->depends, true)) {
            array_unshift($this->depends, CoreVueAsset::class);
        }

        parent::init();
    }
}
