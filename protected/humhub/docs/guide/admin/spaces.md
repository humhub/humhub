Spaces
=======

Spaces are one of the main concepts of HumHub for seperating and categorizing content like posts, wikis and polls. A space
can be described as an independent content container with its own users, permissions and access settings.
A space can be used by space members to share content with other users.

## Add Spaces

Administrators can configure which groups are allowed to create new spaces under **Administration->Groups->Permissions**.
Please read the [Group Section](admin-groups.md) for more information about goups.
Spaces can be added by clicking _Add Space_ within the _Space Navigation_. 
A new space will require at least a space name. Futhermore you are able to define a space color, a description and advanced access settings.
The access settings consists of:

- **Join Policy**: Describes who can join the new space.
    - **Only by invite**: Only invited users can join the space.
    - **Invite and request**: Users can request a space membership beside beeing invited.
    - **Everyone can enter**: All members can join a space
- **Visibility**: Who can view the space content.
    - **Public**: All Users (Registered and Guests users). Available when allow limited access for non-authenticated users (guests) by admin settings.
    - **Public (registered only)**: All registered users in Humbub (no guests)
    - **Private**: This space is invisible for non-space-members

## Invite User

Users can be invited by clicking on the _Invite_ button within the space top menu.

## Approve New User

New user requests can be viewed, declinded or approved under _Space Settings -> Members -> Pending Approvals_

## Pending Invites

Pending Invites can be viewed and rejected under _Space Settings -> Members -> Pending Invites_

## Manage Space Members

All members of a Space can be viewed under _Space Settings -> Members_. In this view space administrators are able to remove members
and assign a specfic space group to members.

## Manage Space Permission

Space Permissions can be configured under _Space Settings -> Members -> Permissions.
The permissions can be assigned to specific space groups which can either be assigned
to an user in the _Members_ configuration or are assigned by default (guests/normal user)
The available space groups are:

- Owner: The owner of this space (the owner can be assigned by the founder of the group)
- Member: A simple member of the space 
- Administrator: The space administrator
- Moderator: Space moderator
- User: A non-member of the space
- Guest: A non-authenticated user

The following permissions can be given to a specific group:

- Create comment: Allows the user to add comments
- Manage content: Can manage (e.g. archive, pin or delete) arbitrary content
- Create public content: Allows the user to create public content
- Create post: Allows the user to create posts
- Invite users: Allows the user to invite new members to the space


