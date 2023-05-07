<?php

/**
 * This file provides to overwrite the default HumHub / Yii configuration by your local common (Console and Web) environments
 * @see http://www.yiiframework.com/doc-2.0/guide-concept-configurations.html
 * @see http://docs.humhub.org/admin-installation-configuration.html
 * @see http://docs.humhub.org/dev-environment.html
 */

$common = [
    'params' => [
        'enablePjax' => false
    ],
    'components' => [
        'urlManager' => [
            'showScriptName' => false,
            'enablePrettyUrl' => true,
        ],
    ]
];

/**
 * UrlManager
 *
 * @see https://github.com/humhub/humhub/issues/2220
 */
if (!empty(getenv('HUMHUB_PROTO'))) {
    if (!empty(getenv('HUMHUB_HOST'))) {
        $common['components']['urlManager']["hostInfo"] = getenv('HUMHUB_PROTO') . "://" . getenv('HUMHUB_HOST');
        $common['components']['urlManager']["baseUrl"] = "/";
    }
}

/**
 * LDAP Thumbnailsync for Advanced LDAP Module
 *
 * @see https://www.humhub.com/de/marketplace/advanced-ldap/
 */
if (!empty(getenv('HUMHUB_ADVANCED_LDAP_THUMBNAIL_SYNC_PROPERTY'))) {
    $common['components']['authClientCollection']['clients']['ldap'] = [
        'class' => 'humhub\modules\advancedLdap\authclient\LdapAuth',
        'profileImageAttribute' => getenv('HUMHUB_ADVANCED_LDAP_THUMBNAIL_SYNC_PROPERTY')
    ];
}

/**
 * Redis configuration.
 *
 * @see https://docs.humhub.org/docs/admin/redis
 */
if (!empty(getenv('HUMHUB_REDIS_HOSTNAME'))) {
    $common['components']['redis'] = [
        'class' => 'yii\redis\Connection',
        'hostname' => getenv('HUMHUB_REDIS_HOSTNAME'),
        'port' => !empty(getenv('HUMHUB_REDIS_PORT')) ? getenv('HUMHUB_REDIS_PORT') : 6379,
        'database' => 0,
    ];
    if (!empty(getenv('HUMHUB_REDIS_PASSWORD'))) {
        $common['components']['redis']['password'] = getenv('HUMHUB_REDIS_PASSWORD');
    }

    if (!empty(getenv('HUMHUB_CACHE_CLASS'))) {
        $common['components']['cache'] = [
            'class' => getenv('HUMHUB_CACHE_CLASS'),
        ];
    }

    if (!empty(getenv('HUMHUB_QUEUE_CLASS'))) {
        $common['components']['queue'] = [
            'class' => getenv('HUMHUB_QUEUE_CLASS'),
        ];
    }

    if (!empty(getenv('HUMHUB_PUSH_URL')) && !empty(getenv('HUMHUB_PUSH_JWT_TOKEN'))) {
        $common['components']['push'] = [
            'class' => 'humhub\modules\live\driver\Push',
            'url' => getenv('HUMHUB_PUSH_URL'),
            'jwtKey' => getenv('HUMHUB_PUSH_JWT_TOKEN'),
        ];
    }
}

// Print generated common config
var_export($common);
