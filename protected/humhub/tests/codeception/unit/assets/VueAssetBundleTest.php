<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\assets;

use humhub\assets\CoreVueAsset;
use humhub\components\assets\VueAssetBundle;
use humhub\modules\activity\assets\ActivityVueAsset;
use humhub\modules\comment\assets\CommentVueAsset;
use humhub\modules\content\assets\ContentVueAsset;
use humhub\modules\file\assets\FileVueAsset;
use humhub\modules\friendship\assets\FriendshipVueAsset;
use humhub\modules\like\assets\LikeVueAsset;
use humhub\modules\notification\assets\NotificationVueAsset;
use humhub\modules\space\assets\SpaceVueAsset;
use humhub\modules\user\assets\UserVueAsset;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\InvalidConfigException;

class VueAssetBundleTest extends HumHubDbTestCase
{
    public function testDerivesSourcePathAndArtifactFromTheModuleTheClassBelongsTo()
    {
        // Nothing is declared: the class lives in humhub\modules\comment\assets, that is enough.
        $bundle = new CommentVueAsset();

        $this->assertSame(Yii::getAlias('@comment/resources'), $bundle->sourcePath);
        $this->assertSame(['js/humhub.comment.vue.js'], $bundle->js);
    }

    public function testAlwaysDependsOnTheCoreVueBundleFirst()
    {
        // PHP names an anonymous class after its parent, so it still belongs to the comment module.
        $bundle = new class extends CommentVueAsset {
            public $depends = [LikeVueAsset::class];
        };

        $this->assertSame([CoreVueAsset::class, LikeVueAsset::class], $bundle->depends);
    }

    public function testDoesNotDuplicateAnExplicitCoreDependency()
    {
        $bundle = new class extends CommentVueAsset {
            public $depends = [LikeVueAsset::class, CoreVueAsset::class];
        };

        $this->assertSame([LikeVueAsset::class, CoreVueAsset::class], $bundle->depends);
    }

    public function testRefusesABundleOutsideEveryModuleNamespace()
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('belongs to no registered module');

        // Named `humhub\components\assets\VueAssetBundle@anonymous…` - core, not a module.
        new class extends VueAssetBundle {
        };
    }

    /**
     * Every core module shipping Vue components is built on the base class, is attributed to its
     * module without declaring it, and its committed artifact (plus sourcemap) exists — see
     * `grunt build-vue --module=all`.
     */
    public function testEveryCoreModuleBundleShipsItsArtifact()
    {
        $bundles = [
            'activity' => ActivityVueAsset::class,
            'comment' => CommentVueAsset::class,
            'content' => ContentVueAsset::class,
            'file' => FileVueAsset::class,
            'friendship' => FriendshipVueAsset::class,
            'like' => LikeVueAsset::class,
            'notification' => NotificationVueAsset::class,
            'space' => SpaceVueAsset::class,
            'user' => UserVueAsset::class,
        ];

        foreach ($bundles as $moduleId => $class) {
            $bundle = new $class();
            $this->assertInstanceOf(VueAssetBundle::class, $bundle, $class);
            $this->assertSame(Yii::getAlias('@' . $moduleId . '/resources'), $bundle->sourcePath, $class);
            $this->assertSame(['js/humhub.' . $moduleId . '.vue.js'], $bundle->js, $class);
            $this->assertContains(CoreVueAsset::class, $bundle->depends, $class);

            $artifact = $bundle->sourcePath . '/' . $bundle->js[0];
            $this->assertFileExists($artifact, $class);
            $this->assertFileExists($artifact . '.map', $class);
        }
    }
}
