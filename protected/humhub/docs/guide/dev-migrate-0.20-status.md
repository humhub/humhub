[Back to 0.20 Migration](dev-migrate-0.20.md)

# HumHub 0.20 - Status

## Open 

- Theming
	- Themed Image files User/Space Default Profile Image
	- Notification/Activity Mail Views
- Integritychecker
- Caching
	- HSetting
	- UserModel: CanApproveUsers
- Url Rewriting (User)
- LDAP 

## Bugs / ToDos / To Improve

- Add ModuleId to Notifications
- Use own Namespaces for Modules not module/
- Delete config.php Cache on Error / Don't config Cache on DEV!
- Reimplement/Check Access Controls

- Installer
	- cookieValidationKey Installer?
	- Birthday Field not populated

- Check Delete Related Record
- Modal Confirm doesn't disappear
- Comment in new Window Mode / Like Link in Modules
- Use AssetBundels
- Check Timezone
- Check UserList Ajax Pagination
- Check how to handle unapproved/disabled users (Directory, Spaces)
- Test Paginations
	- Check/Fix Ajax Link Pager at like show users
- CSRF
- Registration Process
	- When Invited to a space (Notification & Co.)
- Check unapproved users (Space, etc.)
- Check complexer Migrations
- Translation
	- try regenerate files
	- fix translation categories for changed FileNames

## Modules

### Migrated

- Wiki
- Calendar
- Custom Pages
- Mail
- Polls
- Tasks

### Open

- BreakingNews
- NewMembers
- Birthday
- MostActiveUsers
- Notes
- SMS

- Meeting
- Translation
- ReportContent
- LinkList
- Updater
- CV