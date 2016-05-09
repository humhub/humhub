<?php

return array(
    array('name' => 'name', 'value' => 'HumHub', 'module_id' => NULL),
    array('name' => 'baseUrl', 'value' => 'http://dev2/humhub_test', 'module_id' => NULL),
    array('name' => 'paginationSize', 'value' => '10', 'module_id' => NULL),
    array('name' => 'displayNameFormat', 'value' => '{profile.firstname} {profile.lastname}', 'module_id' => NULL),
    array('name' => 'authInternal', 'value' => '1', 'module_id' => 'authentication'),
    array('name' => 'auth.needApproval', 'value' => '0', 'module_id' => 'user'),
    array('name' => 'auth.anonymousRegistration', 'value' => '1', 'module_id' => 'user'),
    array('name' => 'auth.internalUsersCanInvite', 'value' => '1', 'module_id' => 'user'),
    array('name' => 'mailer.transportType', 'value' => 'php', 'module_id' => 'base'),
    array('name' => 'mailer.systemEmailAddress', 'value' => 'social@example.com', 'module_id' => 'base'),
    array('name' => 'mailer.systemEmailName', 'value' => 'My Social Network', 'module_id' => 'base'),
    array('name' => 'receive_email_activities', 'value' => '1', 'module_id' => 'activity'),
    array('name' => 'receive_email_notifications', 'value' => '2', 'module_id' => 'notification'),
    array('name' => 'maxFileSize', 'value' => '1048576', 'module_id' => 'file'),
    array('name' => 'forbiddenExtensions', 'value' => 'exe', 'module_id' => 'file'),
    array('name' => 'cache.class', 'value' => 'CFileCache', 'module_id' => 'base'),
    array('name' => 'cache.expireTime', 'value' => '3600', 'module_id' => 'base'),
    array('name' => 'installationId', 'value' => '99846c45e9b9b0962238986a6fed519a', 'module_id' => 'admin'),
    array('name' => 'theme', 'value' => 'HumHub', 'module_id' => 'base'),
    array('name' => 'tour', 'value' => '1', 'module_id' => 'base')
);
