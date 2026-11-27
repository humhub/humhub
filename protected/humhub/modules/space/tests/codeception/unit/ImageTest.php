<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\tests\codeception\unit;

use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * The initials of a space without an image are readable on its colour (WCAG 1.4.3), as `SpaceImage.vue`'s
 */
class ImageTest extends HumHubDbTestCase
{
    public function testInitialsAreWhiteOrBlackWhicheverIsReadable()
    {
        $this->assertStringContainsString('background-color: #123456; color: #FFFFFF;', $this->initials('#123456'));
        // White reaches only 4.08:1 on this teal
        $this->assertStringContainsString('background-color: #028c8e; color: #000000;', $this->initials('#028c8e'));
    }

    public function testInitialsWithoutColourAreInTheTextColour()
    {
        $this->assertStringContainsString(
            'background-color: var(--hh-space-acronym-bg, var(--bs-border-color)); color: var(--bs-body-color);',
            $this->initials(null),
        );
    }

    private function initials(?string $color): string
    {
        $space = Space::findOne(['id' => 1]);
        $space->color = $color;

        return Image::widget(['space' => $space]);
    }
}
