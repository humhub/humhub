<?php

namespace humhub\modules\admin\models;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\ProfileFieldGroup;

use Yii;


class FieldAssignment extends \yii\base\Model
{
    public $groups;
    
    public function rules()
    {
        return array(
             array('groups', 'in', 'range' => \yii\helpers\ArrayHelper::map(Group::find()->all(), 'id', 'name')),
        );
    }
    
    public function attributeLabels()
    {
        return array('groups' => 'Groups');
    }
    
    public function getFieldAssignmentDataOfGroup($groupid)
    {
    
        $fields = ProfileField::find()->all();
        $group_array = array();
        
        foreach($fields as $field) 
        {
            $showable = $this->hasFieldAssignmentCombination($groupid, $field->id);
            array_push($group_array, new FieldAssignmentData($groupid, $field->id, $field->title, $showable));                
        }

        return $group_array;
        
    } 
    
    public function getFieldAssignmentData()
    {
        $data =  array();
     
        $fields = ProfileField::find()->all();
        $groupData = Group::find()->all();       
        
        foreach($groupData as $group)
        {
            array_push($data, FieldAssignment::getFieldAssignmentDataOfGroup($group->id));
        }
        
        return $data;
        
    }
    
    private function hasFieldAssignmentCombination($groupId, $fieldId){
        $queryFieldMapping = ProfileFieldGroup::findOne(["group_id"=>$groupId, 
                "profile_field_id"=>$fieldId]);
                
        return count($queryFieldMapping)>0;
    }
    
    
    
    public function saveFieldAssignmentData($groupId, $fieldIds){
        $usedFieldIds = array();
         foreach($fieldIds as $fieldId){
             $tempFieldId = str_replace("field", "", $fieldId);
             array_push($usedFieldIds, $tempFieldId);
             if(!$this->hasFieldAssignmentCombination($groupId, $tempFieldId))
             {
                $newProfileFieldGroup = new ProfileFieldGroup(); 
                $newProfileFieldGroup->group_id=$groupId;
                $newProfileFieldGroup->profile_field_id = $tempFieldId;
                $newProfileFieldGroup->save();
             }
         }
         
         $this->deleteUnusedFieldAssignmentData($groupId, $usedFieldIds);
    }
    
    private function deleteUnusedFieldAssignmentData($groupId, $usedFieldIds){
         $queryFieldMappings = ProfileFieldGroup::find(["group_id"=>$groupId])->all();
         
         foreach($queryFieldMappings as $row){
             if(!in_array($row->profile_field_id, $usedFieldIds)){
                $rowToDelete =  ProfileFieldGroup::findOne(["group_id"=>$groupId, 
                                                            "profile_field_id"=>$row->profile_field_id]);
                if($rowToDelete!=null){
                    $rowToDelete->delete();
                }
             }
         }
    }
}


?>