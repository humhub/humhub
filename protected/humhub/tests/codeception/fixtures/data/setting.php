<?php
return [
    ['name' => 'name', 'value' => 'HumHub', 'module_id' => 'base'],
    ['name' => 'baseUrl', 'value' => 'http://dev2/humhub_test', 'module_id' => 'base'],
    ['name' => 'paginationSize', 'value' => '10', 'module_id' => 'base'],
    ['name' => 'displayNameFormat', 'value' => '{profile.firstname} {profile.lastname}', 'module_id' => 'base'],
    ['name' => 'authInternal', 'value' => '1', 'module_id' => 'authentication'],
    ['name' => 'auth.needApproval', 'value' => '0', 'module_id' => 'user'],
    ['name' => 'auth.anonymousRegistration', 'value' => '1', 'module_id' => 'user'],
    ['name' => 'auth.internalUsersCanInvite', 'value' => '1', 'module_id' => 'user'],
    ['name' => 'mailer.transportType', 'value' => 'file', 'module_id' => 'base'],
    ['name' => 'mailer.systemEmailAddress', 'value' => 'social@example.com', 'module_id' => 'base'],
    ['name' => 'mailer.systemEmailName', 'value' => 'My Social Network', 'module_id' => 'base'],
    ['name' => 'receive_email_activities', 'value' => '1', 'module_id' => 'activity'],
    ['name' => 'receive_email_notifications', 'value' => '2', 'module_id' => 'notification'],
    ['name' => 'maxFileSize', 'value' => '1048576', 'module_id' => 'file'],
    ['name' => 'forbiddenExtensions', 'value' => 'exe', 'module_id' => 'file'],
    ['name' => 'cache.class', 'value' => 'CFileCache', 'module_id' => 'base'],
    ['name' => 'cache.expireTime', 'value' => '3600', 'module_id' => 'base'],
    ['name' => 'installationId', 'value' => '99846c45e9b9b0962238986a6fed519a', 'module_id' => 'admin'],
    ['name' => 'theme', 'value' => 'HumHub', 'module_id' => 'base'],
    ['name' => 'tour', 'value' => '1', 'module_id' => 'base'],
    ['name' => 'colorDefault', 'value' => '#ededed', 'module_id' => 'base'],
    ['name' => 'colorPrimary', 'value' => '#708fa0', 'module_id' => 'base'],
    ['name' => 'colorInfo', 'value' => '#6fdbe8', 'module_id' => 'base'],
    ['name' => 'colorSuccess', 'value' => '#97d271', 'module_id' => 'base'],
    ['name' => 'colorDanger', 'value' => '#ff8989', 'module_id' => 'base'],
    ['name' => 'oembedProviders', 'value' => json_encode([
        'Facebook Video' => [
            'pattern' => '/facebook\.com\/(.*)(video)/',
            'endpoint' => 'https://graph.facebook.com/v12.0/oembed_video?url=%url%&access_token='
        ],
        'Facebook Post' => [
            'pattern' => '/facebook\.com\/(.*)(post|activity|photo|permalink|media|question|note)/',
            'endpoint' => 'https://graph.facebook.com/v12.0/oembed_post?url=%url%&access_token='
        ],
        'Facebook Page' => [
            'pattern' => '/^(https\:\/\/)*(www\.)*facebook\.com\/((?!video|post|activity|photo|permalink|media|question|note).)*$/',
            'endpoint' => 'https://graph.facebook.com/v12.0/oembed_post?url=%url%&access_token='
        ],
        'Instagram' => [
            'pattern' => '/instagram\.com/',
            'endpoint' => 'https://graph.facebook.com/v12.0/instagram_oembed?url=%url%&access_token='
        ],
        'Twitter' => [
            'pattern' => '/twitter\.com/',
            'endpoint' => 'https://publish.twitter.com/oembed?url=%url%&maxwidth=450'
        ],
        'YouTube' => [
            'pattern' => '/youtube\.com|youtu.be/',
            'endpoint' => 'https://www.youtube.com/oembed?scheme=https&url=%url%&format=json&maxwidth=450'
        ],
        'Soundcloud' => [
            'pattern' => '/soundcloud\.com/',
            'endpoint' => 'https://soundcloud.com/oembed?url=%url%&format=json&maxwidth=450'
        ],
        'Vimeo' => [
            'pattern' => '/vimeo\.com/',
            'endpoint' => 'https://vimeo.com/api/oembed.json?scheme=https&url=%url%&format=json&maxwidth=450'
        ],
        'SlideShare' => [
            'pattern' => '/slideshare\.net/',
            'endpoint' => 'https://www.slideshare.net/api/oembed/2?url=%url%&format=json&maxwidth=450'
        ]
    ]), 'module_id' => 'base'],
    ['name' => 'defaultLanguage', 'value' => 'en-US', 'module_id' => 'base'],
    ['name' => 'maintenanceMode', 'value' => '0', 'module_id' => 'base'],
    ['name' => 'enableProfilePermissions', 'value' => '1', 'module_id' => 'user'],
];
