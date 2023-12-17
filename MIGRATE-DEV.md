Module Migration Guide
======================

See [humhub/documentation::docs/develop/modules-migrate.md](https://github.com/humhub/documentation/blob/master/docs/develop/modules-migrate.md)
for full version.

Version 1.16 (Unreleased)
-------------------------

### Deprecations
- `\humhub\components\Module::migrate()` use `getMigrationService()->migrateUp(MigrationService::ACTION_MIGRATE)` instead
- `\humhub\libs\BaseSettingsManager::isDatabaseInstalled()` use `Yii::$app->isDatabaseInstalled()` instead
- `\humhub\models\Setting::isInstalled()` use `Yii::$app->isInstalled()` instead
- `\humhub\modules\content\components\ContentAddonActiveRecord::canRead()` use `canView()` instead
- `\humhub\modules\content\components\ContentAddonActiveRecord::canWrite()`
- `\humhub\modules\file\models\File::canRead()` use `canView()` instead

### Type restrictions
- `\humhub\commands\MigrateController` enforces types on fields, method parameters, & return types
- `\humhub\components\behaviors\PolymorphicRelation` enforces types on fields, method parameters, & return types
- `\humhub\components\bootstrap\ModuleAutoLoader::findModules()` is enforcing types on method parameters and return value
- `\humhub\components\bootstrap\ModuleAutoLoader::findModulesByPath()` is enforcing types on method parameters and return value
- `\humhub\components\bootstrap\ModuleAutoLoader::locateModules()` is enforcing return type
- `\humhub\components\ModuleManager::register()` is enforcing types on method parameters
- `\humhub\modules\comment\models\Comment` on `canDelete()`
- `\humhub\modules\content\components\ContentAddonActiveRecord` on `canDelete()`, `canWrite()`, `canEdit()`
- `\humhub\modules\content\models\Content` on `canEdit()`, `canView()`
- `\humhub\modules\file\models\File` on `canRead()`, `canDelete()`



Version 1.15
-------------------------

### Behaviour change
- `\humhub\libs\BaseSettingsManager::deleteAll()` no longer uses the `$prefix` parameter as a full wildcard, but
  actually as a prefix. Use `$prefix = '%pattern%'` to get the old behaviour. Or use `$parameter = '%suffix'` if you
  want to match against the end of the names.
- `\humhub\libs\BaseSettingsManager::get()` now returns a pure int in case the (trimmed) value can be converted
- New `PolymorphicRelation::getObjectModel()`: should replace `get_class()`
- Removed deprecated javascript method `setModalLoader()`
- Javascript CSP Nonces are now required and enabled by default! See: https://docs.humhub.org/docs/develop/javascript/
- Use the verifying `Content->canArchive()` before run the methods `Content->archive()`
  and `Content->archive()`, because it was removed from within there.
- Permission to configure modules is now restricted to users allowed to manage settings (was previously restricted to users allowed to manage modules). [More info here](https://github.com/humhub/humhub/issues/6174).
- `$guid` properties in `contentcontainer`, `file`, `space`, and `user` models are now enforced to be valid UUIDs
  (See `UUID::validate()`) and unique within the table.

### Type restrictions
- `\humhub\libs\BaseSettingsManager` and its child classes on fields, method parameters, & return types
- `\humhub\libs\Helpers::checkClassType()` (see [#6548](https://github.com/humhub/humhub/pull/6548))
  - rather than throwing a `\yii\base\Exception`, it now throws some variations of `yii\base\InvalidArgumentException`
    with different Exception Codes as documented in the function's documentation:
      - `\humhub\exceptions\InvalidArgumentClassException`
      - `\humhub\exceptions\InvalidArgumentTypeException`
      - `\humhub\exceptions\InvalidArgumentValueException`
  - the return type has changed from `false` to `string|null`
  - the second parameter `$type` is now mandatory

### Deprecations
#### New
- `Content::addTags()` and `Content::addTag()`. Use `ContentTagService`
- `humhub\libs\UUID::is_valid()`. Use `UUID::validate()`

#### Removed
- `humhub\libs\Markdown`
- `humhub\libs\MarkdownPreview`
- `humhub\modules\content\widgets\richtext\AbstractRichText::$markdown`
- `humhub\modules\content\widgets\richtext\AbstractRichText::$maxLength`
- `humhub\modules\content\widgets\richtext\AbstractRichText::$minimal`
- `humhub\modules\content\widgets\richtext\PreviewMarkdown`
- `humhub\modules\content\widgets\richtext\ProsemirrorRichText::parseOutput`
- `humhub\modules\content\widgets\richtext\ProsemirrorRichText::replaceLinkExtension`
- `humhub\modules\content\widgets\richtext\ProsemirrorRichText::scanLinkExtension`
- `humhub\modules\ui\form\widgets\Markdown`
- `humhub\widgets\AjaxButton`
- `humhub\widgets\MarkdownEditor`
- `humhub\widgets\MarkdownField`
- `humhub\widgets\MarkdownFieldModals`
- `humhub\widgets\ModalConfirm`

