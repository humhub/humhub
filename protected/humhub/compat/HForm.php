<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\compat;

use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\file\components\FileManager;
use humhub\modules\ui\form\widgets\DatePicker;
use humhub\modules\ui\form\widgets\MultiSelect;
use humhub\modules\ui\form\widgets\SortOrderField;
use Yii;
use yii\helpers\Html;
use yii\widgets\ActiveField;
use yii\widgets\ActiveForm;

/**
 * HForm - Yii1 compatible form generator
 *
 * @author luke
 */
class HForm extends \yii\base\Component
{
    const EVENT_BEFORE_VALIDATE = 'beforeValidate';
    const EVENT_AFTER_VALIDATE = 'afterValidate';

    /**
     * @since 1.2.6
     */
    const EVENT_AFTER_INIT = 'afterInit';

    /**
     * @since 1.2.6
     */
    const EVENT_BEFORE_RENDER = 'beforeRender';

    public $showErrorSummary;


    /**
     * @var ActiveForm
     */
    protected $form;

    public $primaryModel = null;
    public $models = [];
    public $definition = [];

    /**
     * @var boolean manually mark form as submitted
     */
    public $markedAsSubmitted = false;

    public function __construct($definition = [], $primaryModel = null)
    {
        $this->definition = $definition;
        $this->primaryModel = $primaryModel;

        $this->init();
        $this->trigger(static::EVENT_AFTER_INIT);
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

        (new FileManager(['record' => $this->primaryModel]))->attach(Yii::$app->request->post('fileList'));

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

        $this->trigger(static::EVENT_BEFORE_RENDER);

        $out = $this->renderElements($this->definition['elements']);
        $out .= $this->renderButtons($this->definition['buttons']);

        return $out;
    }

    public function renderElements($elements, $forms = [])
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
            $definition['isVisible'] = isset($definition['isVisible']) ? $definition['isVisible'] : true;
            if ($definition['type'] == 'submit' && $definition['isVisible']) {
                $output .= \yii\helpers\Html::submitButton($definition['label'], ['name' => $buttonName, 'class' => $definition['class'], 'data-ui-loader' => '']);
                $output .= "&nbsp;";
            }
        }

        return $output;
    }

    public function renderField($name, $definition, $forms)
    {
        if (isset($definition['isVisible']) && !$definition['isVisible']) {
            return;
        }

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

            if (isset($definition['htmlOptions']) && is_array($definition['htmlOptions'])) {
                $options = array_merge($options, $definition['htmlOptions']);
            }

            if (isset($definition['type'])) {
                switch ($definition['type']) {
                    case 'text':
                        $field = $this->form->field($model, $name)->textInput($options);
                        break;
                    case 'multiselectdropdown':
                        $field = $this->form->field($model, $name)->widget(MultiSelect::class, [
                            'items' => $definition['items'],
                            'options' => $definition['options'],
                            'maxSelection' => $definition['maxSelection'] ?? 50,
                        ]);
                        break;
                    case 'dropdownlist':
                        $field = $this->form->field($model, $name)->dropDownList($definition['items'], $options);
                        break;
                    case 'checkbox':
                        if (isset($options['readOnly']) && $options['readOnly']) {
                            $options['disabled'] = 'disabled';
                        }
                        $field = $this->form->field($model, $name)->checkbox($options);
                        break;
                    case 'checkboxlist':
                        if (isset($options['readOnly']) && $options['readOnly']) {
                            $options['disabled'] = 'disabled';
                        }

                        $value = $model->$name;

                        if (is_string($value)) {
                            $delimiter = isset($definition['delimiter']) ? $definition['delimiter'] : ',';
                            $model->$name = explode($delimiter, $model->$name);
                        }

                        $field = $this->form->field($model, $name)->checkboxList($definition['items'], $options);
                        break;
                    case 'textarea':
                        if (isset($definition['class'])) {
                            $options['class'] = $definition['class'];
                        }

                        $field = $this->form->field($model, $name)->textarea($options);
                        break;
                    case 'hidden':
                        $field = $this->form->field($model, $name)->hiddenInput($options);
                        $definition['label'] = false;
                        break;
                    case 'password':
                        $field = $this->form->field($model, $name)->passwordInput($options);
                        break;
                    case 'datetime':
                        $format = Yii::$app->formatter->dateFormat;
                        if (isset($definition['format'])) {
                            $format = $definition['format'];
                        }

                        $yearRange = isset($definition['yearRange']) ? $definition['yearRange'] : (date('Y') - 100) . ":" . (date('Y') + 100);

                        $field = $this->form->field($model, $name)->widget(DatePicker::class, [
                            'dateFormat' => $format,
                            'clientOptions' => [
                                'changeYear' => true,
                                'yearRange' => $yearRange,
                                'changeMonth' => true,
                                'disabled' => (isset($options['readOnly']) && $options['readOnly'])
                            ],
                            'options' => [
                                'class' => 'form-control']
                        ]);
                        break;
                    case 'markdown':
                        $options['id'] = $name;
                        if (isset($options['readOnly']) && $options['readOnly']) {
                            // TODO: Once the richtext supports readonly view remove this line
                            return RichText::output(Html::getAttributeValue($model, $name));
                        }

                        $field = $this->form->field($model, $name)->widget(RichTextField::class, $options);
                        break;
                    case 'sortOrder':
                        $field = $this->form->field($model, $name)->widget(SortOrderField::class, $options);
                        break;
                    default:
                        if (method_exists($definition['type'], 'widget')) {
                            $field = $this->form->field($model, $name)->widget($definition['type'], $options);
                            break;
                        }

                        return "Field Type " . $definition['type'] . " not supported by Compat HForm";
                }

                if (isset($definition['label'])) {
                    $field->label($definition['label']);
                }

                if (!empty($definition['hint']) && $field instanceof ActiveField) {
                    $field->hint(Html::encode($definition['hint'], false));
                }

                return $field;
            } else {
                return "No type found for: FieldName: " . $name . " Forms: " . print_r($forms, 1) . "<br>";
            }
        } else {
            return "No model for: FieldName: " . $name . " Forms: " . print_r($forms, 1) . "<br>";
        }

        return $output;
    }
}
