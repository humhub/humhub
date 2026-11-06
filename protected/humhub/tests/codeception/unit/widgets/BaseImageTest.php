<?php

namespace humhub\tests\codeception\unit\widgets;

use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image as SpaceImage;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\widgets\BaseImage;
use tests\codeception\_support\HumHubDbTestCase;

class BaseImageTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    public function testSizeStyleCanBeOverriddenByCssVariables()
    {
        $this->assertSame(
            'width: var(--hh-content-container-image-width, 32px); height: var(--hh-content-container-image-height, 40px);',
            BaseImage::getSizeStyle(32, 40),
        );
    }

    public function testUserImageWritesItsSizeByTheCssVariables()
    {
        $html = UserImage::widget(['user' => User::findOne(['id' => 1]), 'width' => 32, 'link' => false]);

        $this->assertStringContainsString('width: var(--hh-content-container-image-width, 32px)', $html);
        $this->assertStringContainsString('height: var(--hh-content-container-image-height, 32px)', $html);
    }

    public function testSpaceImageWritesItsSizeByTheCssVariables()
    {
        $html = SpaceImage::widget(['space' => Space::findOne(['id' => 1]), 'width' => 50, 'link' => false]);

        // The acronym and the image
        $this->assertSame(2, substr_count($html, 'width: var(--hh-content-container-image-width, 50px)'));
        $this->assertSame(2, substr_count($html, 'height: var(--hh-content-container-image-height, 50px)'));
    }
}
