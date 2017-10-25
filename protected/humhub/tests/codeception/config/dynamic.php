<?php return array (
  'name' => 'HumHub',
  'language' => 'en-US',
  'timeZone' => 'Europe/Berlin',
  'components' => 
  array (
    'formatter' => 
    array (
      'defaultTimeZone' => 'Europe/Berlin',
    ),
    'formatterApp' => 
    array (
      'defaultTimeZone' => 'Europe/Berlin',
      'timeZone' => 'Europe/Berlin',
    ),
    'user' => 
    array (
    ),
    'mailer' => 
    array (
      'transport' => 
      array (
      ),
      'useFileTransport' => true,
      'view' => 
      array (
        'theme' => 
        array (
          'name' => 'HumHub',
          'basePath' => 'E:\\codebase\\humhub\\master/themes\\HumHub',
          'publishResources' => false,
        ),
      ),
    ),
    'view' => 
    array (
      'theme' => 
      array (
        'name' => 'HumHub',
        'basePath' => 'E:\\codebase\\humhub\\master/themes\\HumHub',
        'publishResources' => false,
      ),
    ),
    'cache' => 
    array (
      'class' => 'yii\\caching\\DummyCache',
      'keyPrefix' => 'humhub',
    ),
  ),
  'params' => 
  array (
    'config_created_at' => 1508527680,
    'horImageScrollOnMobile' => NULL,
  ),
); ?>