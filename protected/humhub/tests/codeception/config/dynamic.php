<?php return  [
  'name' => 'HumHub',
  'language' => 'en-US',
  'timeZone' => 'Pacific/Niue',
  'components' =>
   [
    'formatter' =>
     [
      'defaultTimeZone' => 'Pacific/Niue',
     ],
     'formatterApp' =>
     [
      'defaultTimeZone' => 'Pacific/Niue',
      'timeZone' => 'Pacific/Niue',
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
       'view' =>
       [
        'theme' =>
         [
          'name' => 'HumHub',
          'basePath' => 'E:\\codebase\\humhub\\master/themes\\HumHub',
          'publishResources' => false,
         ],
       ],
     ],
     'view' =>
     [
      'theme' =>
       [
        'name' => 'HumHub',
        'basePath' => 'E:\\codebase\\humhub\\master/themes\\HumHub',
        'publishResources' => false,
       ],
     ],
   ],
   'params' =>
   [
    'config_created_at' => 1501606491,
    'horImageScrollOnMobile' => null,
   ],
];
