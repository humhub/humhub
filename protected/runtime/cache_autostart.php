<?php

Yii::app()->moduleManager->register(array(
    'id' => 'feedback',
    'class' => 'application.modules.feedback.FeedbackModule',
    'title' => Yii::t('FeedbackModule.base', 'Feedback'),
    'description' => Yii::t('FeedbackModule.base', 'Adds feedback functionality to the space.'),
    'import' => array(
        'application.modules.feedback.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'SpaceMenuWidget', 'event' => 'onInit', 'callback' => array('FeedbackModule', 'onSpaceMenuInit')),
    ),
    'spaceModules' => array(
        'feedback' => array(
            'id' => 'feedback',
            'title' => Yii::t('FeedbackModule.base', 'Feedback'),
            'description' => Yii::t('FeedbackModule.base', 'Adds an feedback page to your space.'),
        ),
    ),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'mail',
    'title' => Yii::t('MailModule.base', 'Mail'),
    'description' => Yii::t('MailModule.base', 'Adds the mailing core module.'),
    'class' => 'application.modules.mail.MailModule',
    'import' => array(
        'application.modules.mail.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('MailModule', 'onUserDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('MailModule', 'onIntegrityCheck')),
        array('class' => 'TopMenuWidget', 'event' => 'onInit', 'callback' => array('MailModule', 'onTopMenuInit')),
        array('class' => 'NotificationAddonWidget', 'event' => 'onInit', 'callback' => array('MailModule', 'onNotificationAddonInit')),
    ),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'polls',
    'class' => 'application.modules.polls.PollsModule',
    'title' => Yii::t('PollsModule.base', 'Polls'),
    'description' => Yii::t('PollsModule.base', 'Adds polling features to spaces.'),
    'import' => array(
        'application.modules.polls.models.*',
        'application.modules.polls.behaviors.*',
        'application.modules.polls.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('PollsModule', 'onUserDelete')),
        array('class' => 'Space', 'event' => 'onBeforeDelete', 'callback' => array('PollsModule', 'onSpaceDelete')),
        array('class' => 'Space', 'event' => 'onUninstallModule', 'callback' => array('PollsModule', 'onSpaceUninstallModule')),
        array('class' => 'SpaceMenuWidget', 'event' => 'onInit', 'callback' => array('PollsModule', 'onSpaceMenuInit')),
        array('class' => 'ModuleManager', 'event' => 'onDisable', 'callback' => array('PollsModule', 'onDisableModule')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('PollsModule', 'onIntegrityCheck')),
    ),
    'spaceModules' => array(
        'polls' => array(
            'title' => Yii::t('PollsModule.base', 'Polls'),
            'description' => Yii::t('PollsModule.base', 'Adds polling features to your space.'),
        ),
    ),
    'contentModels' => array('Poll'),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'tasks',
    'class' => 'application.modules.tasks.TasksModule',
    'title' => Yii::t('TasksModule.base', 'Tasks'),
    'description' => Yii::t('TasksModule.base', 'Adds a taskmanager to your spaces. With this module you can create and assign tasks to users in spaces.'),
    'import' => array(
        'application.modules.tasks.*',
        'application.modules.tasks.models.*',
        'application.modules.tasks.notifications.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'SpaceMenuWidget', 'event' => 'onInit', 'callback' => array('TasksModule', 'onSpaceMenuInit')),
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('TasksModule', 'onUserDelete')),
        array('class' => 'Space', 'event' => 'onBeforeDelete', 'callback' => array('TasksModule', 'onSpaceDelete')),
        array('class' => 'Space', 'event' => 'onUninstallModule', 'callback' => array('TasksModule', 'onSpaceUninstallModule')),
        array('class' => 'ModuleManager', 'event' => 'onDisable', 'callback' => array('TasksModule', 'onDisableModule')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('TasksModule', 'onIntegrityCheck')),
    ),
    'spaceModules' => array(
        'tasks' => array(
            'title' => Yii::t('TasksModule.base', 'Tasks'),
            'description' => Yii::t('TasksModule.base', 'Adds a taskmanager to your spaces. With this module you can create and assign tasks to users in spaces.'),
        ),
    ),
    'contentModels' => array('Task'),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'translation',
    'class' => 'application.modules.translation.TranslationModule',
    'title' => Yii::t('TranslationModule.base', 'Translation manager'),
    'description' => Yii::t('TranslationModule.base', 'Simple translation manager.'),
    'import' => array(
        'application.modules.translation.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'AdminMenuWidget', 'event' => 'onInit', 'callback' => array('TranslationModule', 'onAdminMenuInit')),
    ),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'activity',
    'title' => Yii::t('ActivityModule.base', 'Activities'),
    'description' => Yii::t('ActivityModule.base', 'Adds the activities core module.'),
    'class' => 'application.modules_core.activity.ActivityModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.activity.*',
        'application.modules_core.activity.models.*',
        'application.modules_core.activity.widgets.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onAfterDelete', 'callback' => array('ActivityModule', 'onUserDelete')),
        array('class' => 'Space', 'event' => 'onBeforeDelete', 'callback' => array('ActivityModule', 'onSpaceDelete')),
        array('class' => 'HActiveRecordContent', 'event' => 'onBeforeDelete', 'callback' => array('ActivityModule', 'onContentDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('ActivityModule', 'onIntegrityCheck')),
    ),
    'contentModels' => array('Activity'),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'admin',
    'title' => Yii::t('AdminModule.base', 'Admin'),
    'description' => Yii::t('AdminModule.base', 'Provides general admin functions.'),
    'class' => 'application.modules_core.admin.AdminModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.admin.*',
    ),
    'events' => array(
        array('class' => 'DashboardSidebarWidget', 'event' => 'onInit', 'callback' => array('AdminModule', 'onDashboardSidebarInit')),
    ),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'comment',
    'title' => Yii::t('CommentModule.base', 'Comments'),
    'description' => Yii::t('CommentModule.base', 'Comments core module.'),
    'class' => 'application.modules_core.comment.CommentModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.comment.*',
        'application.modules_core.comment.models.*',
        'application.modules_core.comment.notifications.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('CommentModule', 'onUserDelete')),
        array('class' => 'HActiveRecordContent', 'event' => 'onBeforeDelete', 'callback' => array('CommentModule', 'onContentDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('CommentModule', 'onIntegrityCheck')),
        array('class' => 'WallEntryLinksWidget', 'event' => 'onInit', 'callback' => array('CommentModule', 'onWallEntryLinksInit')),
        array('class' => 'WallEntryAddonWidget', 'event' => 'onInit', 'callback' => array('CommentModule', 'onWallEntryAddonInit')),
    ),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'directory',
    'title' => Yii::t('DirectoryModule.base', 'Directory'),
    'description' => Yii::t('DirectoryModule.base', 'Adds an directory to the main navigation.'),
    'class' => 'application.modules_core.directory.DirectoryModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.directory.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'TopMenuWidget', 'event' => 'onInit', 'callback' => array('DirectoryModule', 'onTopMenuInit')),
    ),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'file',
    'title' => Yii::t('FileModule.base', 'File'),
    'description' => Yii::t('FileModule.base', 'Files core module.'),
    'class' => 'application.modules_core.file.FileModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.file.*',
        'application.modules_core.file.models.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'WallEntryAddonWidget', 'event' => 'onInit', 'callback' => array('FileModule', 'onWallEntryAddonInit')),
    ),
));
?><?php

// Only activate installer mode, when not installed yet
if (!Yii::app()->params['installed']) {
    Yii::app()->moduleManager->register(array(
        'id' => 'installer',
        'title' => Yii::t('InstallerModule.base', 'Installer'),
        'description' => Yii::t('InstallerModule.base', 'Initial Installer.'),
        'class' => 'application.modules_core.installer.InstallerModule',
        'isCoreModule' => true,
    ));
}
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'like',
    'title' => Yii::t('LikeModule.base', 'Likes'),
    'description' => Yii::t('LikeModule.base', 'Likes core module.'),
    'class' => 'application.modules_core.like.LikeModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.like.*',
        'application.modules_core.like.models.*',
        'application.modules_core.like.notifications.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('LikeModule', 'onUserDelete')),
        array('class' => 'HActiveRecordContent', 'event' => 'onBeforeDelete', 'callback' => array('LikeModule', 'onContentDelete')),
        array('class' => 'HActiveRecordContentAddon', 'event' => 'onBeforeDelete', 'callback' => array('LikeModule', 'onContentAddonDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('LikeModule', 'onIntegrityCheck')),
        array('class' => 'WallEntryLinksWidget', 'event' => 'onInit', 'callback' => array('LikeModule', 'onWallEntryLinksInit')),
        array('class' => 'WallEntryAddonWidget', 'event' => 'onInit', 'callback' => array('LikeModule', 'onWallEntryAddonInit')),
    ),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'notification',
    'title' => Yii::t('NotificationModule.base', 'Notification'),
    'description' => Yii::t('FeedbackModule.base', 'Basic subsystem for notifications.'),
    'class' => 'application.modules_core.notification.NotificationModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.notification.*',
        'application.modules_core.notification.models.*',
        'application.modules_core.notification.notifications.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('NotificationModule', 'onUserDelete')),
        array('class' => 'Space', 'event' => 'onBeforeDelete', 'callback' => array('NotificationModule', 'onSpaceDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('NotificationModule', 'onIntegrityCheck')),
        array('class' => 'ZCronRunner', 'event' => 'onDailyRun', 'callback' => array('NotificationModule', 'onCronDailyRun')),
    ),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'post',
    'title' => Yii::t('PostModule.base', 'Post'),
    'description' => Yii::t('PostModule.base', 'Basic subsystem for workspace/user post.'),
    'class' => 'application.modules_core.post.PostModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.post.*',
        'application.modules_core.post.models.*',
        'application.modules_core.post.notifications.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('PostModule', 'onUserDelete')),
        array('class' => 'Space', 'event' => 'onBeforeDelete', 'callback' => array('PostModule', 'onSpaceDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('PostModule', 'onIntegrityCheck')),
        array('class' => 'HSearch', 'event' => 'onRebuild', 'callback' => array('PostModule', 'onSearchRebuild')),
    ),
    'contentModels' => array('Activity'),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'space',
    'title' => Yii::t('SpaceModule.base', 'Spaces'),
    'description' => Yii::t('SpaceModule.base', 'Spaces core'),
    'class' => 'application.modules_core.space.SpaceModule',
    'import' => array(
        'application.modules_core.space.widgets.*',
        'application.modules_core.space.models.*',
        'application.modules_core.space.*',
    ),
    'isCoreModule' => true,

    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('SpaceModule', 'onUserDelete')),
        array('class' => 'HSearch', 'event' => 'onRebuild', 'callback' => array('SpaceModule', 'onSearchRebuild')),
    ),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'user',
    'title' => Yii::t('UserModule.base', 'User'),
    'description' => Yii::t('SpaceModule.base', 'Users core'),
    'class' => 'application.modules_core.user.UserModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.user.models.*',
        'application.modules_core.user.widgets.*',
        'application.modules_core.user.forms.*',
        'application.modules_core.user.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'HSearch', 'event' => 'onRebuild', 'callback' => array('UserModule', 'onSearchRebuild')),
    ),
));
?><?php

Yii::app()->moduleManager->register(array(
    'id' => 'wall',
    'title' => Yii::t('WallModule.base', 'Wall'),
    'description' => Yii::t('WallModule.base', 'Adds the wall/streaming core module.'),
    'class' => 'application.modules_core.wall.WallModule',
    'import' => array(
        'application.modules_core.wall.*',
        'application.modules_core.wall.models.*',
    ),
    'isCoreModule' => true,
    // Events to Catch 
    'events' => array(
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('WallModule', 'onIntegrityCheck')),
        array('class' => 'WallEntryControlsWidget', 'event' => 'onInit', 'callback' => array('WallModule', 'onWallEntryControlsInit')),
        array('class' => 'WallEntryAddonWidget', 'event' => 'onInit', 'callback' => array('WallModule', 'onWallEntryAddonInit')),
    ),
));
?>