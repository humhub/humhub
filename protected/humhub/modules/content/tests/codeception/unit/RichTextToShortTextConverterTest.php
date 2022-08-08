<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\tests\codeception\unit;

use humhub\modules\content\widgets\richtext\RichText;
use tests\codeception\_support\HumHubDbTestCase;

class RichTextToShortTextConverterTest extends HumHubDbTestCase
{
    public function testRichTextPreview()
    {
        $sourceText = '21. November - First line' . "\n" .
            '22. November - Second line' . "\n" .
            '23. November - Third line';

        $expectedText = '21. November - First line 22. November - Second line 23. November - Third line';

        $this->assertEquals($expectedText, RichText::preview($sourceText));
    }
}