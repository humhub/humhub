<?php echo "<?php\n"; ?>
return [
	'id' => '<?php echo $generator->moduleID; ?>',
	'class' => 'humhub\modules\<?php echo $generator->moduleID; ?>\<?php echo $generator->getModuleClassName(); ?>',
	'namespace' => 'humhub\modules\<?php echo $generator->moduleID; ?>',
	'events' => [
		[
			'class' => \humhub\widgets\TopMenu::className(),
			'event' => \humhub\widgets\TopMenu::EVENT_INIT,
			'callback' => ['humhub\modules\<?php echo $generator->moduleID; ?>\Events', 'onTopMenuInit'],
		],
		[
			'class' => humhub\modules\admin\widgets\AdminMenu::className(),
			'event' => humhub\modules\admin\widgets\AdminMenu::EVENT_INIT,
			'callback' => ['humhub\modules\<?php echo $generator->moduleID; ?>\Events', 'onAdminMenuInit']
		],
	],
];
?>

