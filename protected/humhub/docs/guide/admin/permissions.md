Permission defaults
===================

Permission defaults can be overwritten within the file `humhub/config/common.php` ([Advanced Configuration](advanced-configuration.md)) by means of the `defaultPermissions` params array.

The following example overwrites the default permission of `humhub\modules\mymodule\permissions\MyPermission` for the given groups.

```
return [
    'params' => [
        'defaultPermissions' => [
            'humhub\modules\mymodule\permissions\MyPermission' => [
                \humhub\modules\user\models\User::USERGROUP_SELF => \humhub\libs\BasePermission::STATE_ALLOW,
                \humhub\modules\user\models\User::USERGROUP_USER => \humhub\libs\BasePermission::STATE_ALLOW,
                \humhub\modules\user\models\User::USERGROUP_FRIEND => \humhub\libs\BasePermission::STATE_ALLOW,
                \humhub\modules\user\models\User::USERGROUP_GUEST => \humhub\libs\BasePermission::STATE_ALLOW,
            ],
        ]
    ]
]
```

User Groups
-----------

Available user groups in profile context:

| Group   | Description |
|---|---|
| \humhub\modules\user\models\User::USERGROUP_SELF  | The user himself |
| \humhub\modules\user\models\User::USERGROUP_USER  | Any registered user |
| \humhub\modules\user\models\User::USERGROUP_FRIEND  | A befriended user |
| \humhub\modules\user\models\User::USERGROUP_GUEST  | Any user, even unauthenticated if available |

Available user groups in space context:

| Group   | Description |
|---|---|
| \humhub\modules\user\models\User::USERGROUP_OWNER  | The owner of the space |
| \humhub\modules\user\models\User::USERGROUP_ADMIN  | An administrator of the space |
| \humhub\modules\user\models\User::USERGROUP_MODERATOR  | A moderator of the space |
| \humhub\modules\user\models\User::USERGROUP_MEMBER  | A member of the space |
| \humhub\modules\user\models\User::USERGROUP_USER  | Any registered user |
| \humhub\modules\user\models\User::USERGROUP_GUEST  | Any user, even unauthenticated if available |


Core Permissions
-----------------

Global context

| Class   | Description |
|---|---|
| \humhub\modules\admin\permissions\ManageGroups  | Manage user groups |
| \humhub\modules\admin\permissions\ManageModules  | Manage installed modules |
| \humhub\modules\admin\permissions\ManageSettings  | Manage administrative settings |
| \humhub\modules\admin\permissions\ManageSpaces  | Manage spaces |
| \humhub\modules\admin\permissions\ManageUsers  | Manage users |
| \humhub\modules\admin\permissions\SeeAdminInformation  | Access to administrative informations |
| \humhub\modules\space\permissions\CreatePrivateSpace  | The user is allowed to create private spaces. |
| \humhub\modules\space\permissions\CreatePublicSpace  | The user is allowed to create public spaces. |


Content Container (Space or Profile) context

| Class   | Description |
|---|---|
| \humhub\modules\user\permissions\ViewAboutPage  | Access to the profile about page. (User profile only!) |
| \humhub\modules\comment\permissions\CreateComment  | Allowed to create new comments. |
| \humhub\modules\post\permissions\CreatePost  | Allowed to create new posts. |
| \humhub\modules\topic\permissions\AddTopic  | Allowed to create new topics. |
| \humhub\modules\topic\permissions\ManageTopics  | Allowed to manage existing topics. |


Examples
--------

### Even unfriended users can access the "About" page.

```
return [
    'params' => [
        'defaultPermissions' => [
            'humhub\modules\user\permissions\ViewAboutPage' => [
                \humhub\modules\user\models\User::USERGROUP_USER => \humhub\libs\BasePermission::STATE_ALLOW,
            ],
        ]
    ]
]
```






