<?php

/**
 * Application configuration shared by all test types
 */

$default = [
    'name' => 'HumHub Test',
    'language' => 'en-US',
    'params' => [
        'installed' => true,
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\faker\FixtureController',
            'fixtureDataPath' => '@tests/codeception/fixtures',
            'templatePath' => '@tests/codeception/templates',
            'namespace' => 'tests\codeception\fixtures',
        ],
    ],
    'components' => [
        'mailer' => [
            'messageClass' => \yii\symfonymailer\Message::class,
        ],
        'urlManager' => [
            'showScriptName' => true,
            'scriptUrl' => '/index-test.php',
        ],
    ],
    'container' => [
        'definitions' => [
            \Codeception\Lib\Connector\Yii2\TestMailer::class => [
                'class' => \tests\codeception\_support\TestMailer::class,
            ],
        ],
    ],
    'modules' => [
        'user' => [
            'passwordStrength' => [
                '/^(.*?[A-Z]){2,}.*$/' => 'Password has to contain two uppercase letters.',
                '/^.{8,}$/' => 'Password needs to be at least 8 characters long.',
            ],
        ],
    ],
];

$ldap = [
    'components' => [
        'authClientCollection' => [
            'clients' => [
                'ldap' => [
                    'class' => \humhub\modules\ldap\authclient\LdapAuth::class,
                    'hostname' => getenv('LDAP_TEST_HOST') ?: 'localhost',
                    'port' => (int)(getenv('LDAP_TEST_PORT') ?: 389),
                    'bindUsername' => getenv('LDAP_TEST_BIND_DN') ?: 'cn=admin,dc=example,dc=org',
                    'bindPassword' => getenv('LDAP_TEST_BIND_PASSWORD') ?: 'secret',
                    'baseDn' => getenv('LDAP_TEST_BASE_DN') ?: 'ou=users,dc=example,dc=org',
                    'userFilter' => getenv('LDAP_TEST_USER_FILTER') ?: '(objectClass=inetOrgPerson)',
                    'usernameAttribute' => getenv('LDAP_TEST_USERNAME_ATTRIBUTE') ?: 'uid',
                    'emailAttribute' => 'mail',
                    'idAttribute' => 'uid',
                ],
            ],
        ],
    ],
];

$envCfg = dirname(__DIR__) . '/../config/env/env.php';
$env = file_exists($envCfg) ? require($envCfg) : [];

return yii\helpers\ArrayHelper::merge(
    // Default Test Config
    $default,
    // LDAP Auth Client
    $ldap,
    // User Overwrite
    require(dirname(__DIR__) . '/../config/common.php'),
    $env,
);
