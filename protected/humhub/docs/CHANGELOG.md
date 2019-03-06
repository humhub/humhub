HumHub Change Log
=================

1.3.11  (March 06, 2019)
---------------------------
- Fix: Disabled module notification category visible in notification settings.
- Enh: Added `ModuleManager::getEnabledModules()`
- Enh: `LikeAsset` is now part of `AppAsset` and does not need further registration
- Fix (CVE-2019-9093) and (CVE-2019-9094): Reflective XSS in file post upload and cfiles upload (thanks to **Rubal Jain** for testing and reporting)
- Enh: Added further upload file name validation
- Enh: Added `ContentContainerModuleManager::flushCache()`
- Fix: Bootstrap modal blocks richtext prompt focus
- Fix: Richtext images with dimension setting not attached
- Fix: `Stream::renderEntry()` overwrite does not work
- Fix: Removed markdown line breaks from richtext preview
- Fix: WallEntry of global content throws error
- Fix: `ActivityAsset` does not depend on `StreamAsset`
- Fix: Uploaded png preview files lose transparency
- Fix: Modal options `backdrop` and `keyboard` deactivation has no effect


1.3.10  (February 22, 2019)
---------------------------
- Fix: Removed ContentTag logging in search update
- Fix #2567 No results in directory search containing single quote ( ' )
- Fix #3468 Private space stream contains public content filter 
- Fix #3473 Captcha validation breaks invite by mail
- Enh: `data-action-confirm` now works on non action based links in combination with `data-action-method`
- Enh: `grunt test` now uses the composer codeception version instead of a global executable
- Enh: `grunt test` supports an additional `--env` option in order to set the codeception environment
- Fix: Absolute url generation in tests not working
- Enh: Added `HumHubHelper:fetchinviteToken()` in order to fetch invite tokens from emails in functional tests
- Fix: Added "utf8mb4" character set support to database requirements
- Fix: Finnish language name in language dropdown


1.3.9  (February 13, 2019)
--------------------------

- Fix: StreamSuppressedQuery with limit = 2 throws query not executed exception
- Fix #3378: Update user in search index when group memberships changes
- Fix: Space un-archived activity view path broken
- Enh: Accepting module README.md files in 'docs' directory
- Fix: Include user profile posts option in dashboard stream broken
- Fix: Check SoftDeleted user state in ControllerAccess
- Fix: Removed database charset configuration in dynamic config
- Fix: User soft deletion membership cache overwrite
- Fix #3422 Stream suppressed loading logic loads unnecessary stream entries
- Fix: "Back to home" button in registration broken with user approvals and guest mode activated
- Fix #1683 #553: Added link to show/edit users awaiting admin approval
- Fix: TextFilterInput uses `keypress` event instead of `keydown`
- Fix #3452: Soft deleted user remain in other users as friends
- Fix #3170: Wrong cancel invitation mail handling
- Enh: Added `humhub\modules\space\models\Membership::isCurrentUser()`
- Enh: Added `humhub\widgets\Link::post()` for `data-method="POST` requests
- Fix: Use `humhub\modules\user\components\ActiveQueryUser::active()` on UserPicker fillquery by default
- Added `relativeUrl` to notification view parameter
- Fix #3335: Queue migration broken on some database configurations
- Enh: Added new admin setting "Include captcha in registration form"
- Enh: Added contentTags to the search index


1.3.8  (December 10, 2018)
---------------------------

- Fix #3359: Weekly summary e-mails are not sent in default configuration
- Fix #3365: Legacy richtext emojis not parsed in richtext preview
- Fix: Friendship button adds additional spaces
- Fix: SpaceController::actionHome throws 403 Http error for guests
- Enh: New `humhub\modules\user\components\User::EVENT_BEFORE_SWITCH_IDENTITY`
- Fix: Administration menu item visible after user impersonation
- Enh: Added PermissionManager findUsersByPermission feature


1.3.7  (October 23, 2018)
---------------------------

- Enh: Added maximum username length & maximum/minimum space url length (rekollekt)
- Fix: Error message during database installation
- Enh: "Powered by" message handling by widget
- Enh: Add less options for mail font url/family (@rekollekt)
- Fix: Fixed typo in space (un-)archived activities
- Enh: Removed ErrorEvent which will be removed in yii-queue 3.0 (@acs-ferreira)
- Enh: Added config option to remove "user profile posts" entry from directory navigation
- Fix #2912: Deleting single stream item does not reload stream
- Fix: Updated blueimp/jQuery-File-Upload to 2.94.1


1.3.6  (October 11, 2018)
---------------------------

- Fix: Richtext loses mark state
- Fix: New comment scroll behavior not used in edit comment
- Chng: Updated `humhub-prosemirror-richtext` to v1.0.12
- Fix #3322: Disabled users still receive emails notifications


1.3.5  (October 10, 2018)
---------------------------

- Fix: Serialization of notifications without originator fails
- Fix: Hide unapproved member activities
- Fix #3313: Unable to deny invitation to private space
- Fix: Added missing `parent::init()` to `humhub\modules\stream\widgets\StreamViewer`
- Fix: Added PHP GD extension to the requirements (docs and selftest)
- Fix: Comment edit triggers new activity
- Fix: Fixed typo from `MailSummary::INTERVAL_HOURY` to `MailSummary::INTERVAL_HOURLY`
- Enh: Added `humhub.modules.ui.filter.TextInput` and related `humhub\modules\ui\filter\widgets\TextFilterInput`
- Enh: Enable `data-action-keypress` by default
- Enh: Added `preventDefault` argument to `humhub.modules.action.bindAction` to disable prevent default behaviour for action events
- Fix: Fix bootstrap-datepicker.en.min.js 404 (Not Found)
- Fix: Comment form files not cleared
- Enh: Added `humhub\modules\file\models\isAssignedTo($record)` argument in order to check if a File is attached to a specific record
- Fix: rich-text mobile view wrong min-height calculation
- Fix #3314: layout container width differences
- Fix #3315: Exception on first login with ldap
- Fix comment scroll overflows button
- Fix widgetAction events case issue
- Added `humhub.modules.util.string.capitalizeFirstLetter` and `lowerCaseFirstLetter`


1.3.4  (September 25, 2018)
---------------------------

- Fix: Theme parent lookup cache causes installer crash


1.3.3  (September 24, 2018)
---------------------------

- Fix: img overflow in markdown view
- Enh: Added console command to list and change themes
- Enh: Improved theme parent lookup performance
- Enh: Added auto file attachment in `humhub\modules\content\widgets\richtext\ProsemirrorRichTextProcessor`
- Fix: z-index issue with fixed richtext menu
- Fix #3294: space picker encoding
- Chng: Prevent `ActiveRecord::save()` call in `humhub\modules\file\components\FileManager::attach` 
- Fix: Added additional notification validation
- Fix: Notification previews contains new line
- Enh: Allow urls in array form in homeUrl configuration
- Fix: Javascript `humhub.modules.util.object.extend` not working on older Safari version
- Enh: Enable usage of `humhub\modules\content\widgets\PermaLink` outside of `humhub.modules.content.Content` components.
- Fix #3302 smiley are not render into last activity module and email 
- Fix: Space head count includes disabled user
- Fix: Broken picker image alignment (acs-ferreira)
- Fix: File handling in upgrade path between 1.0.x and 1.3.x 
- Chng: Updated `humhub-prosemirror-richtext` to v1.0.10
- Fix: File handling in upgrade path between 1.0.x and 1.3.x 


1.3.2  (September 4, 2018)
--------------------------

- Fix #3241: Profile header space count invalid
- Fix: Disabled Notification E-Mails for installation sample contents
- Fix: No e-mail summary immediately after installation
- Enh: Added queuing for search updates of commments
- Enh: Added queue clear option at Administration - Information
- Enh: Improved support of languages unsupported Yii2 
- Enh: Added Amharic language support
- Enh: Added Finnish language support
- Enh: Added dashboard warning for admins if cron jobs not working
- Fix: Queue worker problem with spaces in PHP binary path
- Fix: Comment buttons overlap editor text
- Enh: Added windows support for grunt tasks
- Enh: Added `grunt test-server` and `grunt test`
- Chng: `humhub\modules\content\models\Content` now implements `humhub\modules\content\interfaces\ContentOwner`
- Fix: Target container not available in `humhub\modules\content\components\ContentActiveRecord:afterMove()`
- Chng: `humhub\modules\topic\models\Topic::attach` now accepts `humhub\modules\content\interfaces\ContentOwner` instances
- Fix: Richtext without focusMenu on small devices overlaps previous sibling
- Enh: Added random default color to `humhub\modules\ui\form\widgets\ColorPicker`
- Chng: `humhub\modules\content\models\ContentTag:deleteAll()` and `humhub\modules\content\models\ContentTag:findAll()` now respect the tag type condition by default- Fix: Space admin user remove broken
- Fix: Space admin user remove broken
- Fix: Invalid "Member since" date in space administration
- Fix: Suprressed stream entry button not rendered.
- Fix: Author stream filter not working.
- Chng: Use of relative urls in richtext files/images
- Fix: Permalink better handling of deleted content
- Fix: Activity exception on integrity check
- Fix: Ensure profile field "internal name" contains at least one character
- Fix: Do not allow user self deletion via admin section
- Fix: Refactored ActiveQueryContent::contentTag method, added method parameter
- Fix: Richtext prompt not removed on pjax load
- Enh: Added `humhub\modules\content\widgets\richtext\ProsemirrorRichText::parseOutput` for pre render parsing
- Enh: Added `humhub.modules.file.getFileUrl` and `humhub.modules.file.filterFileUrl` for file guid based url handling
- Fix: `humhub\modules\space\modules\manage\components\Controller` only accessible by system admins
- Enh: Added scheme parameter to Content::getUrl method
- Enh: Added `ui.richtext.prosemirror.config.link.validate` to intercept link input validation
- Chng: Updated `humhub.prosemirror` dependency to 1.0.9


1.3.1  (August 7, 2018)
-----------------------

> Warning: Please read the [Update Guide](http://docs.humhub.org/admin-updating-130.html) before updating from 1.2.x!

> Note: A full list of changes is available here: [Changelog](https://github.com/humhub/humhub/blob/v1.3.1/protected/humhub/docs/CHANGELOG.md)

- Fix: Complete table cache flush on profile field update
- Fix: Improved handling of inconsistent notifications
- Fix: Vietnamese translation syntax error


1.3.0  (August 3, 2018)
-----------------------

> Warning: Please read the [Update Guide](http://docs.humhub.org/admin-updating-130.html) before updating!

- Enh: Added `PolymorphicRelation::strict` to throw exceptions when accessing invalid polymorphic relations
- Fix: Mail summaries not correctly triggered by cron
- Fix: Click to topics lead on streams without topic filter throws javascript error. (https://github.com/humhub/humhub-modules-polls/issues/49)
- Fix: Existing files may cause NULL pointer exception
- Fix: Newly created profile fields cannot be updated
- Enh: Added `AbstractRichTextEditor::layout` in order to change richtext style
- Enh: Added `block` type RichText for non focus menu style


1.3.0-beta.3  (July 30, 2018)
-----------------------------

- Fix: prevent user serialization for SocialActivity
- Fix: wrong return value for `Content::move()`
- Fix: space archive activity wrong originator assignment
- Fix: suppress "unable to determine dataType" error for aborted xhr requests
- Enh: added `FunctionalTester::loginBySpaceUserGroup()` and `FunctionalTest::assertSpaceAccessStatus()` for ACL testing
- Fix #2721 delete space button not visible for system admin
- Enh: added `humhub\modules\space\behaviors\SpaceModelMembership::canDelete()`
- Fix #3221: Popover Space title "&" to "&amp;"
- Fix invalid translation syntax used in croatian language
- Enh added highligh.js as ui addition
- Fix: ui.addition.applyTo with filter ignores first filter index
- Fix: introduction tour not working
- Chng: Moved static js dependencies from `static/resources` into `static/js`
- Fix: Comment edit cancel edit context item not shown
- Fix  #2700: Prevent GroupManager access to system admin group management
- Enh: Styled user deletion view
- Fixed: Space and User Admin Filterbar padding
- Fix: Source serialization of Notification ActiveJob
- Enh: Added 'requireSource' & 'requireOriginator' flags in SocialActivities


1.3.0-beta.2  (July 18, 2018)
-----------------------------

Please read the [Update Guide](http://docs.humhub.org/beta/admin-updating-130.html) before updating!

> If you're using the sources directly from GitHub, you need to build the required assets manually. Please see the chapter [Build production assets](http://docs.humhub.org/dev-environment.html#build-production-assets) for more details.

- Enh: Added CounterSet Widget to handle Space/Profile header statistic counts
- Fix: UI addition mutation observer interfering with new rich-text mutation logic
- Fix: ThemeLoader publishes assets on CLI requests
- Enh: Add possibility to delete an invitation [#2980](https://github.com/humhub/humhub/issues/2980)
- Enh: Moved search index tasks (add, update & delete) into asynchronous tasks
- Enh: Added search index rebuild button
- Fix #3200: wall stream scroll not working after single entry load request
- Fix added missing required validation of target space for move content feature
- Enh: Added `humhub.modules.stream.StreamState.firstRequest` in order to determine the initial request
- Fix #3204: invalid russian translation in module overview
- Fix #3169: post markdown not stripped in mails and activities
- Fix #3157: invalid use of relative space target link in MailContentEntry widget 
- Fix force invite not working on space creation
- Enh: Enable invite all instead of force membership in case force invite checkbox is not selected
- Fix: prevent MembersAdded activity when using force space membership
- Enh: added `humhub.modules.ui.picker.Picker.disable()` in order to disable/enable userpicker fields
- Enh: topic labels now redirect to space stream with active topic filter if clicked outside of space stream
- Fix: #3123: unbalanced html tags leads to broken comment after load more
- Fix: #3211: escaped html rendered on space list modal
- Fix: invalid userpicker translation syntax in czech language
- Fix: added missing layout-snippet-container class in space and profile layout
- Fix: move profile content not possible
- Fix: Stream wall scroll event not detached on pjax call
- Fix: Error thrown for empty url links in `humhub\libs\Markdown` when used in console environment
- Fix: UserUrlRule double User model import
- Fix: Skip soft delete validation
- Fix: Added user dn to ldap attributes on login
- Enh: Added Twig template engine for usage in modules
- Enh: Added id data attribute on contentcontainer links
- Fix: Wrong permission check on force invite check
- Fix: Space homepage doesn't allow custom pages on first position
- Enh: Added integrity check for notification originator
- Enh: Use of new richtext version 1.0.4 see https://github.com/humhub/humhub-prosemirror/blob/master/docs/CHANGELOG.md 
- Enh: Added max-height for post/comment/edit richtext
- Enh: Richtext style enhancements (dashed selection)
- Fix: Upload preview for comments not cleared after submit
- Fix: Profile/Space image upload not working after full page reloads
- Fix: File upload errors not handled by richtext
- Enh: Removed built and compressed assets from GitHub sources


1.3.0-beta.1  (July 4, 2018)
----------------------------

Please read the [Update Guide](http://docs.humhub.org/beta/admin-updating-130.html) before updating!

- Enh: Added file search indexing
- Enh: Updated composer.json (acs-ferreira)
- Chg: Switched from Composer FXP plugin to Asset Packagist repository
- Enh: Committed composer.lock
- Enh: Refactored ContentContainer Controller
- Chg: Added ContentContainer ModuleManager, instead of individual handling (Space/User)
- Fix: Rebind LDAP connection after successful login with administrative user
- Enh: Make utf8_mb4 as default database charset
- Enh: Moved queueing into own submodule and updated to yii2/queue extension
- Enh: Added user soft deletion without contributions
- Enh: Moved user deletion into asynchronous tasks
- Enh: Improved user grid view design (Administration, User Approval, Space Members)
- Enh: Moved SyncUsers (LDAP) and session table cleanup handling into ActiveJob
- Enh: Added Push live module driver using Redis and Node.JS
- Enh: Added tooltip option to space Image widget.
- Enh: Added `humhub.client.json` javascript util for directly receiving json instead of a Response object.
- Enh: Added `humhub.file.Upload.run()` for triggering the upload of the Upload widget.
- Chg: Moved `humhub\widgets\RichText` to `humhub\modules\content\widgets\richtext\RichText`
- Chg: Moved `humhub\widgets\RichTextField` to `humhub\modules\content\widgets\richtext\RichTextField`
- Enh: Added rich text abstraction by means of configuration parameter `richText`
- Enh: Added `humhub\modules\content\widgets\richtext\ProsemirrorRichText` as default rich text.
- Enh: Added `humhub.oembed` js module for loading oembed content
- Enh: Added `RichText::preview()` helper for minimal rich text output
- Enh: Added `RichText::output()` helper for rendering the richtext
- Enh: Added `RichText::postProcess()` for post-processing rich text content (mentionings/oembed etc.)
- Enh: Added `content` module setting `Module::$maxOembeds` for setting the maximim amount of oembeds in a richtext.
- Chg: Deprecate `humhub\modules\user\models\Mentioning::parse()` and in favor of `humhub\modules\content\widgets\richtext\RichText::postProcess()`
- Enh: Added `humhub.user.getLocale()` javascript helper for checking the user locale on client side
- Enh: Added `humhub\widgets\InputWidget::getValue()` for determining the field value
- Enh: Added `humhub.client.json` for directly receiving the json result instead of a response wrapper object
- Enh: Added option ContentContainerController to restrict container type
- Enh: Ensure valid permalinks when URL rewriting is enabled
- Fix: Birthday field refactoring (@danielkesselberg)
- Enh #2811: Added option to resend invites (@danielkesselberg)
- Enh: Added current database name to the "Administration -> Information -> Database" (githubjeka)
- Chg: Depreciated Instagram OAuthClient & removed (@Felli)
- Enh: Added random default space color on creation
- Enh: Updated to Yii 2.0.14.2
- Chg: Reduced email length to 150 chars to support utf8mb4 charset 
- Enh: Added UI core module to group UI components
- Enh: Added new IconPicker form field
- Chg: Moved form widgets from `humhub\widgets` to `humhub\modules\ui\form\widgets` (added compatibility layer)
- Enh: Added surpressed e-mail addresses configuration variable
- Chg: `Create a new one.` to `Forgot your password?` (@Felli)
- Enh/Fix: Cache Handling + File Preview Fix (@Felli)
- Enh: BaseSettingsManager allow to bunch delete settings with prefix
- Chg: Migrated view and theme components to  `humhub\modules\ui\view` package
- Enh: Improved Theme component
- Enh: Added notification for MembershipSpace by role member changes (@githubjeka)
- Enh: Added Theme cascading to reduce view overwrites
- Enh: Automatic theme stylesheet loading including parent theme stylesheets
- Chg: Moved OpenSans font to core assets
- Chg: Renamed information cronjob section to Background jobs and added queue status
- Chg: MySQL queue is now the default job queuing driver
- Enh: Add steps to using Facebook Oauth (@Felli)
- Enh: Added css `footer-nav` class for footer navigation
- Enh: Added Pin/Archived/Public wallentry icons
- Enh: Added move content behavior by means of a `humhub\modules\content\models\Movable` interface
- Enh: Added sortOrder utility `humhub\libs\Sort` 
- Enh: Added `humhub\modules\content\helpers\ContentContainerHelper` util with `ContentContainerHelper::getCurrent()`
- Enh: Added `humhub\modules\stream\helpers\StreamHelper` util with `StreamHelper::createUrl()`
- Chg: Shifted activity stream logic to `humhub\modules\activity\actions\ActivityStreamAction` and `humhub\modules\activity\controllers\StreamController`
- Chg: Added activity stream action `humhub\modules\activity\actions\ActivityStreamAction`
- Enh: Added `humhub\modules\stream\models\WallStreamQuery` class used for wall streams e.g. Space content stream
- Enh: Added `ui` core module
- Enh: Added abstract ui filters used for dynamic/extendable filter views
- Chg: New Stream and Stream Filter API
- Enh: Added `topic` content filter concept with stream integration


1.2.8 (July 3, 2018)
--------------------

- Enh: Added user email to javascript user config
- Fix: Module Assets are not republished after module update
- Enh: Added `humhub\components\ModuleManager::EVENT_BEFORE_MODULE_ENABLE` and `humhub\components\ModuleManager::EVENT_AFTER_MODULE_ENABLE` events
- Enh: Added `humhub\components\ModuleManager::EVENT_BEFORE_MODULE_DISABLE` and `humhub\components\ModuleManager::EVENT_AFTER_MODULE_DISABLE` events
- Fix: Improved ZendLucence driver error handling
- Fix #3148: Upload space picture dose not use file size setting in HumHub (acs-ferreira)
- Fix: Incorrect last visit date shown in space admin pending members view (acs-ferreira)
- Enh: Allow enable/disable modules by CLI
- Enh: Added UTC only timezone in server timezone dropdown
- Fix #3176: Integrity checker removes modules default state
- Enh: Updated translations


1.2.7 (May 23, 2018)
--------------------

- Fixed empty modal dialog response issue
- Fix #3146 invalid bootstrap.min.css link in installer
- Enh: Load `humhub\modules\content\models\ContentTagAddition` model in `humhub\modules\content\models\ContentTag::load()`
- Enh: Auto save `humhub\modules\content\models\ContentTagAddition` within `humhub\modules\content\models\ContentTag::afterSave()`
- Enh: Added `humhub\modules\content\components\ContentActiveRecord::isOwner()` to check the ownership of a content
- Enh: Make directory access configurable by `humhub\modules\directory\Module::active`, `humhub\modules\directory\Module::guestAccess`
- Enh: Added `humhub\modules\directory\permissions\AccessDirectory` permission for group level directory access
- Fixed `User `namespace issue in `humhub\modules\user\components\BaseAccountController`
- Chg: Added footer menu to account menu on small display resolutions


1.2.6  (May 14, 2018)
-----------------------

When you are using a custom theme, you may need to add the newly introduced footer navigation to your overwritten view files.
You can find a full list of the view changes here: https://github.com/humhub/humhub/commit/a1815fb61d83619ce9ca40166800b8c5dcb9d539

- Fix #3108: Fixed cronjob examples with leading zero (acs-ferreira)
- Fix: Memory leak in activity mail summary processor cron
- Fix: With enabled guest mode BaseAccountController does not redirect to login page
- Enh: Added footer navigation - FooterMenu widget
- Enh: Added HForm class events EVENT_AFTER_INIT and EVENT_BEFORE_RENDER
- Enh: Updated translations


1.2.5  (April 11, 2018)
-----------------------

When you customized or used the createCVS method of PendingRegistrationsController please 
migrate your code to SpreadsheetExport. PHPOffice is replaced by PHPSpreadsheet.

- Enh: Added BaseURL setting protocol scheme validation
- Fix #2849: ActiveQueryContent doesn't find public profile content when guest access is enabled
- Enh: Fixed username alignment in comments (@githubjeka)
- Enh: More readable WallEntryAddon links (@githubjeka)
- Fix: Documentation grammar fixes (@Felli)
- Fix: Let's Encrypt ACME Error (@Felli)
- Fix: Typo in password recovery (@acs-ferreira)
- Fix: Profile posts of friends not appears on dashboard
- Fix #2745: Yii2 2.0.13 will break the admin interface
- Enh: Allow auto detection of response dataType
- Fix #2947: Allow json success result on modalSubmit
- Enh: Disabled automatic content following on likes by default
- Enh: Improved IntegrityChecker memory usage
- Chg: `PendingRegistrationsController->createCVS` removed
- Fix: Stream image preview size not changeable
- Fix: Increased maximum e-mail address length from 45 characters to 254
- Fix: Group member search by firstname/lastname
- Enh: Added Slovene language
- Enh: Added Croatian language
- Fix: User approval, lastname field is shown twice to admins
- Fix: User model namespace issue in `humhub/modules/user/components/UrlRule`
- Enh: Raised notification over view pagination size to 20
- Enh: Added `humhub/modules/space/models/Module::flushCache()` and `humhub/modules/space/behaviours/SpaceModelModules::flushCache()` in order to flush the space module cache
- Enh: Added further `FunctionalTester` utilities
- Enh: Added Norwegian Nynorsk language
- Fix #3009: Change the Space URL raises 404

1.2.4  (December 13, 2017)
--------------------------

- Enh: Translation updates
- Fix: Added `ManageSpaces` and SystemAdmin check to `UserGroupAccessValidator`.
- Fix: Only include content with `stream_channel = default` into spacechooser update count.
- Enh: Add LinkedIn auth to login. (Felli)
- Enh: Add Twitter auth to login. (Felli)
- Enh: Add Instagram auth to login. (Felli)
- Enh: Add Twitter, LinkedIn & Instagram auth to docs (Felli)
- Enh: Make lucene search term limit configurable via `ZendLuceneSearch::$searchItemLimit`.
- Fix: Empty stream message between friends
- Enh: Improve composer-asset-plugin config (cebe)
- Enh: Added a link to the permalink from the ago text (benklop)
- Enh: Added directory group description (githubjeka)
- Enh: Added configuration option to include user profile posts in dashboard without following
- Fix: User profile sidebar disappered
- Fix: Like notification for comments not working
- Fix: Add example users to default Users group
- Fix #2851: getting model attribute value using Html::getAttributeValue()
- Fix #2844: Directory member search broken on page 2
- Fix #2702: Disable content search for guest users due to space visibility
- Fix #2806: Register process broken on some environments (Felli)

1.2.3  (October 23, 2017)
-------------------------

Important note for LDAP users: There is a new setting "ID Attribute" which should be set to clearly identify users.
Important note for Git/Composer installations: http://www.yiiframework.com/news/148/important-note-about-bower-and-the-asset-plugin/

- Fix: Readonly markdown field issue.
- Enh: Fixed registration approval/denial mails and made their default value configurable.
- Enh: Updated primary auth client interface for more flexibility
- Enh: Added LDAP ID attribute to improve user mapping
- Enh: Option to disable e-mail address requirement in User model
- Fix: Overwrite of static image in theme + added documentation section
- Fix: Account Controller exception when user is not logged in
- Fix: Exception on notification overview page when not logged in
- Enh: Added possibility to sort groups in directory
- Enh: Removed LDAP UserFilter/LoginFilter length restriction
- Fix: UTC timezone issue with `TimeZoneDropdownAddition` and added `$includeUTC` flag to `TimezoneHelper::generateList()`
- Fix: ControllerAccess json rule
- Enh: added `closable = false` as default `ModalDialog` widget setting
- Fix: trigger richtext `clear` when submitting comment.
- Fix: missing return in `FileContent::beforeValidate`
- Fix: Mentioning search with `-` not working
- Fix #2730: Mentioning search with `-` not working
- Fix: File search with suffix not working
- Enh: Added SearchAttributesEvent to improve content addon indexing (comment/file)
- Fix: Do not automatically force modal close on stream edit
- Enh: Added DurationPickerWidget
- Enh: Allow `ContentActiveRecord($contentContainer, $config)` initialization
- Fix: `WallEntry::addControl` with simple array options
- Enh: Added `$scheme` Argument to `DownloadFileHandler::getUrl()`
- Fix: Clear UserModule cache after save/delete
- Fix: Prevent Integrity check failures.
- Enh: Added default open content as modal action
- Enh: Added possibility to add attachments in Notification MailTarget
- Enh: Added surpressSendToOriginator Notification option
- Chg: #2745 Removed `GroupPermission::instance()` for yii 2.0.13 compatibility
- Enh: Added `MobileTargetProvider` abstraction for mobile push notifications
- Enh: Added `humhub:notification:updateCount` js event
- Enh: Show space administrators and moderators in member snippet
- Fix: `humhub\modules\live\Module::getLegitimateContentContainerIds` behaviour with friendship module enabled
- Enh: Added `BaseNotification:priority` to mark high priority notifications
- Enh: Added new `User::isVisible` and `ActiveQueryUser::visible` methods
- Fix: MarkdownEditor cursor position after inserting file/
- Fix: Make sure own profile content is always visible to user
- Fix #2501: Do not try to embed Youtube unauthorized videos (acs-ferreira)
- Fix #2613: Wrong username encoding with pretty url (githubjeka)
- Fix #2791, #2749: Force private join policy on private spaces + non changeable post visibility
- Fix wrong Comment date issue in notification mails
- Enh: Added `data-file-*` attributes to download links, for beeing able to intercept file downloads
- Enh: Added `apple-mobile-web-app-*` and `mobile-web-app-capable` meta tags to `head.php`
- Fix #2783: E-Mail notification link broken when guest mode is enabled (Buliwyfa)
- Enh: Added `ContentActiveRecord::silentContentCreation` for disabling ContentCreated Activity/Notification on ContentActiveRecord level
- Enh: Now the `NewContent` live event is always fired with `sourceClass` and `sourceId` information and a `silent` flag for silent content creations

1.2.2  (August 2, 2017)
--------------------------------
- Enh: Allow returning class names beside BasePermission instances in `Module::getPermissions()`
- Enh: Increase profile image size to 800px.
- Fix #2644 overlapping popup preview image after increasing preview image size (hagalaz)
- Fix: Button widget child class static instantiation not working 
- Fix: ModalButton instantiation and added ModalButton::close()
- Fix: Respect `max_file_uploads` setting in UploadInput widget
- Enh: Include `kartik-v/yii2-widgets`
- Enh: Added `getAccessRules()` to `humhub/components/Controller`
- Fix: AccessControl action restriction bug
- Fix: `ModuleAutoLoader` exceptions not logged
- Fix: `I18N` formatter user timezone not set
- Enh: Automatically set space default visibility in `Content::setContainer()`
- Fix: Fixed ContentContainerSettingManager caching issue if space/user id are equal
- Enh: Use of select2 dropdown for time zone selections
- Fix: Bypass AccessControl behavior in installer
- Fix: Use of JS-Widget internal event object instead of node
- Enh: Added `Formatter::getDateTimePattern()` and `Formatter::isShowMeridiem()`
- Fix: Set formatter locale in I18N when changing locale
- Enh: Added `$hideInStream` flag for upload component/action for changing `show_in_stream` file flag
- Enh: Added `$showInStream` flag for `FilePreview` widget to only include files with certain `show_in_stream` flag
- Enh: Added `FileManager::findStreamFiles()` for querying files with either given `show_in_stream = 1` or `show_in_stream = 0` flag.
- Enh: Added `humhub\widgets\Tabs` and `humhub\widgets\SettingsTabs` with view type tab support
- Enh: Added new `MarkdownField` input widget which as replacement of deprecated `MarkdownEditor`
- Fix: Fixed markdown file upload pjax issue
- Fix: Removed `display: table-cell` from markdown image css to enable inline images
- Enh: Added `humhub/widgts/Button::userPickerSelfSelect()` for creating self select button for userpickers.
- Enh: Added `humhub/widgts/Link::withAction()` for creating action based links
- Enh: Added `SelectTimeZoneDropdown` widget
- Enh: Added `Modal::closable` in order to respect `backdrop` and `keyboard` data setting of `Modal` and `ModalDialog` widget
- Enh: Avoid cutting oembed entry in stream if it's the first part of a richtext 
- Enh: Added `humhub/widgets/TimePicker` widget
- Enh: Added `DbDateValidator::timeZone` for setting input time zone
- Enh: Additional WallEntry settings: `$jsWidget`, `$addonOptions`, `$controlsOptions`, `$renderControls`, `$renderAddons`
- Enh: Added possibility to overwrite WallEntry settings in `humhub/stream/actions/Stream::renderEntry()`
- Enh: Added `ShowFiles::preview` and `ShowFiles::active` flag
- Enh: Allow `$adminOnly` for User base ContentContainerController Controller
- Enh: Added `ContentContainerActiveRecord::getDefaultContentVisibility()` and `User::getDefaultContentVisibility()`
- Enh: Added automatic Notification Class loading by convention. No need to overwrite `Module::getNotifications()`
- Enh: Added `ContentActiveRecord::getIcon()` for adding an badge icon to WallEntry content type badge
- Enh: Added `ContentActiveRecord::getLabels()` for managing WallEntry labels (badges)
- Enh: Added `Label` widget for creating sortable labels
- Fix: Reset modal dialog size + add `size` option
- Enh: Added `size` option `ui.modal.Modal.set()`
- Enh: Use `ContentActiveRecord::getUrl()` for content perma links (if given)
- Enh: Added `ContentTag` concept for creating content categories/filter on module level
- Fix: Mentioning keeps running even if previous input result was empty
- Enh: Darkened comment links for better readability
- Fix #2582 Userfollow activity click action not working
- Enh: Make space membership activities clickable
- Chg: Removed `yii2-codeception` dependency
- Chg: Added `phpoffice/phpexcel` dependency
- Enh: Added `JsWidget::fadeIn` for smooth widget initialization
- Enh: Enhanced `AccessControl` filter with `ControllerAccess` layer for better testability and flexibility
- Enh: Added `Pending Registrations` admin view with `csv`, `xlsx` support.

1.2.1 (June 17, 2017)
--------------------------------
- Fix: Invite error in french language
- Fix #2518: ActivityStreamWidget::EVENT_INIT is missed (githubjeka)
- Enh: Fixed accessibility issues in Dashboard/Login/Profile
- Fix: module beforeInit and afterInit event
- Enh: Added Registraion::EVENT_AFTER_REGISTRATION UserEvent
- Enh: Added grunt `migrate-up` and `migrate-create` task
- Enh: Added profile field type `CheckboxList`
- Fix: Fixed `ui.addition` `MutationObserver`, only apply additions to inserted nodes.
- Enh: Changed invite mail subject text
- Fix #2571: last_login not set after registration direct login 
- Enh: Always trigger dom widget events for widget `fire` until `triggerDom` is set to false
- Enh: Added `richtextPaste` event
- Enh: On search index rebuilding - use batch queries 
- Fix: `ActiveQueryContent:readable()` for guNest users missing join
- Enh: Added `ContentActiveRecord:managePermission` for changing the default write permission of ContentActiveRecord classes
- Enh: Moved all default `WallEntryControls` to `WallEntry:getContextMenu()` widget.
- Fix: Connect google OAuth under `Profile Settings  -> Connected Accounts` throws invalid redirect uri.
- Fix: Invite Users does not respect ManageUsers/ManageGroups permission
- Fix: Mail summaries sent in incorrect language
- Fix: Send button text on request space membership dialog
- Fix #2555: Friendship notification category visible even if friendship system deactivated
- Enh: Don't auto focus space chooser search on small devices
- Fix #2612: Single list item hides markers
- Fix #2558: No notification for user profile posts send
- Fixed #2560: Markdown profile field editable flag not working
- Fix: Hide also header (space, profile) counts when following system is disabled
- Fix: Perma link to space contents broken when space homepage was changed
- Fix: Properly sort language and country select by users locale
- Enh: Allow search in country profile field dropdown
- Fix: js action api empty data attribute
- Enh: Added button helper widgets `<?= Button::primary('myButton')->action('myJsAction')?>`
- Enh: Enhanced ContentActiveRecord instantiation `$model = new MyContent($space, Content::VISIBILITY_PRIVATE)`
- Fix #2625 Pjax problem with local links to files within stream
- Enh: Use of `target="_blank"` for stream links
- Fix #2594 Bug: Url with unicode in stream markdown
- Fix: Notification grouping not working
- Fix: Show more suppression entries with sort order update + equal update_at not working.
- Fix #2627: Incorrect language used in group admin user approval e-mail
- Fix #2631: Module configuration link shown for disabled modules
- Fix #2785 #2172: Added iconv PHP extension to the requirement check (leuprechtroman)


1.2.0 (April 16, 2017)
--------------------------------
- Fix: SVG file uploads broken (mime type: image/svg+xml)
- Fix: Public badge missing after create post
- Fix: Mentioning notificaiton in user not working
- Fix: Catch yii\db\Expression error for updated_at in wallentry
- Enh: Added 'client.back' js action
- Fix #2219: Overlapping summary mail content
- Fix: Wall entry layout link/text overflow
- Fix: Stream - Do not surpress if only particual contents are displayed
- Fix: GlobalModal extends base Modal widget and GlobalModal::$backdrop is false by default (githubjeka)
- Fix: Search StreamEntry options delete/editModal
- Fix: Tour popover close behaviour
- Fix: Incorrect permissions in space with guest mode

1.2.0-beta.4 (March 28, 2017)
--------------------------------
- Fix: Notification count '0' visible after click on notification link
- Fix: Default space permissions not adopted
- Fix: Use of $permission->getId() instead of $permission->id in PermissionManager (allow dynamic permission ids)
- Fix #2393: Markdown h4,h5,h6 broken
- Fix #2389: calculate max upload file size on PHP 7.1 (githubjeka)
- Fix: LDAP - Lost authclient ldap class configuration on user update
- Fix #2400: Space ownership transfer form shows wrong users
- Fix: Enable user approval without available registration groups or default group
- Fix: Activate 'User' navigation in Admin Menu "Administration -> User -> Settings"
- Enh: Improved administration user deletion view
- Enh: Added 'containerLink' HTML Helper method
- Enh: WallEntry layout layout improvements
- Fix: Default user & space module configuration lost after foreign key migration
- Fix: Respect pinned post when inserting a a new stream entry
- Fix: Show comments in modal not working
- Fix: #2374 Comment input not focused on comment link click 
- Enh: Toggle comment box
- Enh: Added global copyToClipboard
- Enh: Added "weekly" mail summary interval
- Fix: Invalid temp.css file
- Fix: Default stream sort setting not applied
- Enh: Show different login message, when registration is disabled
- Fix: Norwegian translation code for Yii messages
- Fix: Also allow comment editing by admins if content 'adminCanEditAllContent' is enabled
- Enh: Added Make Private/ Make Public link to wall entry controls
- Enh #2392: Added Latvian language
- Fix: Hide image file info setting
- Fix #2297: Failed to open stream: No such file or directory when attaching files in UploadAction

1.2.0-beta.3 (March 20, 2017)
--------------------------------
- Enh: Added Grunt tasks `build-assets`, `build-theme`, `build-search`
- Fix: Error when saving account setting permission.
- Fix: #2296 stream scroll issue for mobile webkit browsers (martinbeek)
- Fix: Added unknown upload error if server cancels upload (e.g in case of a post_max_size violation issue)
- Enh: Added warning if php max upload/post is less than the humhub setting.
- Enh: Added mp4/ogg blueimp support in post gallery
- Enh: Added global (default) notification space settings
- Enh: #2359 Use Jplayer playlist feature for post mp3
- Enh: added js module 'ui.view' for view state and utils - Changed 'ui.state.getState' to 'ui.view.getState'
- Enh: added view helper as getHeight/Width and isSmall/Medium/Normal (width) to 'ui.view' js module
- Fix: removed popover image preview from mobile
- Fix: removed target-densitydpi not supported warning
- Enh: Added Stream::renderEntry for rendering Streamentries from ContentActiveRecord
- Fix: Wallentry menu not working in search view
- Fix: Double notifications when mentioning in comments
- Enh: Raised collapse value for posts to full embeded youtube video height
- Fix: Fixed oembed post edit
- Enh: Included select2-humhub theme into the new theming
- Enh: Added select2 dropdown for language selection in account and admin settings
- Enh: Added data-ui-select2 addition for simple select2 dropdowns
- Fix: Don't apply js additions if there was no matching element
- Fix: #2336 use of invalid message key in comment notification (dutch)
- Enh: Disable user and space follow by means of module settings
- Fix: Setting of submitName and upload input name in file upload JsWidget
- Fix: Rendering of UploadButton without given id
- Enh: Added preventPopover and popoverPosition options to file preview
- Enh: Added uploadSingle option for uploads with only one file
- Fix: Missing margin of wall-entry-controls in comments
- Enh: Added alignHeight setting to js loader module
- Enh: Allow Response as arguments for modal.setDialog
- Enh: Enable modal loading events with dataType json
- Enh: Allow direct class export instead of module export in JS Modules e.g. module.export = MyClass
- Enh: Added xhr to client response instances
- Enh: Added response.header for receiving response header from xhr
- Enh: Added post action to client module for data-action-click="client.post"
- Fix: Try using options url as fallback in client calls if the action instance does not provide an url. e.g. client.post(evt, {url:...}
- Enh: Enable setting ajax dataType from trigger e.g. data-action-click="modal.load" data-action-data-type="json"
- Enh: Added action event.data for receiving action specific data options
- Enh: Added default run for JsWidget
- Enh: Added File::findByRecord for searching all attached files of a given ActiveRecord
- Fix: Only set js view state for non full page load and pjax
- Fix: Small gap on TopNav mouse hover (acs-ferreira)
- Enh: Humand readable file sizes. (acs-ferreira)
- Enh: Changed default $minInput of SpacePickerField to 2. (githubjeka)
- Fix: Error when saving "Administration -> Settings -> General" without default space. (githubjeka)
- Fix: #826: Notification status not updated right after like.
- Fix: #2316: Reinvitation by email not working
- Fix: #2314: Html helper namespace issue in Markdown.php class
- Fix: #2302: Hide file info for images on wall settings not applied.
- Fix: German translation error in Admin -> Users -> Groups -> Members -> Add Member UserPicker.
- Fix: German translation "Notify Users" placeholder too long.
- Fix: Admin group add members placeholder.
- Fix: Stream entry root not removed for content delte (poll,etc)
- Enh: Easier save feedback by using 'module.log.success('saved')'
- Fix: Admin group add members placeholder.
- Enh: Easier save feedback by using module.log.success('saved');
- Fix: Set jsWidget id when autogenerated
- Fix: Use of Html::activeLabel instead of $form->label in RichtextField
- Enh: Added pjax redirect capability to js client
- Fix: Show default error in status bar if invalid message object was provided
- Fix #2304: Users not loaded in user selection fields.
- Enh: Added User module 'displayNameCallback' attribute for custom display name formats
- Enh: Added Clipboard.js and Permalink "Copy to clipboard" link
- Enh: Validate minimum PHP version in Console Application
- Enh: Added optional ActiveRecordContent::canEdit() method for custom ACLs
- Fix: Better error handling/logging on corrupt GD Image files
- Fix #2288: Pjax breaks OAuth2 ReturnUrl
- Fix: Incorrect First name & Last name message key (githubjeka)
- Fix: Do not store complete comments with search index (helnokaly)
- Fix #2319: Run console application before installation
- Fix: Directory Knob statistics on included modules (e.g. Enterprise Edition)
- Enh: Added widget to display user profile image
- Enh: Directory view templates cleanups
- Fix: All LDAP Users have been disabled and not reenabled by hourly cronjob if ldap server not reachable.
- Enh: Cleanup authentication layout file 
- Fix: Console image converter memory limit allocation
- Enh: Added new controller init event
- Enh: Made admin base controller method "getAccessRules()" non static
- Enh: Created new ImageController for user image and banner handling
- Enh: Decreased OEmbed url max length 180chars (acs-ferreira)
- Enh: Added APCu Support
- Enh: Added ContentContainer integrity check (Daha62)
- Fix #2331: Bug image load on PHP 7.1 with dynamic memory alloc (githubjeka)
- Fix #2367: `ImageConverter::allocateMemory` uses common units(MegaBates) of memory (githubjeka)
- Fix: #2369: typo issue (Felli)
- Fix: Better notification compatiblity - mail views and enabled WebNotificationTarget
- Fix #2312: Pinned post appears twice on stream 
- Enh: Added option to show/hide deactivated user content in stream
- Enh: Allow any url route as homepage by homeUrl array application parameter
- Fix #2255: Added missing Social Account Settings menu
- Fix: Added missing file download http caching
- Enh: Added console email test command
- Enh: Added stream module defaultStreamSuppressQueryIgnore to ease overwrites
- Enh: Added 'archived' badge to archived spaces in directory

1.2.0-beta.2 (February 24, 2017)
--------------------------------
- Fix: TimeAgo locale not loaded in production mode, added AppDynamicAsset (luke-)
- Enh: Translation message rebuild and auto translated duplicates (luke-)
- Enh: Combined all directory translations into base message category (luke-)
- Enh: Added logging table cleanup job (luke-)
- Enh: Added new version check as ActiveJob (luke-)
- Enh: Moved user configuration params 'minUsernameLength' + 'adminCanChangeProfileImages' to user module class (luke-)
- Enh: Added .editorconfig code style configuration file (luke-)
- Enh: Added 'show_in_stream' column in file table to hide output in wall entries (luke-)
- Enh: Added 'renderGalleryLink' link method to PreviewImage converter (luke-)
- Fix: Search view links not working (buddh4)
- Fix: Markdown in comment layout issue (buddh4)
- Enh: humhub.ui.showMore module for cutting post text and comments (buddh4)
- Fix: Javascript issues with guest users, removed initialitation of some modules for guest user (buddh4)
- Fix: Mutliple use of same emoji in richtext.
- Enh: Use of Yiis new afterRun for humhub Widgets.
- Fix: Word break issue in markdown posts.
- Fix: Richtext with emoji only on post edit where ignored.
- Enh: Added data-action-confirm for confirming actions.
- Fix: File StorageManager setContent method broken
- Enh: Added FileHelper methods createLink & getContentContainer
- Enh: Javascript HumHub Client - better handle ajax redirects
- Enh: TopMenu / TopMenuRightStack hide content when user is not logged in without guest mode
- Enh: Added showUserName option in AccountTopMenu widget
- Enh: Added isGuestAccessEnabled method in User component
- Enh: Added flash variable (executeJavascript)to execute js on the next page load
- Enh: Added possibility to create own file handlers (edit, create, import, export)
- Enh: Added data-action-process to handle modal processes
- Enh: Added upload file event (humhub:file:created)
- Enh: Added custom file handler positions
- Enh: Moved UploadAction::getFileResponse method to FileHelper::getFileInfos
- Enh: Added JS context menu to ui.additions module
- Enh: Enhanced ContentContainer Module enable/disable
- Enh: Added client.reload for pjax and non pjax page reloads
- Enh: Added ContentContainerAsset to AppAsset
- Enh: Added editModal for editing wallentries within a modal instead of inline
- Fix: Oembed not rendered in richtext.
- Enh: Smarter show more logic - Only cut text if it overlaps the max height by a specific span.
- Enh: Added getContextMenu for defining wallentry context options. 
- Enh: Added editMode to WallEntry for allowing modal based edits.
- Fix: file-preview text overflow in HumHub theme.
- Fix #2280: Meta data (rotation) not respected for camera images (ImageMagick)
- Fix: Activity stream rendering issue on page unload.
- Enh: Optimized stream entry fade animation.
- Enh: Added $maxAttachedFiles to content module to restrict the uploaded file count of comments and posts
- Fix: Hide notification count badge if notification count is 0
- Fix: Only flush js config if not empty
- Enh: Use of theme variables in mail all views
- Fix: Submit comment with only files leads to Internal Server Error
- Fix: Prevent spacechooser message count update on own content creation
- Fix: Abort overlapping space chooser search requests
- Fix: Problem in notification group_count with only one involved user
- Fix #2257: Set module as default in space/user broken

1.2.0-beta.1 (February 08, 2017)
--------------------------------
- Enh: Moved HumHub browser icons to HumHub theme (luke-)
- Enh: Moved support css/js for older IE version into own AssetBundles (luke-)
- Enh: Moved CSRF Tag output to View renderHeader (luke-)
- Enh: Moved LayoutAddons widget from main layout to View endBody() method (luke-)
- Enh: Added PJax page loading (luke-)
- Enh: Refactored File module (luke-)
- Enh: Added yii2-imagine Extension (luke-)
- Enh: Use of blueimp image gallery (buddha)
- Enh: JS module system with build in logging/text/config features (buddha)
- Enh: JS core api under humhub namespace (buddha)
- Enh: Use of compressed assets (js,css) in production mode (buddha)
- Enh: Enhanced testability (buddha)
- Enh: Added administrative backend group permissions (buddha)
- Enh: Enhanced AccessControl filter with permission rules. (buddha)
- Enh: Splitted less files to facilitate theming. (buddha)
- Enh: Added user status bar for user feedback (buddha)
- Enh: Better UserFeedack (buttons/messages) / Replacement of old DataSaved widget (buddha)
- Enh: Overwrite default permission settings (buddha)
- Enh: SpaceChooser rewrite with following spaces and remote search (buddha)
- Enh: Modal widget rewrite.
- Enh: Enhanced Archived Space handling (buddha)
- Enh: Upload widget rewrite. (buddha)
- Enh: Picker widgets rewrite (UserPicker/SpacePicker/MultiselectDropdown). (buddha)
- Enh: Richtext widget rewrite. (buddha)
- Enh: Removed almost all inline JS blocks. (buddha)
- Enh: StreamAction now uses flexible StreamQuery Model. (buddha)
- Enh: Post markdown support. (buddha)
- Enh: Added 'live' module for push/pull messages to the frontend (luke-)
- Enh: Added asynchronous job queues (luke-)
- Enh: Changed minimum PHP version to 5.6
- Enh: Added possibility of global content (content w/o contentcontainer) (luke)
- Enh: Added new profile field type: checkbox (luke-)
- Enh: Refactored mail summaries activity module (luke-)
- Enh: Moved all static files (js, fonts, css) into own static folder @web-static (luke-)
