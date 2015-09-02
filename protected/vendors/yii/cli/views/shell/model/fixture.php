<?php
/**
 * This is the template for generating the fixture file for a model class.
 * The following variables are available in this template:
 * - $table: the table schema
 */
?>
<?php echo "<?php\n"; ?>

return array(
	/*
	'sample1'=>array(
<?php foreach($table->columns as $name=>$column) {
	if($table->sequenceName===null || $table->primaryKey!==$column->name)
		echo "\t\t'$name' => '',\n";
} ?>
	),
	'sample2'=>array(
<?php foreach($table->columns as $name=>$column) {
	if($table->sequenceName===null || $table->primaryKey!==$column->name)
		echo "\t\t'$name' => '',\n";
} ?>
	),
	*/
);
