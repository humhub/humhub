<?php return  [
  'name' => 'HumHub',
  'language' => 'en-US',
  'timeZone' => 'Europe/Berlin',
  'components' => 
   [
    'formatter' => 
     [
      'defaultTimeZone' => 'Europe/Berlin',
    ],
    'formatterApp' => 
     [
      'defaultTimeZone' => 'Europe/Berlin',
      'timeZone' => 'Europe/Berlin',
    ],
    'user' => 
     [
    ],
    'mailer' => 
     [
      'transport' => 
       [
      ],
      'useFileTransport' => true,
    ],
    'cache' => 
     [
      'class' => 'yii\\caching\\DummyCache',
      'keyPrefix' => 'humhub',
    ],
  ],
  'params' => 
   [
    'config_created_at' => 1509135303,
    'horImageScrollOnMobile' => null,
  ],
]; ?>