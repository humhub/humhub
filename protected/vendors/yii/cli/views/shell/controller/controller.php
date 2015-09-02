<?php
/**
 * This is the template for generating a controller class file.
 * The following variables are available in this template:
 * - $className: the class name of the controller
 * - $actions: a list of action names for the controller
 */
?>
<?php echo "<?php\n"; ?>

class <?php echo $className; ?> extends Controller
{
<?php foreach($actions as $action): ?>
	public function action<?php echo ucfirst($action); ?>()
	{
		$this->render('<?php echo $action; ?>');
	}

<?php endforeach; ?>
	// -----------------------------------------------------------
	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}