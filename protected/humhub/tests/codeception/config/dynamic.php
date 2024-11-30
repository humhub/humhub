<?php

return  [
    'name' => 'HumHub',
    'components' =>
     [
         'user' =>
          [
          ],
         'mailer' =>
          [
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
         'config_created_at' => 1732966138,
         'horImageScrollOnMobile' => null,
     ],
];
