Permissions
========

Permissions in HumHub are used to grant users access to specific areas or functions of your network.

![Notification Class Diagram](images/permissions.jpg)

##Use Cases

 - Group A needs permission to manage the users of the network.
 - Moderators of Space A need write permissions for Module X.
 - User A only allows friend users to send him messages.

To achieve the permission management in such cases, HumHub provides an own group and contentcontainer level permission system.

There are two different types of permission: [[humhub\modules\user\models\GroupPermission]] and [[humhub\modules\content\models\ContentContainerPermission]].

## Verifying permissions

Permissions are verified by means of a [[humhub\modules\user\components\PermissionManager]]. 
There are two types of [[humhub\modules\user\components\PermissionManager|PermissionManager]], one for verifying [[humhub\modules\user\models\GroupPermission|GroupPermissions]]
and one for [[humhub\modules\content\models\ContentContainerPermission|ContentContainerPermissions]].

## Group Permissions

[[humhub\modules\user\models\GroupPermission|GroupPermissions]] are system wide permissions which can be assigned to system groups (Administration -> Users -> Groups).

Example of [[humhub\modules\user\models\GroupPermission|GroupPermissions]] are

 - [[humhub\modules\admin\permissions\ManageUsers]] - Permission to access the global user management section.
 - [[humhub\modules\admin\permissions\ManageGroups]] - Permission to access the global user group section.
 - [[humhub\modules\space\permissions\CreatePublicSpace]] - Permission to create public spaces.

### Verify Group Permissions

Group permissions of the current user can be verified by calling [[humhub\modules\user\components\User::can()]]:

```php
// Note that we are using the user component and not the user model here!
Yii::$app->user->can(new MyPermission());

// or
Yii::$app->user->getPermissionManager()->can(new MyPermission());  

// or
// Note that you can leave the subject parameter if you want to verify against the currently logged in user model.
$permissionManager = new PermissionManager(['subject' => $myUserModel]);
$permissionManager->can(new MyPermission());
```

## Content Container Permissions

[[humhub\modules\content\models\ContentContainerPermission|ContentContainerPermissions]] are container (Space/User) specific permissions and can be assigned to 
so-called user-groups.

User user-groups:
    - User::USERGROUP_SELF: The permission container is the user instance itself.
    - User::USERGROUP_FRIEND: The permission container is a friend of the user.
    - User::USERGROUP_USER: The user is just a network member, but does not have any specific relationship with the permission container.
    - User::USERGROUP_GUEST: The user is a guest user and therefore has no relationship with the permission container.

Space user-groups:
    - Space::USERGROUP_OWNER_ User is the owner of the space.
    - Space::USERGROUP_ADMIN: User is member of the space administrator group.
    - Space::USERGROUP_MODERATOR: User is member of the  space moderator group.
    - Space::USERGROUP_MEMBER: User is a simple member of the space.
    - Space::USERGROUP_USER: User is not a member of the space but a member of the network.
    - Space::USERGROUP_GUEST: User is not a member of the space nor a member of the network.

Example of [[humhub\modules\content\models\ContentContainerPermission|ContentContainerPermissions]] are

 - [[humhub\modules\space\permissions\InviteUsers]] - Permission to invite users to a space.
 - [[humhub\modules\mail\permissions\SendMail]] - Allows/Disallows other users to send messages.
 - [[humhub\modules\content\permissions\ManageContent]] - Permission to archive, pin to top or delete content within a space.

### Verify ContentContainer Permissions

```php
// check if the current user is allowed to send messages to user A
$userA = User::findOne([...]);
$userA->can(new SendMail());

// check if the current user is allowed to manage content in spaceA
$spaceA->can(new ManageContent());

// or
// Note the 'all' parameter is used in this case to require all given Permissions to be verified successfully instead of only one.
$permissionManager = new ContentContainerPermissionManager(['subject' => $myUserModel, 'contentContainer' => '$mySpace']);
$permissionManager->can([new MyPermissionA, new MyPermissionB], ['all' => true]);
```

## Custom Permissions

All permission classes are derived from [[humhub\libs\BasePermission]] and should reside in the 'permissions' directory of your module. 
A [[humhub\libs\BasePermission]] subclass should at least overwrite the following attributes:

 - [[humhub\libs\BasePermission::$id|BasePermission::$id]] - A unique permission id.
 - [[humhub\libs\BasePermission::$moduleId|BasePermission::$moduleId]] - The moduleId this Permission belongs to.
 - [[humhub\libs\BasePermission::$title|BasePermission::$title]] - Permission title used to display the permission.
 - [[humhub\libs\BasePermission::$description|BasePermission::$description]] - Short description of the permission.

### Default State

By default a permission is only granted if either the [[humhub\libs\BasePermission::$defaultState|BasePermission::$defaultState]] is set to [[humhub\libs\BasePermission::$STATE_ALLOW|BasePermission::$STATE_ALLOW]]
or if the given group is contained in the [[humhub\libs\BasePermission::$defaultAllowedGroups|BasePermission::$defaultAllowedGroups]] array.

The default state of a group can either be overwritten by setting a group state in the database

```php
// This is normally handled by the permission settings grid.
$user->getPermissionManager()->setGroupState(User::USERGROUP_USER, new SendMail(), BasePermission::STATE_ALLOW);
```

or by overwriting the default behaviour

```php
return [
...
    'params' => [
        // Grant SendMail permission for group User::USERGROUP_FRIEND
        'defaultPermissions' => [
            'humhub\modules\mail\permissions\SendMail' => [
                  'u_friend' => 1
            ]
        ]
    ],
...
];
```

### Fixed Groups

The defaultstate of a group can be fixated by overwriting the [[humhub\libs\BasePermission::$fixedGroups|BasePermission::$fixedGroups]] array within your permission class.
This will disable the edit function of the given groups, which can be used for security reasons.

By default the followng space user-groups are fixed:

- Space::USERGROUP_GUEST
- Space::USERGROUP_OWNER
- Space::USERGROUP_ADMIN

## Edit Permissions

If you plan to make your custom permissions editable, you have to return an array of all your module permissions within the 
[[humhub\components\Module::getPermissions()|Module::getPermissions()]] method of your Module.php. Your permissions will be added automatically to 
the permission grid of your content-container or to the global permission settings in case of group level permissions.

```php
public function getPermissions($contentContainer = null)
{
    if ($contentContainer instanceof Space) {
        return [
            new permissions\MySpacePermission()
        ];
    } elseif ($contentContainer instanceof User) {
        return [];
    }

    return [
        new permissions\MyGroupPermission()
    ];
}
```

## Controller Access Permission

To restrict the access to a controller or specific controller actions a controller can use the [[humhub\components\behaviors\AccessControl]] behaviour.

The following example shows a controller which restricts the access of the 'secret' action.


```php
class SpecialController extends Controller
{
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'rules' => [
                    ['permissions' => SpecialPermission::className(), 'actions' => ['secret']]
                ];
            ]
        ];
    }

    public function actionIndex()
    {
        ...
    }

    // Will only be allowed for users with SpecialPermission
    public function actionSecret()
    {
        ...
    }
}
```

In the following, we'll show some more use cases for the [[humhub\components\behaviors\AccessControl|AccessControl]]

```php

// Allow only system administrators
return [
    'acl' => [
        'class' => \humhub\components\behaviors\AccessControl::className(),
        'adminOnly' => true
    ]
];

// Allow guest access for index actions
return [
    'acl' => [
        'class' => \humhub\components\behaviors\AccessControl::className(),
        'guestAllowedActions' => ['index']
        'rules' => [
            ['permissions' => SpecialPermission::className(), 'actions' => ['secret']]
        ];
    ]
];

// Combined rules: Every action is only granted for users with SpecialPermission except 'secret' action, which is accessible by SpecialPermission and SpecialAdminPermission users.
return [
    'acl' => [
        'class' => \humhub\components\behaviors\AccessControl::className(),
        'guestAllowedActions' => ['index']
        'rules' => [
            ['permissions' => SpecialPermission::className()]
            ['permissions' => [SpecialPermission::className(), SpecialAdminPermission::className()], 'actions' => ['secret']]
        ];
    ]
];

```