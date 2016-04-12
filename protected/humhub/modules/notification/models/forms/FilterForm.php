<?php

namespace humhub\modules\notification\models\forms;

use Yii;
use humhub\modules\notification\models\Notification;

/**
 * @package humhub.forms
 * @since 0.5
 */
class FilterForm extends \yii\base\Model
{

    public $classFilter;
    public $classFilterSelection;
    
     public function rules()
    {
        return [
            [['classFilter'], 'safe'],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'classFilter' => Yii::t('NotificationModule.views_overview_index', 'Notification Type'),
        ];
    }
    
    public function initFilter()
    {
        $this->classFilter = [];
        foreach($this->getClassFilterSelection() as $class => $title) {
            $this->classFilter[] = $class;
        }
    }
    
    public function getExcludeFilter()
    {
        $result = [];
        foreach($this->getClassFilterSelection() as $class => $title) {
            if(!in_array($class, $this->classFilter)) {
                $result[] = $class;
            }
        }
        return $result;
    }
    
    public function getClassFilterSelection()
    {
        if($this->classFilterSelection == null) {
            $this->classFilterSelection = [];
            $classes = Notification::getNotificationClasses();

            foreach($classes as $type) {
                $class = $type['class'];
                $title = $class::getTitle();
                if($title != null && !in_array($class, $this->classFilterSelection)) {
                    $this->classFilterSelection[$class] = $title;
                }
            }
            
            $this->classFilterSelection['other'] = Yii::t('NotificationModule.models_forms_FilterForm', 'Other');
        }
        return $this->classFilterSelection;
    }
}
