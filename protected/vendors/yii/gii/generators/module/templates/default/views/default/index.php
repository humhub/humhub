<?php echo "<?php\n"; ?>
/* @var $this DefaultController */

$this->breadcrumbs=array(
	$this->module->id,
);
?>
<h1><?php echo "<?php"; ?> echo $this->uniqueId . '/' . $this->action->id; ?></h1>

<p>
This is the view content for action "<?php echo "<?php"; ?> echo $this->action->id; ?>".
The action belongs to the controller "<?php echo "<?php"; ?> echo get_class($this); ?>"
in the "<?php echo "<?php"; ?> echo $this->module->id; ?>" module.
</p>
<p>
You may customize this page by editing <tt><?php echo "<?php"; ?> echo __FILE__; ?></tt>
</p>