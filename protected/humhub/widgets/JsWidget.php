<?php
namespace humhub\widgets;


use humhub\components\Widget;
/**
 * Description of JsWidget
 *
 * @author buddha
 * @since 1.2
 */
class JsWidget extends Widget
{
    /**
     * Defines the select input field id
     * 
     * @var string 
     */
    public $id;
    
    /**
     * Js Widget namespace
     * @var type 
     */
    public $jsWidget;
    
    /*
     * Used to overwrite select input field attributes. This array can be used for overwriting
     * texts, or other picker settings.
     * 
     * @var string
     */
    public $options = [];
    
    /**
     * Event action handler.
     * @var type 
     */
    public $events = [];
    
    /**
     * Auto init flag.
     * @var mixed 
     */
    public $init = false;
    
    /**
     * Used to hide/show the actual input element.
     * @var type 
     */
    public $visible = true;
    
    /**
     * Assembles all widget attributes and data settings of this widget.
     * Those attributes/options are are normally transfered to the js client by ordinary html attributes
     * or by using data-* attributes.
     * 
     * @return array
     */
    protected function getOptions()
    {
        $attributes = $this->getAttributes();
        $attributes['data'] = $this->getData();
        $attributes['id'] = $this->getId();
            
        $this->setDefaultOptions();
        
        $result = \yii\helpers\ArrayHelper::merge($attributes, $this->options);        
        
        if(!$this->visible) {
            if(isset($result['style'])) {
                $result['style'] .= ';display:none;';
            } else {
                $result['style'] = 'display:none;';
            }
        }
        
        return $result;
    }
    
    /**
     * Sets some default data options required by all widgets as the widget implementation
     * and the widget evetns and initialization trigger.
     */
    public function setDefaultOptions() 
    {   
        // Set event data
        foreach($this->events as $event => $handler) {
            $this->options['data']['widget-action-'.$event] = $handler;
        }
        
        $this->options['data']['ui-widget'] = $this->jsWidget;
        
        if(!empty($this->init)) {
            $this->options['data']['ui-init'] = $this->init;
        } 
    }
    
    /**
     * Returns the html id of this widget, if no id is set this function will generate
     * an id if $autoGenerate is set to true (default).
     * 
     * Note that the id is automatically included within the <code>getOptions()<code> function.
     * 
     * @param type $autoGenerate
     * @return type
     */
    public function getId($autoGenerate = true)
    {
        if($this->id) {
            return $this->id;
        }
        return parent::getId($autoGenerate);
    }
    
    /**
     * Returns an array of data-* attributes to configure your clientside js widget. 
     * Note that this function does not require to add the data- prefix. This will be done by Yii.
     * 
     * The data-* attributes should be inserted to the widgets root element.
     * 
     * @return type
     */
    protected function getData() 
    {
        return [];
    }
    
    /**
     * Returns all html attributes for used by this widget and will normally inserted in the widgets root html element.
     * @return type
     */
    protected function getAttributes()
    {
        return [];
    }
}
