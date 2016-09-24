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

    const EVENT_BEFORE_VALIDATE = 'beforeValidate';
    const EVENT_AFTER_VALIDATE = 'afterValidate';

    public $showErrorSummary;
    protected $form;
    public $primaryModel = null;
    public $models = array();
    public $definition = array();

    /**
     * @var boolean manually mark form as submitted
     */
    public $markedAsSubmitted = false;

    public function __construct($definition = [], $primaryModel = null)
    {
        $this->definition = $definition;
        $this->primaryModel = $primaryModel;

        $this->init();
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
        } elseif ($this->markedAsSubmitted) {
            return true;
        }

        return false;
    }

    public function validate()
    {
        $hasErrors = false;
        $this->trigger(self::EVENT_BEFORE_VALIDATE);

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

        $this->trigger(self::EVENT_AFTER_VALIDATE);
        return !$hasErrors;
    }

    public function clearErrors()
    {
        if ($this->primaryModel !== null) {
            $this->primaryModel->clearErrors();
        }

        foreach ($this->models as $model) {
            $model->clearErrors();
        }
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
                $output .= \yii\helpers\Html::submitButton($definition['label'], ['name' => $buttonName, 'class' => $definition['class'], 'data-ui-loader' => '']);
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

            if (isset($definition['id'])) {
                $options['id'] = $definition['id'];
            }

            if (isset($definition['readonly']) && $definition['readonly']) {
                $options['readOnly'] = true;
                $options['disabled'] = true;
            }

            if (isset($definition['value'])) {
                $options['value'] = $definition['value'];
            }

            if (isset($definition['prompt']) && $definition['prompt']) {
                $options['prompt'] = $definition['prompt'];
            }
            if (isset($definition['label']) && $definition['label']) {
                $options['label'] = $definition['label'];
            }
            if (isset($definition['type'])) {
                switch ($definition['type']) {
                    case 'text':
                        return $this->form->field($model, $name)->textInput($options);
                    case 'multiselectdropdown':
                        $options['class'] = 'form-control multiselect_dropdown';
                        $options['multiple'] = 'multiple';
                        return $this->form->field($model, $name)->listBox($definition['items'], $options);
                    case 'dropdownlist':
                        return $this->form->field($model, $name)->dropDownList($definition['items'], $options);
                    case 'checkbox':
                        if (isset($options['readOnly']) && $options['readOnly']) {
                            $options['disabled'] = 'disabled';
                        }
                        return $this->form->field($model, $name)->checkbox($options);
                    case 'textarea':
                        return $this->form->field($model, $name)->textarea($options);
                    case 'hidden':
                        return $this->form->field($model, $name)->hiddenInput($options)->label(false);
                    case 'password':
                        return $this->form->field($model, $name)->passwordInput($options);
                    case 'datetime':
                        $format = Yii::$app->formatter->dateFormat;
                        if (isset($definition['format'])) {
                            $format = $definition['format'];
                        }

                        $yearRange = isset($definition['yearRange']) ? $definition['yearRange'] : (date('Y') - 100) . ":" . (date('Y') + 100);

                        return $this->form->field($model, $name)->widget(\yii\jui\DatePicker::className(), [
                                    'dateFormat' => $format,
                                    'clientOptions' => ['changeYear' => true, 'yearRange' => $yearRange, 'changeMonth' => true, 'disabled' => (isset($options['readOnly']) && $options['readOnly'])],
                                    'options' => ['class' => 'form-control']]);
                    case 'markdown':
                        $options['id'] = $name;
                        $returnField = $this->form->field($model, $name)->textarea($options);
                        $returnField .= \humhub\widgets\MarkdownEditor::widget(array('fieldId' => $name));
                        return $returnField;
                    default:
                        return "Field Type " . $definition['type'] . " not supported by Compat HForm";
                }
            } else {
                return "No type found for: FieldName: " . $name . " Forms: " . print_r($forms, 1) . "<br>";
            }
        } else {
            return "No model for: FieldName: " . $name . " Forms: " . print_r($forms, 1) . "<br>";
        }

        return $output;
    }

}
