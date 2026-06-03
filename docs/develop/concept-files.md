# Files

The `humhub\modules\file` module provides file storage with attached access control, available to any `humhub\components\ActiveRecord`. Records get a `fileManager` (`humhub\modules\file\components\FileManager`) that handles attaching, listing and querying related files.

## Uploading

Two patterns exist depending on how the upload reaches the controller.

### Direct mapping (ActiveForm upload)

When a single form posts both metadata and the file in one request. See [Yii's file upload guide](https://www.yiiframework.com/doc/guide/2.0/en/input-file-upload) for the form side.

After validation, persist the upload into HumHub's file storage:

```php
use humhub\modules\file\models\File;
use yii\web\UploadedFile;

$model = new YourModel();
if ($model->load(Yii::$app->request->post()) && $model->validate()) {
    $upload = UploadedFile::getInstance($model, 'image');

    $file = new File();
    $file->file_name = $upload->baseName . '.' . $upload->extension;
    $file->mime_type = $upload->type;
    $file->size = $upload->size;
    if ($file->save()) {
        $file->setStoredFile($upload);
    }
}
```

### Lazy mapping (JS upload, attach later)

For modules that upload before the parent record exists — the JS client uploads files individually, collects their GUIDs, and the controller attaches them after saving the form:

1. JS uploads each file via the file API; the response contains the file's `guid`.
2. The form stores collected GUIDs in a hidden field (comma-separated).
3. After `save()`, attach them in the controller:

```php
$model = new YourModel();

if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
    $model->fileManager->attach(Yii::$app->request->post('fileList'));
}
```

## Querying

Use the record's `fileManager` to look up attached files:

```php
// All files attached to the record
$files = $record->fileManager->findAll();

// Filtered query
$banner = $record->fileManager->find()
    ->andWhere(['title' => 'banner'])
    ->one();
```

## Updating file contents

Three ways to replace the bytes of an existing `File` record without creating a new row:

```php
$file->setStoredFileContent('new content as string');
$file->setStoredFile($uploadedFile);          // yii\web\UploadedFile
$file->setStoredFile($anotherFileRecord);     // copy from another File
```

If file history is enabled on the record, each replacement appends a history entry — see below.

## File history (versioning)

History is opt-in via a flag on the record:

```php
class YourModel extends \humhub\components\ActiveRecord
{
    public $fileManagerEnableHistory = true;
}
```

With history enabled, every `setStoredFile()` / `setStoredFileContent()` call snapshots the previous version.

```php
// All history entries, newest first
$history = $file->getHistoryFiles()->all();

// Newest history entry
$latest = $file->getHistoryFiles()->one();
$bytes = file_get_contents($latest->getFileStorePath());

// Roll back to the latest history version
$file->setStoredFile($latest->getFileStorePath());
```

## Converters and variants

A converter produces a *variant* of a file — a thumbnail, a different format, a resized image. The variant is stored alongside the original.

```php
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\file\models\File;

$file = File::findOne(['guid' => $guid]);

$preview = new PreviewImage();
if ($preview->applyFile($file)) {
    echo $preview->getUrl();
}
```

Custom converters extend `humhub\modules\file\converter\BaseConverter`.

Always generate variants on demand — they can be removed during upgrades or migrations, so treat them as cache, not source of truth.

## Image manipulation

The core bundles the [Yii Imagine extension](https://www.yiiframework.com/extension/yiisoft/yii2-imagine/doc/guide/2.0/en/README) for image processing. See its docs for the API; HumHub does not wrap it.

## Access control

Attached files inherit visibility from the record they're attached to. Polymorphic relations carry the access rules:

- File attached to a `Content` → enforces the content's `visibility`, container membership, and content permissions.
- File attached to a freestanding ActiveRecord → access is whatever the record's class declares; if your record exposes files via a controller action, that action is responsible for the access check.

There is no separate ACL on the file itself — the access path is *always* through the parent record.

## Storage backend

The default backend writes to `uploads/file/` on the local filesystem. Alternative storage drivers (S3 etc.) are typically provided by third-party modules and configured on the `file` module.
