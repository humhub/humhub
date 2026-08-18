<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\helpers\Html;
use humhub\modules\like\assets\LikeAsset;
use humhub\widgets\VueComponent;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;

class VueComponentTest extends HumHubDbTestCase
{
    public function testRendersKebabCaseTagWithScalarProps()
    {
        $html = VueComponent::widget([
            'name' => 'LikeButton',
            'props' => [
                'likeUrl' => '/like/like/like?recordId=1',
                'likeCount' => 3,
                'currentUserLiked' => true,
            ],
        ]);

        $this->assertStringContainsString('<like-button', $html);
        $this->assertStringContainsString('like-count="3"', $html);
        $this->assertStringContainsString('current-user-liked="true"', $html);
        $this->assertStringContainsString('</like-button>', $html);
    }

    public function testFalseBooleanIsRenderedAsStringFalse()
    {
        $html = VueComponent::widget([
            'name' => 'LikeButton',
            'props' => ['currentUserLiked' => false],
        ]);

        $this->assertStringContainsString('current-user-liked="false"', $html);
    }

    public function testConsecutiveCapitalsDeriveTheSameTagAsTheJsRuntime()
    {
        $html = VueComponent::widget(['name' => 'PDFViewer']);
        $this->assertStringContainsString('<pdf-viewer', $html);

        $html = VueComponent::widget(['name' => 'HButton']);
        $this->assertStringContainsString('<h-button', $html);
    }

    public function testComplexPropsAreJsonEncodedIntoPropsAttribute()
    {
        $html = VueComponent::widget([
            'name' => 'TestList',
            'props' => ['items' => ['a', 'b']],
        ]);

        $expected = 'props="' . Html::encode(Json::encode(['items' => ['a', 'b']])) . '"';
        $this->assertStringContainsString($expected, $html);
    }

    public function testContentAndOptionsAreRendered()
    {
        $html = VueComponent::widget([
            'name' => 'LikeButton',
            'content' => '<span>loading</span>',
            'options' => ['id' => 'my-island', 'class' => 'test-class'],
        ]);

        $this->assertStringContainsString('<span>loading</span>', $html);
        $this->assertStringContainsString('id="my-island"', $html);
        $this->assertStringContainsString('class="test-class"', $html);
    }

    public function testRegistersTheAssetBundle()
    {
        VueComponent::widget([
            'name' => 'LikeButton',
            'assetBundle' => LikeAsset::class,
        ]);

        $this->assertArrayHasKey(LikeAsset::class, Yii::$app->view->assetBundles);
    }

    public function testSingleWordNameThrows()
    {
        $this->expectException(InvalidConfigException::class);
        VueComponent::widget(['name' => 'Badge']);
    }

    public function testNonPascalCaseNameThrows()
    {
        $this->expectException(InvalidConfigException::class);
        VueComponent::widget(['name' => 'like-button']);
    }

    public function testPropMappingToReservedClassAttributeThrows()
    {
        $this->expectException(InvalidConfigException::class);
        VueComponent::widget([
            'name' => 'LikeButton',
            'props' => ['class' => 'x'],
        ]);
    }

    public function testPropMappingToDataAttributeThrows()
    {
        $this->expectException(InvalidConfigException::class);
        VueComponent::widget([
            'name' => 'LikeButton',
            'props' => ['dataFoo' => 'x'],
        ]);
    }

    public function testScalarPropCollidingWithOptionsThrows()
    {
        $this->expectException(InvalidConfigException::class);
        VueComponent::widget([
            'name' => 'LikeButton',
            'props' => ['title' => 'a'],
            'options' => ['title' => 'b'],
        ]);
    }

    public function testPropNamedPropsThrows()
    {
        $this->expectException(InvalidConfigException::class);
        VueComponent::widget([
            'name' => 'LikeButton',
            'props' => ['props' => 'scalar'],
        ]);
    }

    public function testComplexPropsCollidingWithOptionsPropsThrows()
    {
        $this->expectException(InvalidConfigException::class);
        VueComponent::widget([
            'name' => 'LikeButton',
            'props' => ['items' => ['a', 'b']],
            'options' => ['props' => 'x'],
        ]);
    }
}
