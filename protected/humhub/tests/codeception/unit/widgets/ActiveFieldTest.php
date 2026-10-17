<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\form\ActiveForm;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\DynamicModel;

class ActiveFieldTest extends HumHubDbTestCase
{
    public function testToggleRendersASwitch()
    {
        $html = $this->renderField(fn(ActiveForm $form, DynamicModel $model) => $form->field($model, 'notify')
            ->toggle()
            ->hint('Receive a notification'));

        $this->assertStringContainsString('class="form-check form-switch"', $html);
        $this->assertMatchesRegularExpression('/<input type="checkbox"[^>]*role="switch"/', $html);
        $this->assertStringContainsString('Receive a notification', $html);
    }

    public function testCheckboxStaysAPlainCheckbox()
    {
        $html = $this->renderField(fn(ActiveForm $form, DynamicModel $model) => $form->field($model, 'notify')
            ->checkbox());

        $this->assertStringContainsString('class="form-check"', $html);
        $this->assertStringNotContainsString('form-switch', $html);
        $this->assertStringNotContainsString('role="switch"', $html);
    }

    private function renderField(callable $field): string
    {
        $model = new DynamicModel(['notify' => false]);
        $model->addRule('notify', 'boolean');

        ob_start();
        $form = ActiveForm::begin(['action' => '#']);
        echo $field($form, $model);
        ActiveForm::end();

        return ob_get_clean();
    }
}
