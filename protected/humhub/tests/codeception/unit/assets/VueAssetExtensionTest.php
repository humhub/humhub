<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\assets;

use humhub\components\ModuleManager;
use humhub\components\View;
use humhub\modules\friendship\assets\FriendshipVueAsset;
use humhub\modules\user\assets\UserVueAsset;
use ReflectionMethod;
use ReflectionProperty;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\AssetBundle;

/**
 * Bundles declared as extensions of another one (`vueAssetExtensions` in a module's config.php,
 * {@see ModuleManager::addVueAssetExtension()}) are registered along with it by the view.
 */
class VueAssetExtensionTest extends HumHubDbTestCase
{
    private ModuleManager $originalModuleManager;

    protected function setUp(): void
    {
        parent::setUp();

        // Declared extensions are app-wide - work on a copy, so they do not leak into other tests.
        $this->originalModuleManager = Yii::$app->moduleManager;
        Yii::$app->set('moduleManager', clone $this->originalModuleManager);
    }

    protected function tearDown(): void
    {
        Yii::$app->set('moduleManager', $this->originalModuleManager);

        parent::tearDown();
    }

    public function testAnExtensionIsRegisteredRightAfterItsHost()
    {
        Yii::$app->moduleManager->addVueAssetExtension(ExtensionHostTestAsset::class, ExtensionTestAsset::class);
        Yii::$app->moduleManager->addVueAssetExtension(ExtensionHostTestAsset::class, SecondExtensionTestAsset::class);

        $view = new View();
        ExtensionHostTestAsset::register($view);

        $this->assertSame(
            [ExtensionHostTestAsset::class, ExtensionTestAsset::class, SecondExtensionTestAsset::class],
            array_keys($view->assetBundles),
        );
        $this->assertSame(['/test/host.js', '/test/extension.js', '/test/second.js'], $this->renderedJs($view));
    }

    public function testAnExtensionIsRegisteredWhenItsHostIsADependency()
    {
        Yii::$app->moduleManager->addVueAssetExtension(ExtensionHostTestAsset::class, ExtensionTestAsset::class);

        $view = new View();
        DependingOnHostTestAsset::register($view);

        $this->assertArrayHasKey(ExtensionTestAsset::class, $view->assetBundles);
        $this->assertSame(['/test/host.js', '/test/page.js', '/test/extension.js'], $this->renderedJs($view));
    }

    public function testAnExtensionDependingOnItsHostIsNoCircularDependency()
    {
        Yii::$app->moduleManager->addVueAssetExtension(ExtensionHostTestAsset::class, ExtensionDependingOnHostTestAsset::class);

        // Registered directly, the extension registers its host, which would register it again.
        $view = new View();
        ExtensionDependingOnHostTestAsset::register($view);
        $this->assertSame(['/test/host.js', '/test/extension-depending.js'], $this->renderedJs($view));

        $view = new View();
        ExtensionHostTestAsset::register($view);
        $this->assertSame(['/test/host.js', '/test/extension-depending.js'], $this->renderedJs($view));
    }

    public function testAHostRegisteredAgainDoesNotRegisterItsExtensionsAgain()
    {
        Yii::$app->moduleManager->addVueAssetExtension(ExtensionHostTestAsset::class, ExtensionTestAsset::class);
        Yii::$app->moduleManager->addVueAssetExtension(ExtensionTestAsset::class, ExtensionHostTestAsset::class);

        $view = new View();
        ExtensionHostTestAsset::register($view);
        ExtensionHostTestAsset::register($view);
        ExtensionTestAsset::register($view);

        $this->assertSame(['/test/host.js', '/test/extension.js'], $this->renderedJs($view));
    }

    public function testABundleWithoutExtensionsRegistersNothingElse()
    {
        $view = new View();
        ExtensionHostTestAsset::register($view);

        $this->assertSame([ExtensionHostTestAsset::class], array_keys($view->assetBundles));
    }

    public function testTheI18nCategoriesOfAVueBundleAreHandedToTheMounter()
    {
        Yii::$app->assetManager->bundles[FriendshipVueAsset::class] = [
            'i18nCategories' => ['FriendshipModule.base', 'base'],
        ];
        Yii::$app->moduleManager->addVueAssetExtension(UserVueAsset::class, FriendshipVueAsset::class);

        try {
            $view = new View();
            UserVueAsset::register($view);
            UserVueAsset::register($view);

            $this->assertArrayHasKey(FriendshipVueAsset::class, $view->assetBundles);
            // Only bundles declaring categories are listed - the user bundle declares none.
            $this->assertSame(
                ['vueBundles' => [FriendshipVueAsset::class => ['i18nCategories' => ['FriendshipModule.base', 'base']]]],
                array_intersect_key($this->jsConfig($view), ['vueBundles' => true]),
            );
        } finally {
            unset(Yii::$app->assetManager->bundles[FriendshipVueAsset::class]);
        }
    }

    /**
     * The JS files of the registered bundles in the order the page loads them.
     */
    private function renderedJs(View $view): array
    {
        $registerAssetFiles = new ReflectionMethod($view, 'registerAssetFiles');
        foreach (array_keys($view->assetBundles) as $bundle) {
            $registerAssetFiles->invoke($view, $bundle);
        }

        $files = array_map(
            static fn($file) => strtok($file, '?'),
            array_keys($view->jsFiles[View::POS_END] ?? []),
        );

        return array_values(array_filter($files, static fn($file) => str_starts_with($file, '/test/')));
    }

    private function jsConfig(View $view): array
    {
        return (new ReflectionProperty($view, 'jsConfig'))->getValue($view);
    }
}

class ExtensionHostTestAsset extends AssetBundle
{
    public $baseUrl = '/test';
    public $js = ['host.js'];
}

class ExtensionTestAsset extends AssetBundle
{
    public $baseUrl = '/test';
    public $js = ['extension.js'];
}

class SecondExtensionTestAsset extends AssetBundle
{
    public $baseUrl = '/test';
    public $js = ['second.js'];
}

class ExtensionDependingOnHostTestAsset extends AssetBundle
{
    public $baseUrl = '/test';
    public $js = ['extension-depending.js'];
    public $depends = [ExtensionHostTestAsset::class];
}

class DependingOnHostTestAsset extends AssetBundle
{
    public $baseUrl = '/test';
    public $js = ['page.js'];
    public $depends = [ExtensionHostTestAsset::class];
}
