<?php
/**
 * This is the template for generating the action script for the form.
 * The following variables are available in this template:
 * - $modelClass: the model class name
 * - $viewName: the name of the view
 */
?>
<?php
$actionName=$modelClass;
if(strrpos($modelClass,'Form')===strlen($modelClass)-4)
    $actionName=substr($modelClass,0,strlen($modelClass)-4);
?>
public function action<?php echo $actionName; ?>()
{
    $model=new <?php echo $modelClass; ?>;

    // uncomment the following code to enable ajax-based validation
    /*
    if(isset($_POST['ajax']) && $_POST['ajax']==='<?php echo $this->class2id($modelClass); ?>-form')
    {
        echo CActiveForm::validate($model);
        Yii::app()->end();
    }
    */

    if(isset($_POST['<?php echo $modelClass; ?>']))
    {
        $model->attributes=$_POST['<?php echo $modelClass; ?>'];
        if($model->validate())
        {
            // form inputs are valid, do something here
            return;
        }
    }
    $this->render('<?php echo $viewName; ?>',array('model'=>$model));
}