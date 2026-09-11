<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\form\ActiveForm;
use humhub\widgets\VueFormShell;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\InvalidConfigException;

class VueFormShellTest extends HumHubDbTestCase
{
    public function testRendersCustomFieldsFromTheContentClosure()
    {
        $html = VueFormShell::widget([
            'content' => fn(ActiveForm $form) => '<div class="my-custom-field">hi</div>',
        ]);

        $this->assertStringContainsString('<div class="my-custom-field">hi</div>', $html);
    }

    public function testContentClosureReceivesAUsableActiveFormInstance()
    {
        $received = null;

        VueFormShell::widget([
            'content' => function (ActiveForm $form) use (&$received) {
                $received = $form;
                return '';
            },
        ]);

        $this->assertInstanceOf(ActiveForm::class, $received);
    }

    public function testEveryIdForAndHashSelectorAttributeContainsTheToken()
    {
        $html = VueFormShell::widget([
            'content' => fn(ActiveForm $form) => '<label for="' . VueFormShell::id('input') . '">Label</label>'
                . '<input id="' . VueFormShell::id('input') . '">'
                . '<div data-drop-zone="#' . VueFormShell::id('dropzone') . '"></div>',
        ]);

        $this->assertGreaterThan(0, preg_match_all('/\b(?:id|for)="([^"]*)"/', $html, $idMatches));
        foreach ($idMatches[1] as $value) {
            $this->assertStringContainsString(VueFormShell::TOKEN, $value);
        }

        $this->assertGreaterThan(0, preg_match_all('/\bdata-[a-z-]+="(#[^"]*)"/', $html, $hashMatches));
        foreach ($hashMatches[1] as $value) {
            $this->assertStringContainsString(VueFormShell::TOKEN, $value);
        }
    }

    public function testIdHelperBuildsATokenPrefixedSuffix()
    {
        $this->assertSame('__VUEFORM___title', VueFormShell::id('title'));
    }

    public function testFormIdItselfCarriesTheToken()
    {
        $html = VueFormShell::widget(['content' => fn(ActiveForm $form) => '']);

        $this->assertMatchesRegularExpression('/<form[^>]*\bid="[^"]*' . preg_quote(VueFormShell::TOKEN, '/') . '[^"]*"/', $html);
    }

    public function testHasNoCsrfInputByDefault()
    {
        $html = VueFormShell::widget(['content' => fn(ActiveForm $form) => '']);

        $this->assertStringNotContainsString('_csrf', $html);
    }

    public function testAcknowledgeAttributeIsPresentByDefault()
    {
        $html = VueFormShell::widget(['content' => fn(ActiveForm $form) => '']);

        $this->assertStringContainsString('data-ui-addition="acknowledgeForm"', $html);
    }

    public function testActionIsAStaticHash()
    {
        $html = VueFormShell::widget(['content' => fn(ActiveForm $form) => '']);

        $this->assertMatchesRegularExpression('/<form[^>]*\baction="#"/', $html);
    }

    public function testFormOptionsAreMergedOverTheDefaultConventions()
    {
        $html = VueFormShell::widget([
            'content' => fn(ActiveForm $form) => '',
            'formOptions' => ['options' => ['class' => 'my-extra-class']],
        ]);

        // The extra class is present alongside the convention id (i.e. a deep merge, not an
        // overwrite of the whole `options` sub-array).
        $this->assertStringContainsString('class="my-extra-class"', $html);
        $this->assertStringContainsString(VueFormShell::TOKEN, $html);
    }

    public function testFormOptionsCanOverrideAConvention()
    {
        $html = VueFormShell::widget([
            'content' => fn(ActiveForm $form) => '',
            'formOptions' => ['acknowledge' => false],
        ]);

        $this->assertStringNotContainsString('data-ui-addition="acknowledgeForm"', $html);
    }

    public function testMissingContentThrows()
    {
        $this->expectException(InvalidConfigException::class);

        VueFormShell::widget([]);
    }

    public function testNonClosureContentThrows()
    {
        $this->expectException(InvalidConfigException::class);

        VueFormShell::widget(['content' => 'not-a-closure']);
    }

    /**
     * `run()` wraps `ob_start()` plus `ActiveForm::begin()`/`::end()` in try/finally
     * specifically so a throwing `content` closure can't leak an open output-buffer level or
     * an unbalanced `ActiveForm` widget stack entry into the rest of the request/test run -
     * both would otherwise silently corrupt whatever renders next.
     */
    public function testThrowingContentClosurePropagatesWithoutLeakingTheOutputBuffer()
    {
        $obLevelBefore = ob_get_level();

        try {
            VueFormShell::widget([
                'content' => function (ActiveForm $form) {
                    throw new \RuntimeException('boom');
                },
            ]);
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }

        $this->assertSame($obLevelBefore, ob_get_level());
    }

    /**
     * Pins the contract: a caller-supplied `formOptions.options` (e.g. an extra CSS class)
     * merges over the widget's own conventions rather than replacing the whole `options`
     * sub-array - the token-bearing `id` and the disabled `csrf` flag must both survive
     * alongside it (see `testFormOptionsAreMergedOverTheDefaultConventions` /
     * `testHasNoCsrfInputByDefault` above for each half individually).
     */
    public function testFormOptionsMergeKeepsTheTokenAndOmitsCsrf()
    {
        $html = VueFormShell::widget([
            'content' => fn(ActiveForm $form) => '',
            'formOptions' => ['options' => ['class' => 'x']],
        ]);

        $this->assertMatchesRegularExpression(
            '/<form[^>]*\bid="[^"]*' . preg_quote(VueFormShell::TOKEN, '/') . '[^"]*"/',
            $html,
        );
        $this->assertStringNotContainsString('_csrf', $html);
    }
}
