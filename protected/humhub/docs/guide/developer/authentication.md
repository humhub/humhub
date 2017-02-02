Authentication
===============

HumHub is using <http://www.yiiframework.com/doc-2.0/ext-authclient-index.html> to provide an interface to pluggable authentication providers.


Implementing own AuthClients
----------------------------

Please see <https://github.com/yiisoft/yii2-authclient/blob/master/docs/guide/README.md> for more details.


HumHub Specific Extensions
--------------------------

Optionally your custom client can implement following interfaces to provide additional HumHub features.

### ApprovalBypass

Use interface ([[humhub\modules\user\authclient\interfaces\ApprovalBypass]]) to skip user approval for new users.

### AutoSyncUsers

The interface ([[humhub\modules\user\authclient\interfaces\AutoSyncUsers]]) the HumHub cronjob will execute 
the AuthClients syncUsers method hourly to create, update or delete existing users.

### SyncAttributes

By using the interface ([[humhub\modules\user\authclient\interfaces\SyncAttributes]]) interface - you can define user attribute (e.g. profile fields) to 
be automatically updated by the AuthClient and cannot be changed by the user.

### PrimaryClient

A user can only belongs to one ([[humhub\modules\user\authclient\interfaces\PrimaryClient]]) AuthClient.

Example for PrimaryClients:

- Standard Password Authentication
- LDAP


