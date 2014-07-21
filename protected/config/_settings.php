<?php return array (
  'components' => 
  array (
    'db' => 
    array (
      'connectionString' => 'mysql:host=localhost;dbname=humhub',
      'username' => 'root',
      'password' => '',
    ),
    'cache' => 
    array (
      'class' => 'CFileCache',
    ),
    'mail' => 
    array (
      'class' => 'ext.yii-mail.YiiMail',
      'transportType' => 'php',
      'viewPath' => 'application.views.mail',
      'logging' => true,
      'dryRun' => false,
    ),
  ),
  'params' => 
  array (
    'installer' => 
    array (
      'db' => 
      array (
        'installer_hostname' => 'localhost',
        'installer_database' => 'humhub',
      ),
    ),
    'installed' => true,
  ),
  'name' => 'HumHub',
  'theme' => 'HumHub',
); ?>