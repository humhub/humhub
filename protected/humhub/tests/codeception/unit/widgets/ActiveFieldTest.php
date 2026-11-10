<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\form\ActiveForm;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\DynamicModel;
use yii\base\Model;

/**
 * An input is described by its hint and its error message (WCAG 1.3.1, 3.3.1), so a screen reader reads them with the
 * field
 */
class ActiveFieldTest extends HumHubDbTestCase
{
    public function testTheErrorMessageDescribesTheInput()
    {
        $model = $this->model();
        $model->addError('email', 'The address is not valid.');

        $html = $this->render(fn(ActiveForm $form) => $form->field($model, 'email')->textInput());

        $this->assertMatchesRegularExpression('/<input[^>]*id="dynamicmodel-email"[^>]*aria-invalid="true"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*aria-describedby="dynamicmodel-email-error"/', $html);
        $this->assertMatchesRegularExpression('/<div[^>]*id="dynamicmodel-email-error"[^>]*>The address is not valid.<\/div>/', $html);
    }

    /**
     * The message the client validation adds later is read too
     */
    public function testAnInputWithoutAnErrorIsDescribedByTheEmptyMessage()
    {
        $html = $this->render(fn(ActiveForm $form) => $form->field($this->model(), 'email')->textInput());

        $this->assertMatchesRegularExpression('/<input[^>]*aria-describedby="dynamicmodel-email-error"/', $html);
        $this->assertMatchesRegularExpression('/<div[^>]*id="dynamicmodel-email-error"[^>]*><\/div>/', $html);
    }

    public function testAnInputOfItsOwnIdKeepsItsDescription()
    {
        $html = $this->render(fn(ActiveForm $form) => $form->field($this->model(), 'email')
            ->textInput(['id' => 'login_email', 'aria-describedby' => 'login_email-note']));

        $this->assertMatchesRegularExpression('/<input[^>]*aria-describedby="login_email-note login_email-error"/', $html);
        $this->assertStringContainsString('id="login_email-error"', $html);
    }

    public function testAFieldWithoutAnErrorMessageIsNotDescribedByOne()
    {
        $html = $this->render(fn(ActiveForm $form) => $form->field($this->model(), 'email', ['enableError' => false])->textInput());

        $this->assertStringNotContainsString('aria-describedby', $html);
    }

    /**
     * The hint describes the input too, before the error message, as in the Vue form fields
     */
    public function testTheHintOfTheModelDescribesTheInput()
    {
        $model = new class extends Model {
            public $email = '';

            public function formName()
            {
                return 'Test';
            }

            public function attributeHints()
            {
                return ['email' => 'We never share it.'];
            }
        };

        $html = $this->render(fn(ActiveForm $form) => $form->field($model, 'email')->textInput());

        $this->assertMatchesRegularExpression('/<input[^>]*aria-describedby="test-email-hint test-email-error"/', $html);
        $this->assertMatchesRegularExpression('/<div[^>]*id="test-email-hint"[^>]*>We never share it.<\/div>/', $html);
    }

    public function testAHintSetAfterTheInputDescribesIt()
    {
        $html = $this->render(fn(ActiveForm $form) => $form->field($this->model(), 'email')
            ->textInput(['id' => 'login_email'])
            ->hint('We never share it.'));

        $this->assertMatchesRegularExpression('/<input[^>]*aria-describedby="login_email-hint login_email-error"/', $html);
        $this->assertMatchesRegularExpression('/<div[^>]*id="login_email-hint"[^>]*>We never share it.<\/div>/', $html);
    }

    public function testAFieldWithoutAHintIsNotDescribedByOne()
    {
        $withoutHint = $this->render(fn(ActiveForm $form) => $form->field($this->model(), 'email')->textInput());
        $hintTurnedOff = $this->render(fn(ActiveForm $form) => $form->field($this->model(), 'email')
            ->hint('We never share it.')
            ->textInput()
            ->hint(false));

        foreach ([$withoutHint, $hintTurnedOff] as $html) {
            $this->assertStringNotContainsString('-hint', $html);
            $this->assertMatchesRegularExpression('/<input[^>]*aria-describedby="dynamicmodel-email-error"/', $html);
        }
    }

    public function testTheHintOfACheckboxDescribesIt()
    {
        $model = (new DynamicModel(['accept' => false]))->addRule('accept', 'boolean');

        $html = $this->render(fn(ActiveForm $form) => $form->field($model, 'accept')->checkbox()->hint('Optional.'));

        $this->assertMatchesRegularExpression('/<input type="checkbox"[^>]*aria-describedby="dynamicmodel-accept-hint dynamicmodel-accept-error"/', $html);
        $this->assertStringContainsString('id="dynamicmodel-accept-hint"', $html);
    }

    private function model(): DynamicModel
    {
        return (new DynamicModel(['email' => '']))->addRule('email', 'email');
    }

    /**
     * The field of `$field`, rendered inside a form whose own output is dropped
     */
    private function render(callable $field): string
    {
        ob_start();
        $form = ActiveForm::begin(['id' => 'test-form', 'action' => '/test']);
        $html = (string) $field($form);
        ActiveForm::end();
        ob_end_clean();

        return $html;
    }
}
