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
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\form\widgets\DatePicker;
use humhub\modules\ui\form\widgets\MultiSelect;
use humhub\modules\ui\form\widgets\SortOrderField;
use Yii;
use yii\helpers\Html;
use yii\widgets\ActiveField;

/**
 * HForm - Yii1 compatible form generator
 *
 * @author luke
 */
class HForm extends \yii\base\Component
{
    public const EVENT_BEFORE_VALIDATE = 'beforeValidate';
    public const EVENT_AFTER_VALIDATE = 'afterValidate';

    /**
     * @since 1.2.6
     */
    public const EVENT_AFTER_INIT = 'afterInit';

    /**
     * @since 1.2.6
     */
    public const EVENT_BEFORE_RENDER = 'beforeRender';

    public $showErrorSummary;


    /**
     * @var ActiveForm
     */
    protected $form;

    public $primaryModel = null;
    public $models = [];
    public $definition = [];

    /**
     * @var bool manually mark form as submitted
     */
    public $markedAsSubmitted = false;

    public function __construct($definition = [], $primaryModel = null, array $config = [])
    {
        $this->definition = $definition;
        $this->primaryModel = $primaryModel;

        if (!empty($config)) {
            Yii::configure($this, $config);
        }

        $this->init();
        $this->trigger(static::EVENT_AFTER_INIT);
    }

    public function submitted($buttonName = "")
    {
        if (Yii::$app->request->method == 'POST') {
            if ($buttonName == "" || isset($_POST[$buttonName])) {
                $allowedPostData = $this->getAllowedPostData();
                foreach ($this->models as $model) {
                    $model->load($allowedPostData);
                }
                if ($this->primaryModel !== null) {
                    $this->primaryModel->load($allowedPostData);
                }
                return true;
            }
        } elseif ($this->markedAsSubmitted) {
            return true;
        }

        return false;
    }

    protected function getAllowedPostData(): array
    {
        $post = Yii::$app->request->post();

        foreach ($this->models as $modelName => $model) {
            $className = substr(strrchr(get_class($model), '\\'), 1);
            if (!isset($post[$className])) {
                continue;
            }
            if (!isset($this->definition['elements'][$modelName])) {
                // Remove post data of the object if no definition
                unset($post[$className]);
            }
            if (isset($this->definition['elements'][$modelName]['elements'])) {
                foreach ($this->definition['elements'][$modelName]['elements'] as $elementName => $element) {
                    if (!empty($element['readonly']) && isset($post[$className][$elementName])) {
                        // Remove a readonly field from the POST data
                        unset($post[$className][$elementName]);
                    }
                }
            }
        }

        return $post;
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
            $isVisible = $definition['isVisible'] ?? true;
            if ($definition['type'] == 'submit' && $isVisible) {
                $output .= \yii\helpers\Html::submitButton($definition['label'], array_merge(['name' => $buttonName, 'class' => $definition['class'], 'data-ui-loader' => ''], $definition['options'] ?? []));
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
            $options = $this->getOptionsFromDefinition($definition);

            if (isset($model->$name, $options['value'])) {
                unset($options['value']);
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
                            $options['itemOptions']['disabled'] = 'disabled';
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
                                'disabled' => (isset($options['readOnly']) && $options['readOnly']),
                            ],
                            'options' => [
                                'class' => 'form-control'],
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

                if (isset($definition['label']) && $definition['type'] !== 'checkbox') {
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

    /**
     * Translates definition array into options.
     *
     * @param array $definition Input field definition.
     *
     * @return array The associated array of options.
     */
    private function getOptionsFromDefinition($definition)
    {
        $options = [];

        foreach (['id', 'value', 'prompt', 'label', 'rows', 'cols'] as $name) {
            if (isset($definition[$name])) {
                $options[$name] = $definition[$name];
            }
        }

        if (isset($definition['readonly']) && $definition['readonly']) {
            $options['readOnly'] = true;
            $options['disabled'] = true;
        }

        if (isset($definition['htmlOptions']) && is_array($definition['htmlOptions'])) {
            $options = array_merge($options, $definition['htmlOptions']);
        }

        return $options;
    }
}
