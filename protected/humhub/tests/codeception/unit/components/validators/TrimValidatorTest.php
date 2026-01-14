<?php

namespace humhub\tests\codeception\unit\components\validators;

use tests\codeception\_support\HumHubDbTestCase;
use yii\base\DynamicModel;

class TrimValidatorTest extends HumHubDbTestCase
{
    public function testWhitespaceSuffixPrefix()
    {
        $model = new DynamicModel();
        $model->addRule('attr', 'trim');


        $model->setAttributes([
            'attr' => " test ",
        ]);
        $model->validate();
        $this->assertEquals('test', $model->attr);

        $model->setAttributes([
            'attr' => " абв ",
        ]);
        $model->validate();
        $this->assertEquals('абв', $model->attr);

        $model->setAttributes([
            'attr' => " 🙂 ",
        ]);
        $model->validate();
        $this->assertEquals('🙂', $model->attr);

        $model->setAttributes([
            'attr' => "  test ",
        ]);
        $model->validate();
        $this->assertEquals('test', $model->attr);

        $model->setAttributes([
            'attr' => "\n\ttest\n\t",
        ]);
        $model->validate();
        $this->assertEquals('test', $model->attr);

        $model->setAttributes([
            'attr' => " \u{00A0} test \u{00A0} ",
        ]);
        $model->validate();
        $this->assertEquals('test', $model->attr);
    }
}
