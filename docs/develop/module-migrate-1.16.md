# Module Migration — Version 1.16

## Version 1.16 (April 2024)

At least PHP 8.0 is required with this version.

### Removed
- `\humhub\modules\search\*` The existing search module was removed and the related features merged into the 'content', 'user' and 'space' modules.
- `\humhub\modules\user\models\User::getSearchAttributes()` and `\humhub\modules\space\models\Space::getSearchAttributes()`

### Behaviour change
- New Meta Search API
- Controller route change: `/search/mentioning` -> `/user/mentioning`
- `Yii::$app->search()` component is not longer available.
    - Use `(new ContentSearchService($exampleContent->content))->update();` instead of `Yii::$app->search->update($exampleContent);`
- The method `setCellValueByColumnAndRow()` has been replaced with `setCellValue()` and `setValueExplicit()`.
- When rendering xlsx generated data cells, use the `setCellValue()` method with the appropriate coordinate obtained using `getColumnLetter()`.
- Switch `Module::$resourcesPath` to `resources`

### Deprecations
- `\humhub\components\Module::getIsActivated()` use `getIsEnabled()` instead
  (note: this also affects the virtual instance property `\humhub\modules\friendship\Module::$isActivated` which should now read `$isEnabled`!)
- `\humhub\components\Module::migrate()` use `getMigrationService()->migrateUp(MigrationService::ACTION_MIGRATE)` instead
- `\humhub\libs\BaseSettingsManager::isDatabaseInstalled()` use `Yii::$app->isDatabaseInstalled()` instead
- `\humhub\models\Setting::isInstalled()` use `Yii::$app->isInstalled()` instead
- `\humhub\modules\content\components\ContentAddonActiveRecord::canRead()` use `canView()` instead
- `\humhub\modules\content\components\ContentAddonActiveRecord::canWrite()`
- `\humhub\modules\file\models\File::canRead()` use `canView()` instead
- `\humhub\modules\friendship\Module::getIsEnabled()` use `isFriendshipEnabled()` instead
  (note: `\humhub\modules\friendship\Module::getIsEnabled()` and the virtual property `\humhub\modules\friendship\Module::isEnabled` now return the status of the module - which yields always true for core modules.)
- `\humhub\modules\marketplace\Module::isEnabled()` use `isMarketplaceEnabled()` instead
- `\humhub\modules\marketplace\services\ModuleService::activate()` use `enable()` instead

### New
- `humhub\modules\stream\actions\GlobalContentStream`
- `humhub\modules\stream\models\GlobalContentStreamQuery`
- `humhub\modules\stream\models\filters\GlobalContentStreamFilter`
- A new protected function `SpreadsheetExport::getColumnLetter()` has been introduced to get the column letter based on the column index.

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

### Bugfix with potential side-effect
- `\humhub\modules\ui\form\widgets\BasePicker` and `\humhub\modules\ui\form\widgets\MultiSelect` do now treat and empty array for the field `BasePicker::$selection` as a valid selection list and will not attempt to get the list from the model in that case.
