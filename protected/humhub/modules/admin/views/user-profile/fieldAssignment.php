<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use humhub\compat\CActiveForm;
use humhub\modules\user\models\Group;
use humhub\compat\CHtml;
?>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="panel-heading"> <strong><?php echo 'Field Assignment'; ?></strong></div>
        
        <?php  
        
            $form = CActiveForm::begin(['options'=>['class'=>'fieldForm']]);
            echo '<div class="dropdown">';

            $recievedGroupId;
            if(Yii::$app->request->get('groupId')!=null) {
                $recievedGroupId = Yii::$app->request->get('groupId');
            }
            else{
                $recievedGroupId = Group::find()->all()[0]->id;
            }
                
            echo "Group selection: ";
            echo $form->dropDownList($model, 
                                    'groups', 
                                    \yii\helpers\ArrayHelper::map(Group::find()->all(), 'id', 'name'),
                                    array(
                                        'options' => array($recievedGroupId=>array('selected '=>'selected')), // white space needed -> https://github.com/yiisoft/yii2/issues/2728
                                        'style' => array('margin-bottom'=>'10px')
                                    )); 
                
            echo '</div>';
            
            echo '<strong>Fields</strong>';              
                
            $fieldGroup = $model->getFieldAssignmentDataOfGroup($recievedGroupId);

            $fieldGroupNew = array();
            $fieldGroupNewPreSelected = array();
            foreach($fieldGroup as $field) 
            {                
                $fieldGroupNew = array_merge($fieldGroupNew, array('field'.$field->fieldId => $field->name));   
                if($field->showable == true) {
                    array_push($fieldGroupNewPreSelected, 'field'.$field->fieldId);
                }
            }
            
            echo '<div class="checkboxgroup">';
            echo CHtml::checkBoxList('fieldAssignemnt',$fieldGroupNewPreSelected,$fieldGroupNew,
                        array('template'=>'{input}{label}',
                        'separator'=>'</br>')); 
            echo '</div>';
            
            echo Html::submitButton(Yii::t('AdminModule.views_userprofile_fieldAssignment', 'Save'), array('class' => 'btn btn-primary'));
            echo Html::a(Yii::t('AdminModule.views_userprofile_index', 'Add new field'), Url::to(['edit-field']), array('class' => 'btn btn-default form-button-search'));
            CActiveForm::end(); 
        ?>
        
    </div>
</div>

<script>
var comboBox = document.getElementById("fieldassignment-groups");
comboBox.onchange = function()
{
    window.location = "<?php  echo Url::to(['field-assignment']); ?>&groupId="+ comboBox.value;
};  
</script>

