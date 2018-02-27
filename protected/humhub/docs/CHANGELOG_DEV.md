HumHub Change Log - v1.3-dev Branch
===================================

1.3.0-beta.1  (Not released yet)
--------------------------------

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

