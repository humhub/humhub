Module Migration Guide
======================

See [humhub/documentation::docs/develop/modules-migrate.md](https://github.com/humhub/documentation/blob/master/docs/develop/modules-migrate.md)
for full version.

Version 1.15 (Unreleased)
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

### Deprecations

- `Content::addTags()` and `Content::addTag()` are now deprecated. Use `ContentTagService` instead.
- Removed deprecated classes `humhub\widgets\AjaxButton` and `humhub\widgets\ModalConfirm`

### Type restrictions
- `\humhub\libs\BaseSettingsManager` and its child classes on fields, method parameters, & return types

### Removed Deprecations

- `humhub\modules\content\widgets\richtext\PreviewMarkdown`
- `humhub\modules\content\widgets\richtext\ProsemirrorRichText::replaceLinkExtension`
- `humhub\modules\content\widgets\richtext\ProsemirrorRichText::scanLinkExtension`
- `humhub\modules\content\widgets\richtext\ProsemirrorRichText::parseOutput`
- `humhub\modules\content\widgets\richtext\AbstractRichText::$minimal`
- `humhub\modules\content\widgets\richtext\AbstractRichText::$maxLength`
- `humhub\modules\content\widgets\richtext\AbstractRichText::$markdown`
- `humhub\libs\Markdown`
- `humhub\libs\MarkdownPreview`
- `humhub\widgets\MarkdownEditor`
- `humhub\widgets\MarkdownField`
- `humhub\widgets\MarkdownFieldModals`
- `humhub\modules\ui\form\widgets\Markdown`
- 
