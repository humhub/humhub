User
=======
##User Management

Users are managed under _Administration -> Users_.

###Overview:

The user overview lists all registered users with actions to 

- _View Profile_
- _Edit User_
- _Delete User_

> Note: You can only delete users who are not the owner of a space. You'll have to assign a new space owner in order to delte the user account.

###Add new users:

Beside the registration, users can be added by system administrators under _Administration -> Users -> Add new user_.

###Login as a specific User:

Administrators are able to login as another user by clicking the **Become this user** button within the user edit view. 

## Authentication

The way users can be registered to your network can be configured under _Administration -> Authentication_.

The following configurations are available:

 - **Allow limited access for non-authenticated user (guests)**:
This option allows guest users to view public accessible content.
Instead of showing the initial login screen, non-authenticated users will be redirected to the dashboard containing all public contents.
Furthermore, guest users will also have access to the directory.
- **Anonymous users can register:**
If this option is enabled, anonymous users will be able to send registration requests. Otherwise users can only join your network by receiving an invite or being added in the administration view.
- **Members can invite external users by email**:
If this option is enabled, all members of your network will be able to invite new users by email.
- **Require group admin approval after registration**:
If this option is enabled, new users have to be enabled by a system- or group admin in order to join the network.
Within the registration form users can either select an initial group, or are automatically assigned to a default registrationg group.
After the registration of a new user, all administrators of the given group will be informed about the new pending user request
and can either accept or decline the registration request.
- **Default user group for new users**:
This selection sets the default group for all new users. If 'None' is selected the user will be able to
select a group within the registration form (Only if the group admin approval is activated).
- **Default user idle timeout, auto-logout (in seconds, optional):**
Sets the time in seconds before inactive user sessions are closed, min value 20 seconds, default 1400seconds/24minutes.
- **Default user profile visiblity**:
Sets the default visibility of user profiles. This is only applicable when limited access for non-authenticated users is enabled.
Note: Changes of this selection will only affect new users. It can either be set to _members only_ or _members and guests_.

##User Approval

Pending users can either be approved by group managers under the _Dashboard/Account Dropdown -> User Approval_ or by administrators under _Administration -> User Approval_.

> Note: The approval process is only required if the **Require group admin approval after registration** option within the user authentication settings is enabled.
