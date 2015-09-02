<?php
/**
 * This is the template for generating the action script for the form.
 * - $this: the CrudCode object
 */
?>
<?php
$viewName=basename($this->viewName);
?>
public function action<?php echo ucfirst(trim($viewName,'_')); ?>()
{
    $model=new <?php echo $this->modelClass; ?><?php echo empty($this->scenario) ? '' : "('{$this->scenario}')"; ?>;

    // uncomment the following code to enable ajax-based validation
    /*
    if(isset($_POST['ajax']) && $_POST['ajax']==='<?php echo $this->class2id($this->modelClass); ?>-<?php echo $viewName; ?>-form')
    {
        echo CActiveForm::validate($model);
        Yii::app()->end();
    }
    */

    if(isset($_POST['<?php echo $this->modelClass; ?>']))
    {
        $model->attributes=$_POST['<?php echo $this->modelClass; ?>'];
        if($model->validate())
        {
            // form inputs are valid, do something here
            return;
        }
    }
    $this->render('<?php echo $viewName; ?>',array('model'=>$model));
}