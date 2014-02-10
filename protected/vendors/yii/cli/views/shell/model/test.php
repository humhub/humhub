<?php
/**
 * This is the template for generating the unit test for a model class.
 * The following variables are available in this template:
 * - $className: the class name
 * - $fixtureName: the fixture name
 */
?>
<?php echo "<?php\n"; ?>

class <?php echo $className; ?>Test extends CDbTestCase
{
	public $fixtures=array(
		'<?php echo $fixtureName; ?>'=>'<?php echo $className; ?>',
	);

	public function testCreate()
	{

	}
}