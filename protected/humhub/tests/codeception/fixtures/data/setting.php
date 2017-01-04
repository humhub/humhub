<?php

return [
    ['name' => 'name', 'value' => 'HumHub', 'module_id' => NULL],
    ['name' => 'baseUrl', 'value' => 'http://dev2/humhub_test', 'module_id' => NULL],
    ['name' => 'paginationSize', 'value' => '10', 'module_id' => NULL],
    ['name' => 'displayNameFormat', 'value' => '{profile.firstname} {profile.lastname}', 'module_id' => NULL],
    ['name' => 'authInternal', 'value' => '1', 'module_id' => 'authentication'],
    ['name' => 'auth.needApproval', 'value' => '0', 'module_id' => 'user'],
    ['name' => 'auth.anonymousRegistration', 'value' => '1', 'module_id' => 'user'],
    ['name' => 'auth.internalUsersCanInvite', 'value' => '1', 'module_id' => 'user'],
    ['name' => 'mailer.transportType', 'value' => 'php', 'module_id' => 'base'],
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
];
