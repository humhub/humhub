<?php

namespace humhub\widgets;

use Yii;
use yii\helpers\Html;
use \yii\helpers\Url;
use humhub\widgets\RichText;

/**
 * @package humhub.modules_core.user.widgets
 * @since 1.2
 * @author buddha
 */
class RichtextField extends JsWidget
{

    /**
     * Defines the javascript picker implementation.
     * 
     * @var string 
     */
    public $jsWidget = 'ui.richtext.Richtext';

    /**
     * Minimum character input before triggering search query.
     *
     * @var integer
     */
    public $minInput = 3;

    /**
     * If the ActiveForm is set, it will be used to create the picker field,
     * otherwise it's created by Html::activeDropDownList
     *
     * @var \yii\widgets\ActiveForm
     */
    public $form;

    /**
     * Model instance. Requires the setting of an model $attribute.
     * 
     * @var \yii\db\ActiveRecord
     */
    public $model;

    /**
     * Model attribute which holds the picker value. The referenced model attribute has to be an
     * array.
     * 
     * @var string 
     */
    public $attribute;

    /**
     * Input form name.
     * This can be provided if no form and model is provided for custom input field setting.
     * 
     * @var type 
     */
    public $name;

    /**
     * Can be used to overwrite the default placeholder.
     * 
     * @var string
     */
    public $placeholder;
    
    /**
     * The url used for the default @ metioning.
     * If there is no $searchUrl is given, the $searchRoute will be used instead.
     * 
     * @var string 
     */
    public $mentioningUrl;
    
    /**
     * Route used for the default @ mentioning. This will only be used if
     * not $searchUrl is given.
     * 
     * @var string 
     */
    protected $mentioningRoute = "/search/search/mentioning";
    
    /**
     * Richtext features supported for within this feature.
     * By default all features will be included.
     * 
     * @var array 
     */
    public $includes = [];
    
    /**
     * Richtext features not supported in this richtext feature.
     * 
     * @var array 
     */
    public $excludes = [];
    
    /**
     * Can be used to set the value in case no $model and $attribute is provided.
     * $model and $attribute is provided.
     * 
     * @var string 
     */
    public $value;
    
    /**
     * If set to true the picker will be focused automatically.
     * 
     * @var boolean 
     */
    public $focus = false;
    
    /**
     * Disables the input field.
     * @var boolean 
     */
    public $disabled = false;
    
    /**
     * Will be used as userfeedback, why this richtext is disabled.
     * 
     * @var string 
     */
    public $disabledText = false;

    /**
     * @inheritdoc
     */
    public $init = true;
    
    /**
     * @inheritdoc
     */
    public $visible = true;
    
    /**
     * @var boolean defines if the default label should be rendered. This is only available if $form is given.
     */
    public $label = false;

    /**
     * @inhertidoc
     */
    public function run()
    {
        $inputOptions = $this->getAttributes();
        $inputOptions['id'] = $this->getId(true).'_input';
        $inputOptions['style'] = 'display:none;color';
        unset($inputOptions['contenteditable']);
        $modelAttribute = $this->attribute;
        
        if ($this->form != null) {
            $input = $this->form->field($this->model, $this->attribute)->textarea($inputOptions)->label(false);
            $richText = Html::tag('div', RichText::widget(['text' => $this->model->$modelAttribute, 'edit' => true]), $this->getOptions());
            $richText = $this->form->label($this->model, $this->attribute, ['class' => 'control-label']).$richText;
        } else if ($this->model != null) {
            $input = Html::activeTextarea($this->model, $this->attribute, $inputOptions);
            $richText = Html::tag('div', RichText::widget(['text' => $this->model->$modelAttribute, 'edit' => true]), $this->getOptions());
        } else {
            $input = Html::textarea(((!$this->name) ? 'richtext' : $this->name), $this->value, $inputOptions);
            $richText = Html::tag('div', RichText::widget(['text' => $this->value, 'edit' => true]),$this->getOptions());
        }
        
        return $input.$richText;
    }
    
    public function getData()
    {
        $result = [
            'excludes' => $this->excludes,
            'includes' => $this->includes,
            'mentioning-url' => $this->getMentioningUrl(),
            'placeholder' => $this->placeholder,
        ];
        
        if($this->disabled) {
            $result['disabled'] = true;
            $result['disabled-text'] = $this->disabledText;
        }
        
        return $result;
    }
    
    public function getAttributes()
    {
        return [
            'class' => "atwho-input form-control humhub-ui-richtext",
            'contenteditable' => "true",
        ];
    }
    
    public function getMentioningUrl()
    {
        return ($this->mentioningUrl) ? $this->mentioningUrl : Url::to([$this->mentioningRoute]);
    }
}
