<?php

return  [
    'name' => 'HumHub',
    'language' => 'en-US',
    'timeZone' => 'Europe/Berlin',
    'components'
     => [
         'formatter'
          => [
              'defaultTimeZone' => 'Europe/Berlin',
          ],
         'user'
          => [
          ],
         'mailer'
          => [
              'useFileTransport' => true,
          ],
         'cache'
          => [
              'class' => 'yii\\caching\\DummyCache',
              'keyPrefix' => 'humhub',
          ],
     ],
    'params'
     => [
         'config_created_at' => 1509135303,
         'horImageScrollOnMobile' => null,
     ],
];
