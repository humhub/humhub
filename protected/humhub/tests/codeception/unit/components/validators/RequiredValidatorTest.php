<?php

namespace humhub\tests\codeception\unit\components\validators;

use tests\codeception\_support\HumHubDbTestCase;
use yii\base\DynamicModel;

class RequiredValidatorTest extends HumHubDbTestCase
{
    public function testWhitespaceOnlyIsEmpty()
    {
        $model = new DynamicModel();
        $model->addRule('attr', 'required');


        $model->setAttributes([
            'attr' => "  ",
        ]);
        $this->assertFalse($model->validate());

        $model->setAttributes([
            'attr' => "   ",
        ]);
        $this->assertFalse($model->validate());

        $model->setAttributes([
            'attr' => "\n\t ",
        ]);
        $this->assertFalse($model->validate());

        $model->setAttributes([
            'attr' => "  \u{00A0} ",
        ]);
        $this->assertFalse($model->validate());
    }

    public function testNonWhitespaceIsNotEmpty()
    {
        $model = new DynamicModel();
        $model->addRule('attr', 'required');


        $model->setAttributes([
            'attr' => "a",
        ]);
        $this->assertTrue($model->validate());

        $model->setAttributes([
            'attr' => "  x  ",
        ]);
        $this->assertTrue($model->validate());

        $model->setAttributes([
            'attr' => "абв",
        ]);
        $this->assertTrue($model->validate());

        $model->setAttributes([
            'attr' => "🙂",
        ]);
        $this->assertTrue($model->validate());
    }
}
