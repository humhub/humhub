<?php
/**
 * This is the configuration for generating message translations
 * for the Yii framework. It is used by the 'yiic message' command.
 */
return array(
	//'sourcePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'../..',
	'sourcePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    
	'messagePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'messages',
	//'languages'=>array('fi','zh_cn','zh_tw','de','de_du','1337', 'el','es','sv','he','nl','pt','pt_br','ru','it','fr','ja','pl','hu','ro','id','vi','bg','lv','sk','uk','ko_kr','kk','cs'),
	'languages'=>array('de','fr','nl'),

    'fileTypes'=>array('php'),
	'overwrite'=>true,
	'exclude'=>array(
		'.svn',
		'.gitignore',
		'yiilite.php',
		'yiit.php',
		'/i18n/data',
		'/messages',
		'/vendors',
		'/web/js',
        'yii',
	),
);
