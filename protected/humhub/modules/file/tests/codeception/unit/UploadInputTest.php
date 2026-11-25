<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\widgets\UploadInput;
use tests\codeception\_support\HumHubDbTestCase;

class UploadInputTest extends HumHubDbTestCase
{
    public function testHiddenInputIsNamedByAriaLabel()
    {
        $html = UploadInput::widget(['name' => 'files[]', 'url' => '/upload']);

        // Accessibility checkers (WAVE) inspect hidden inputs too, and warn of one named only by its title
        $this->assertStringContainsString('aria-label="Upload file"', $html);
        $this->assertStringNotContainsString('title=', $html);
    }
}
