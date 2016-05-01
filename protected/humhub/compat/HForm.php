<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\compat;

use Yii;

/**
 * HForm - Yii1 compatible form generator
 *
 * @author luke
 */
class HForm extends \yii\base\Component
{

    public $showErrorSummary;
    protected $form;
    public $primaryModel = null;
    public $models = array();
    public $definition = array();

    public function __construct($definition, $primaryModel = null)
    {
        $this->definition = $definition;
        $this->primaryModel = $primaryModel;
    }

    public function submitted($buttonName = "")
    {
        if (Yii::$app->request->method == 'POST') {

            if ($buttonName == "" || isset($_POST[$buttonName])) {
                foreach ($this->models as $model) {
                    $model->load(Yii::$app->request->post());
                }
                if ($this->primaryModel !== null) {
                    $this->primaryModel->load(Yii::$app->request->post());
                }
                return true;
            }
        }

        return false;
    }

    public function validate()
    {
        $hasErrors = false;

        if ($this->primaryModel !== null) {
            if (!$this->primaryModel->validate()) {
                $hasErrors = true;
            }
        }

        foreach ($this->models as $model) {
            if (!$model->validate()) {
                $hasErrors = true;
            }
        }
        return !$hasErrors;
    }

    public function save()
    {
        $hasErrors = false;
        if ($this->primaryModel !== null) {
            if (!$this->primaryModel->save()) {
                $hasErrors = true;
            }

            $this->primaryModel->save();
        }

        foreach ($this->models as $model) {
            if (!$model->save()) {
                $hasErrors = true;
            }
        }

        return !$hasErrors;
    }

    public function render($form)
    {
        $this->form = $form;

        $out = $this->renderElements($this->definition['elements']);
        $out .= $this->renderButtons($this->definition['buttons']);

        return $out;
    }

    public function renderElements($elements, $forms = array())
    {
        $output = "";
        foreach ($elements as $name => $element) {
            if (isset($element['type']) && $element['type'] == 'form') {
                $forms[] = $name;
                if (isset($element['elements']) && count($element['elements']) > 0) {
                    $output .= $this->renderForm($element);
                    $output .= $this->renderElements($element['elements'], $forms);
                    $output .= $this->renderFormEnd($element);
                }
            } else {
                $output .= $this->renderField($name, $element, $forms);
            }
        }
        return $output;
    }

    public function renderForm($element)
    {

        $class = "";
        if (isset($element['class'])) {
            $class = $element['class'];
        }

        $output = "<fieldset class='" . $class . "'>";
        if (isset($element['title'])) {
            #$output .= "<h2>" . $element['title'] . "</h2>";
            $output .= "<legend>" . $element['title'] . "</legend>";
        } else {
            #$output .= "Untitled Form";
        }
        return $output;
    }

    public function renderFormEnd($element)
    {
        return "</fieldset>";
    }

    public function renderButtons($buttons)
    {
        $output = "";
        foreach ($buttons as $buttonName => $definition) {
            if ($definition['type'] == 'submit') {
                $output .= \yii\helpers\Html::submitButton($definition['label'], ['name' => $buttonName, 'class' => $definition['class']]);
                $output .= "&nbsp;";
            }
        }
        return $output;
    }

    public function renderField($name, $definition, $forms)
    {
        $output = "";

        // Determine Model
        $model = null;
        foreach ($forms as $formName) {
            if (isset($this->models[$formName])) {
                $model = $this->models[$formName];
            }
        }
        if ($model == null && $this->primaryModel !== null) {
            $model = $this->primaryModel;
        }

        if ($model) {
            $options = [];

            if (isset($definition['readonly']) && $definition['readonly']) {
                $options['readOnly'] = true;
                $options['disabled'] = true;
            }
            if (isset($definition['prompt']) && $definition['prompt']) {
                $options['prompt'] = $definition['prompt'];
            }
            if (isset($definition['label']) && $definition['label']) {
                $options['label'] = $definition['label'];
            }
            if (isset($definition['type'])) {
                if ($definition['type'] == 'text') {
                    $output .= $this->form->field($model, $name)->textInput($options);
                } elseif ($definition['type'] == 'dropdownlist') {
                    $output .= $this->form->field($model, $name)->dropDownList($definition['items'], $options);
                } elseif ($definition['type'] == 'checkbox') {
                    if (isset($options['readOnly']) && $options['readOnly']) {
                        $options['disabled'] = 'disabled';
                    }
                    $output .= $this->form->field($model, $name)->checkbox($options);
                } elseif ($definition['type'] == 'textarea') {
                    $output .= $this->form->field($model, $name)->textarea($options);
                } elseif ($definition['type'] == 'hidden') {
                    $output .= $this->form->field($model, $name)->hiddenInput($options)->label(false);
                } elseif ($definition['type'] == 'password') {
                    $output .= $this->form->field($model, $name)->passwordInput($options);
                } elseif ($definition['type'] == 'datetime') {
                    $format = Yii::$app->formatter->dateFormat;
                    if (isset($definition['format'])) {
                        $format = $definition['format'];
                    }
                    $output .= $this->form->field($model, $name)->widget(\yii\jui\DatePicker::className(), ['dateFormat' => $format, 'clientOptions' => ['changeYear' => true, 'yearRange' => (date('Y') - 100) . ":" . date('Y'), 'changeMonth' => true, 'disabled' => (isset($options['readOnly']) && $options['readOnly'])], 'options' => ['class' => 'form-control']]);
                } else {
                    $output .= "Field Type " . $definition['type'] . " not supported by Compat HForm";
                }
            }
        } else {
            $output .= "No model for: FieldName: " . $name . " Type:" . $definition['type'] . " Forms: " . print_r($forms, 1) . "<br>";
        }

        return $output;
    }

}
