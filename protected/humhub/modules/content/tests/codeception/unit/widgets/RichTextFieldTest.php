<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\modules\content\widgets\richtext\RichTextField;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\DynamicModel;

class RichTextFieldTest extends HumHubDbTestCase
{
    public function testHiddenInputIsNamedByThePlaceholder()
    {
        $html = RichTextField::widget(['name' => 'message', 'placeholder' => 'Write a message']);

        // Accessibility checkers (WAVE) inspect hidden inputs too, and warn of one named only by its title
        $this->assertMatchesRegularExpression('/<textarea[^>]* aria-label="Write a message"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<textarea[^>]* title=/', $html);
    }

    public function testHiddenInputWithoutPlaceholderIsNamedByTheFieldLabel()
    {
        $html = RichTextField::widget(['model' => new DynamicModel(['about' => '']), 'attribute' => 'about']);

        $this->assertMatchesRegularExpression('/<textarea[^>]* aria-label="About"/', $html);
    }
}
