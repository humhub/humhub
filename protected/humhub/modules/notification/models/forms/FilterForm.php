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
    const FILTER_OTHER = 'other';

    /**
     * Contains the current module filters
     * @var type array
     */
    public $moduleFilter;
    
    /**
     * Contains all available module filter
     * @var type array
     */
    public $moduleFilterSelection;
    
    /**
     * Contains all notifications by modulenames
     * @var type 
     */
    public $moduleNotifications;
    
     public function rules()
    {
        return [
            [['moduleFilter'], 'safe'],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'moduleFilter' => Yii::t('NotificationModule.views_overview_index', 'Module Filter'),
        ];
    }
    
    /**
     * Preselects all possible module filter
     */
    public function initFilter()
    {
        $this->moduleFilter = [];
        
        foreach($this->getModuleFilterSelection() as $moduleName => $title) {
            $this->moduleFilter[] = $moduleName;
        }
    }
    
    /**
     * Returns all Notifications classes of modules not selected in the filter
     * 
     * @return type
     */
    public function getExcludeClassFilter()
    {
        $result = [];
        $moduleNotifications = $this->getModuleNotifications();
        
        foreach($this->moduleFilterSelection as $moduleName => $title) {
            if($moduleName != self::FILTER_OTHER && !in_array($moduleName, $this->moduleFilter)) {
                $result = array_merge($result, $moduleNotifications[$moduleName]);
            }
        }
        return $result;
    }
    
    /**
     * Returns all Notifications classes of modules selected in the filter
     * @return type
     */
    public function getIncludeClassFilter()
    {
        $result = [];
        $moduleNotifications = $this->getModuleNotifications();
        
        foreach($this->moduleFilter as $moduleName) {
            if($moduleName != self::FILTER_OTHER) {
                $result = array_merge($result, $moduleNotifications[$moduleName]);
            }
        }
        return $result;
    }
    
    public function getModuleFilterSelection()
    {
        if($this->moduleFilterSelection == null) {
            $this->moduleFilterSelection = [];
            
            foreach(array_keys($this->getModuleNotifications()) as $moduleName) {
                $this->moduleFilterSelection[$moduleName] = $moduleName;
            }
            
            $this->moduleFilterSelection[self::FILTER_OTHER] = Yii::t('NotificationModule.models_forms_FilterForm', 'Other');
        }
        return $this->moduleFilterSelection;
    }
    
    public function getModuleNotifications()
    {
        if($this->moduleNotifications == null) {
            $this->moduleNotifications = Notification::getModuleNotifications();
        }
        
        return $this->moduleNotifications;
    }
    
    /**
     * Determines if this filter should exclude specific modules (if other filter is selected)
     * or rather include specific module filter.
     * 
     * @return boolean true if other was selected, else false
     */
    public function isExcludeFilter()
    {
        return $this->isActive() && in_array(self::FILTER_OTHER, $this->moduleFilter);
    }
    
    /**
     * Checks if this filter is active (at least one filter selected)
     * @return type
     */
    public function isActive()
    {
        return $this->moduleFilter != null;
    }
}
