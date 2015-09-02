<?php
/**
 * This is the template for generating the 'view' view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<?php
echo "<?php\n";
$nameColumn=$this->guessNameColumn($columns);
$label=$this->class2name($modelClass,true);
echo "\$this->breadcrumbs=array(
	'$label'=>array('index'),
	\$model->{$nameColumn},
);\n";
?>

$this->menu=array(
	array('label'=>'List <?php echo $modelClass; ?>', 'url'=>array('index')),
	array('label'=>'Create <?php echo $modelClass; ?>', 'url'=>array('create')),
	array('label'=>'Update <?php echo $modelClass; ?>', 'url'=>array('update', 'id'=>$model-><?php echo $ID; ?>)),
	array('label'=>'Delete <?php echo $modelClass; ?>', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model-><?php echo $ID; ?>),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage <?php echo $modelClass; ?>', 'url'=>array('admin')),
);
?>

<h1>View <?php echo $modelClass." #<?php echo \$model->{$ID}; ?>"; ?></h1>

<?php echo "<?php"; ?> $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
<?php
foreach($columns as $column)
	echo "\t\t'".$column->name."',\n";
?>
	),
)); ?>
