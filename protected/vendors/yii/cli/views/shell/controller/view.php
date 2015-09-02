<?php
echo "<?php\n";
$controller=substr($controller,0,strlen($controller)-10);
$label=ucwords(trim(strtolower(str_replace(array('-','_','.'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $controller)))));

if($action==='index')
{
	echo "\$this->breadcrumbs=array(
	'$label',
);";
}
else
{
	$route=$controller.'/index';
	$route[0]=strtolower($route[0]);
	$action=ucfirst($action);
	echo "\$this->breadcrumbs=array(
	'$label'=>array('$route'),
	'$action',
);";
}
?>
?>
<h1><?php echo '<?php'; ?> echo $this->id . '/' . $this->action->id; ?></h1>

<p>You may change the content of this page by modifying the file <tt><?php echo '<?php'; ?> echo __FILE__; ?></tt>.</p>
