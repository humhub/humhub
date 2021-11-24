1.10.0-beta.3 (November 12, 2021)
---------------------------------
- Enh #5437: Tests with MySQL Galera compatibility
- Fix #5427: Fix deep comment link with enabled caching
- Enh #5435: Allow non modal links in header counter
- Enh #5436: Better usage of UserPicker in Form Definition


1.10.0-beta.2 (November 12, 2021)
---------------------------------
- Enh #5403: Confirmation before close a not saved modal form
- Fix #5401: Fix profile field value result type 
- Fix #5402: Fix mentioning search in comment content
- Enh #5418: Allow to detach file from simple ActiveRecord


1.10.0-beta.1 (October 27, 2021)
--------------------------------
- Enh #4399: Direct deep links to comments and highlighting
- Enh #4242: More failsafe module loading when reading module config
- Enh #5197: Default `.htaccess` - Remove `Options +FollowSymLinks` 
- Enh #4495: Allow locking comments per content
- Enh #3688: Use `Image` widget in user list
- Enh #5194: Confirm leave page for Post & Comment forms
- Enh #5188: People/Spaces: Endless Scrolling
- Enh #5216: Separate View document button
- Enh #5229: Use `RichTextField` for user approval messages
- Enh #100: Allow additional toggle for elements with context menu
- Enh #5170: `UserPicker`: Allow zero as min input size
- Enh #4133: Backup a content of `RichTextEditor`
- Enh #100: Extend upload buttons to use a paste zone
- Enh #5256: Limit uploading profile images (Thanks to @tuhin1729 for discovering the issue.) 
- Enh #5257: Delete old unread notifications of inactive users
- Fix #5143: Unlimited page size for profile fields
- Enh #5269: Allow adding new item on ui selector
- Enh #5005: Possibility to invite a registered user to a space by email
- Enh #3546: Sign in back from impersonate mode
- Fix #5282: On account creation, registration form has HTML tag set with English language
- Enh #5280: Allow to set the number of contents from which `Show {i} more.` appears in the stream
- Enh #5303: Unassigned files are only accessibly for creator 
- Enh #5293: Added File History API for versioning
- Enh #4399: Changed default `@warning` color to `#FC4A64`
- Enh #5302: Improve checkbox widget ContentVisibilitySelect 
- Enh #5151: ContentContainer scoped URL Rules
- Enh #5094: Reflect and reload Stream filters by URL
- Enh $4879: Refactoring of `Followable` behavior
- Enh $4879: Added supported of "protected" module groups
- Enh #5330: Added option to hide "Spaces" top menu item
- Enh #5080: Show available module updates directly in admin menu
- Fix #5331: Fix js error on pjax open "Directory" page
- Enh #5333: Module's events handlers registration: check if method exists
- Enh #5300: Blocking users for space
- Enh #5347: Caching added for group permissions (reduced db queries)
- Enh #5349: Archived Spaces on Spaces directory
- Enh #4945: Collapsible-fields now accessible by tab and enable/closable by keypress, check-boxes now focusable
- Enh #5354: Space - pending invites and approvals: add the image of the users
- Enh #5361: Optimize People directory details query
- Enh #5357: SpaceChooser - lazy load added, widget refactoring.
- Fix #5360: Mentioning search fails apostrophe in a user's displayName
- Fix #5359: Removed encoding of user's name in UserPicker
- Enh #5363: Optimize duplicated SQL queries on profile edit
- Enh #5362: Optimize getting of ContentContainer tags
- Fix #5386: Fixed empty stream in archived space
