<?php

namespace humhub\modules\content\widgets;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Generic WallEntryControlLink.
 *
 * @since 1.2
 * @author buddh4
 */
class WallEntryControlLink extends \humhub\components\Widget
{

    /**
     * Object derived from HActiveRecordContent
     *
     * @var string
     */
    public $label;
    
    /**
     * Object derived from HActiveRecordContent
     *
     * @var string
     */
    public $icon;

    protected $_icon;

    /**
     *
     * @var type 
     */
    public $options = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if(empty($this->label)) {
            $this->label = ArrayHelper::remove($this->options, 'label', 'Label');
        }

        if(empty($this->icon)) {
            $this->icon = ArrayHelper::remove($this->options, 'icon');
        }

        if($this->icon) {
            $this->_icon = '<i class="fa '.$this->icon.'"></i> ';
        }
        
        ArrayHelper::remove($this->options, 'sortOrder');
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return '<li>'.\yii\helpers\Html::a($this->_icon.$this->label, '#', $this->options).'</li>';
    }

}
