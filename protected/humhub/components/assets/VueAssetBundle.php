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
 * register the bundle) and must not be added to the compressed core bundle.
 *
 * @since 1.20
 */
abstract class VueAssetBundle extends AssetBundle
{
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
