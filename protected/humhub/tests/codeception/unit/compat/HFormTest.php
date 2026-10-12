<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2018-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace humhub\tests\codeception\unit\compat;

use Codeception\Test\Unit;
use humhub\compat\HForm;

class HFormTest extends Unit
{
    public function testFieldsetLegendIsEncoded()
    {
        $form = new HForm();

        $output = $form->renderForm(['title' => '<script>alert(1)</script>']);

        $this->assertStringContainsString('<legend>&lt;script&gt;alert(1)&lt;/script&gt;</legend>', $output);
        $this->assertStringNotContainsString('<script>', $output);
    }

    public function testFieldsetClassIsEncoded()
    {
        $form = new HForm();

        $output = $form->renderForm(['class' => 'my-form" onmouseover="alert(1)']);

        $this->assertStringContainsString('class="my-form&quot; onmouseover=&quot;alert(1)"', $output);
        $this->assertStringNotContainsString('onmouseover="alert(1)"', $output);
    }

    public function testFieldsetWithoutTitle()
    {
        $form = new HForm();

        $this->assertSame('<fieldset class="">', $form->renderForm([]));
        $this->assertSame('</fieldset>', $form->renderFormEnd([]));
    }
}
