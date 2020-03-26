HumHub Change Log (DEVELOP)
===========================


1.5.0-beta.1 (Unreleased)
-------------------------

- Enh #3858: Support SameSite cookies
- Fix #3861: Improved warning details when auto delete inconsistent notification
- Enh: Added gradient to `ui.showMore` feature
- Fix #3873: Invalid visibility handling in `Content::canView()` for private global content
- Fix #3896: Top menu dropdown double border on focus/hover
- Fix #3834: Many entries in the top menu crashing layout
- Enh #3907: Allow `client.ajax().abort()`
- Enh #3909: Add filters to `Administration -> Information -> Logging`
- Enh #3910: Add javascript url util `humhub.util.url.getUrlParameter()`
- Enh #3557: Add permission filter to space and user permission settings
- Enh #3844: Add directory menu icons
- Enh #3792: Render profile field description as form hint
- Enh #3841: Allow * as group-id in `defaultPermissions` configuration
- Enh #3924: Implement `StreamQuery->$to` field and query for stream updates
- Enh #3927: Add `content.container.guid()` function to determine active container on the frontend
- Enh #3924: Display update stream badge once an update is available
- Enh #3924: Implement `Stream.isUpdateAvailable()` to determine if there is an update available
- Enh #3924: Implement `Stream.onUpdateAvailable()`, `Stream.loadUpdate()` and `Stream.options.autoUpdate` to manage stream updates
- Enh #3924: Added `humhub\modules\content\live\NewContent:$streamChannel`
- Enh #3928: Implement auto updates on activity stream
- Enh #3930: Add default `Stream.initScroll` with support of IntersectionObserver
- Fix #3904: Removed unused "alt" attribute on Span element
