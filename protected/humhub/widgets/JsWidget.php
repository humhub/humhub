<?php

namespace humhub\widgets;

use humhub\components\Widget;
use humhub\libs\Html;

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
     * @var string html container element. 
     */
    public $container = 'div';

    /**
     * If set to true or 'fast', 'slow' or a integer duration in milliseconds the jsWidget will fade in the root element after initialization.
     * This can be handy for widgets which need some time to initialize.
     *
     * @var bool|string|integer
     * @since 1.2.2
     */
    public $fadeIn = false;

    /**
     * @var string html content. 
     */
    public $content;
        
    /**
     * Default implementation of JsWidget.
     * This will render a widget html element specified by $container and $content and the given $options/$event attributes.
     * This function should be overwritten for widgets with a more complex rendering.
     * 
     * @return type
     */
    public function run()
    {
        return \yii\helpers\Html::tag($this->container, $this->content, $this->getOptions());
    }

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

        if (!$this->visible) {
            Html::addCssStyle($result, 'display:none');
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
        foreach ($this->events as $event => $handler) {
            $this->options['data']['widget-action-' . $event] = $handler;
        }

        if($this->jsWidget) {
            $this->options['data']['ui-widget'] = $this->jsWidget;
        }

        if($this->fadeIn) {
            $fadeIn = $this->fadeIn === true ? 'fast' : $this->fadeIn;
            $this->options['data']['widget-fade-in'] = $fadeIn;
            $this->visible = false;
        }

        if (!empty($this->init)) {
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
        if ($this->id) {
            return $this->id;
        }

        return $this->id = parent::getId($autoGenerate);
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
