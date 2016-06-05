User
=======
##User Management

Users are managed under **Administration -> Users**.

###Overview:

The _Overview_ lists all registrated users with actions to 

- _View Profile_
- _Edit User_
- _Delete User_

> Note: You can only delete users which are not owner of a space. You'll have to assign a new space-owner in the space-settings before beeing able to delte the user account.

###Add new users:

Beside the registration of a new user, users can be added by system administrators under _Administration -> Users -> Add new user_.

###Login as a specific User:

Administrators are able to login as other users by clicking on **Become this user** within the user edit view. 

## Authentication

The way users can be registered to your HumHub instance is configurable under **Administration -> Authentication**.
The following configurations are available:

 - **Allow limited access for non-authenticated user (guests)**:
Guest users are non-authenticated users and are able to view public accessible content if this option is enabled. 
Instead of showing an initial Login modal, non-authenticated users are able to access the directory and dashboard and view public content of spaces.
- **Anonymous users can register:**
If this checkbox is enabled anonymous users will be able to send registration requests to the system.
- **Members can invite external users by email**:
If this checkbox is enabled all members will be able to invite new users by email.
- **Require group admin approval after registration**:
This checkbox will either add an additional group dropdown selection to the registration form,
or automatically add the user to a default group. After the registration, all admins of the given group will be informed about a new pending user request
and have the possibility to accept or decline the registration request.
- **Default user group for new users**:
With this selection you can set the default group for all new users. If 'None' is selected the user will be able to
select an group while registration (If the group admin approval is activated).
- **Default user idle timeout, auto-logout (in seconds, optional):**
Sets the time in seconds before inactive users session is closed, min value 20 seconds, default 1400seconds/24minutes.
- **Default user profile visiblity**:
Sets the default visibility of user profiles. This is only applicable when limited access for non-authenticated users is enabled.
Changes of this selection will only affect new users. It can either be set to _members only_ or _members and guests_.

##User Approval

Pending users can either be approved by group managers under the **Dashboard/Account Dropdown -> User Approval** or by Administrators under **Administration -> User Approval**.

> Note: The approval process is only needed if the **Require group admin approval after registration** checkbox within the user authentication settings is enabled.
