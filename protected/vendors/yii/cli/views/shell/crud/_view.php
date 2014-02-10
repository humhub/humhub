<?php
/**
 * This is the template for generating the partial view for rendering a single model.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<div class="view">

<?php
echo "\t<b><?php echo CHtml::encode(\$data->getAttributeLabel('{$ID}')); ?>:</b>\n";
echo "\t<?php echo CHtml::link(CHtml::encode(\$data->{$ID}), array('view', 'id'=>\$data->{$ID})); ?>\n\t<br />\n\n";
$count=0;
foreach($columns as $column)
{
	if($column->isPrimaryKey)
		continue;
	if(++$count==7)
		echo "\t<?php /*\n";
	echo "\t<b><?php echo CHtml::encode(\$data->getAttributeLabel('{$column->name}')); ?>:</b>\n";
	echo "\t<?php echo CHtml::encode(\$data->{$column->name}); ?>\n\t<br />\n\n";
}
if($count>=7)
	echo "\t*/ ?>\n";
?>

</div>