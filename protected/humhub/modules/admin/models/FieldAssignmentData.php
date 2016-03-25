<?php

namespace humhub\modules\admin\models;

use Yii;

class FieldAssignmentData
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
   
}

?>