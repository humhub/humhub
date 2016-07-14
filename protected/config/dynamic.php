<?php return array (
  'components' => 
  array (
    'db' => 
    array (
      'class' => 'yii\\db\\Connection',
      'dsn' => 'mysql:host=localhost;dbname=humhub_new',
      'username' => 'root',
      'password' => 'root',
      'charset' => 'utf8',
    ),
    'user' => 
    array (
    ),
    'mailer' => 
    array (
      'transport' => 
      array (
        'class' => 'Swift_MailTransport',
      ),
      'view' => 
      array (
        'theme' => 
        array (
          'name' => 'HumHub',
          'basePath' => '/var/www/public_html/huntedhive/humhub-1.0.1/themes/HumHub',
        ),
      ),
    ),
    'view' => 
    array (
      'theme' => 
      array (
        'name' => 'HumHub',
        'basePath' => '/var/www/public_html/huntedhive/humhub-1.0.1/themes/HumHub',
      ),
    ),
    'formatter' => 
    array (
      'defaultTimeZone' => 'Europe/Helsinki',
    ),
    'formatterApp' => 
    array (
      'defaultTimeZone' => 'Europe/Helsinki',
      'timeZone' => 'Europe/Helsinki',
    ),
  ),
  'params' => 
  array (
    'installer' => 
    array (
      'db' => 
      array (
        'installer_hostname' => 'localhost',
        'installer_database' => 'humhub_new',
      ),
    ),
    'settings' => 
    array (
      'core' => 
      array (
        'colorDefault' => '#ededed',
        'colorPrimary' => '#708fa0',
        'colorInfo' => '#6fdbe8',
        'colorSuccess' => '#97d271',
        'colorWarning' => '#fdd198',
        'colorDanger' => '#ff8989',
        'oembedProviders' => '{"vimeo.com":"http:\\/\\/vimeo.com\\/api\\/oembed.json?scheme=https&url=%url%&format=json&maxwidth=450","youtube.com":"http:\\/\\/www.youtube.com\\/oembed?scheme=https&url=%url%&format=json&maxwidth=450","youtu.be":"http:\\/\\/www.youtube.com\\/oembed?scheme=https&url=%url%&format=json&maxwidth=450","soundcloud.com":"https:\\/\\/soundcloud.com\\/oembed?url=%url%&format=json&maxwidth=450","slideshare.net":"https:\\/\\/www.slideshare.net\\/api\\/oembed\\/2?url=%url%&format=json&maxwidth=450"}',
        'name' => 'HumHubTest',
        'baseUrl' => 'http://huntedhive.ua/humhub-1.0.1',
        'paginationSize' => '10',
        'displayNameFormat' => '{profile.firstname} {profile.lastname}',
        'theme' => 'HumHub',
        'defaultLanguage' => 'en',
        'useCase' => 'community',
        'noUsers' => 'mostactiveusers',
        'secret' => 'd67e94fc-2cf1-490d-96fe-27c8e979f613',
        'timeZone' => 'Europe/Helsinki',
        'type_manage' => '0',
        'required_manage' => '0',
        'logic_enter' => 'IF teacher_type = "math math2" and teacher_level = "level" and subject_area = "math math2" and email_domain = "edu.au" THEN insert into "Welcome Space, default, some-some"',
        'logic_else' => 'Welcome Space, default, some, callback',
      ),
      'space' => 
      array (
        'defaultVisibility' => '1',
        'defaultJoinPolicy' => '1',
        'spaceOrder' => '0',
      ),
      'authentication' => 
      array (
        'authInternal' => '1',
        'authLdap' => '0',
      ),
      'authentication_ldap' => 
      array (
        'refreshUsers' => '1',
      ),
      'authentication_internal' => 
      array (
        'needApproval' => '0',
        'anonymousRegistration' => '1',
        'internalUsersCanInvite' => '1',
        'allowGuestAccess' => '1',
      ),
      'mailing' => 
      array (
        'transportType' => 'php',
        'systemEmailAddress' => 'social@example.com',
        'systemEmailName' => 'HumHubTest',
        'receive_email_activities' => '1',
        'receive_email_notifications' => '2',
      ),
      'file' => 
      array (
        'maxFileSize' => '1048576',
        'maxPreviewImageWidth' => '200',
        'maxPreviewImageHeight' => '200',
        'hideImageFileInfo' => '0',
      ),
      'cache' => 
      array (
        'type' => 'CFileCache',
        'expireTime' => '3600',
      ),
      'admin' => 
      array (
        'installationId' => 'e4e754d075f7714b15e388e7fc49bd40',
        'defaultDateInputFormat' => '',
      ),
      'tour' => 
      array (
        'enable' => '1',
      ),
      'share' => 
      array (
        'enable' => '1',
      ),
      'notification' => 
      array (
        'enable_html5_desktop_notifications' => '0',
      ),
      'birthday' => 
      array (
        'shownDays' => '2',
      ),
      'mostactiveusers' => 
      array (
        'noUsers' => '5',
      ),
      'installer' => 
      array (
        'sampleData' => '0',
      ),
      'dashboard' => 
      array (
        'showProfilePostForm' => '0',
      ),
    ),
    'config_created_at' => 1468488260,
    'databaseInstalled' => true,
    'installed' => true,
  ),
  'name' => 'HumHubTest',
  'language' => 'en',
  'timeZone' => 'Europe/Helsinki',
); ?>