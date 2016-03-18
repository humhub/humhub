<?php

namespace humhub\modules\admin\models;

use Yii;

class FieldAssignmentData extends \yii\base\Model
{
    public $fieldId;
    public $name;
    public $showable;
    public $groupId;
    
    function __construct($groupId, $fieldId, $name, $showable) {
       $this->fieldId = $fieldId;
       $this->name = $name;
       $this->showable = $showable;
       $this->groupId = $groupId;
   }
   
   public function rules()
    {
        return array(
            array('showable', 'boolean')
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'showable' => 'showable',
        );
    }
}

?>