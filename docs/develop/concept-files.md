# Files

The HumHub core module *humhub/modules/file* provides a generic file management which can be used in custom modules to store and read files including access control.

The file management is available for all `humhub\components\ActiveRecord` classes.

## Basic Usages

### Create

There are two typical variants for file upload implementations.

#### Uploads via ActiveForm (Direct Mapping)

For direct File Uploads via ActiveForm, see [Yii2 Guide - Uploading files](http://www.yiiframework.com/doc-2.0/guide-input-file-upload.html).

Example to add an uploaded file to HumHub file storage:

```php

$model = new YourModelIncludingFileField();
if ($model->load(Yii::$app->request->post()) && $model->validate()) {
	
	$humhubFile = new \humhub\modules\file\models\File();
	$humhubFile->file_name = $this->image->baseName . '.' . $$this->image->extension;
	$humhubFile->mime_type = $this->image->type;
	$humhubFile->size = $this->image->size;
	if ($humhubFile->save()) {
	    $humhubFile->setStoredFile($this->image);
	}
}

```

#### Uploads via Javascript (Lazy Mapping)

When using lazy file mapping, the files are uploaded by Javascript (see Javascript Uploads section) first and later mapped to an existing ActiveRecord.

Typical workflow:
1. File upload (handled by Javascript)
2. Store successfully uploaded file guids in a hidden form field (comma separated)
3. After saving the form in controller action, assign previously collected file guids to the record.

Example (Step 3):

```php

$model = new YourModel();

if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
	// Lazy map uploaded images
	$model->fileManager->attach(Yii::$app->request->post('field-which-contains-uploaded-file-guids'));

	// Create or Update success
}

```

### Find/Query
 
To read mapped files of an ActiveRecord, use the `humhub\modules\file\components\FileManager` via `humhub\components\ActiveRecord::getFileManager`.

```php
// Get all files
$files = $record->fileManager->findAll();

// Get a single file
$bannerFile = $record->fileManager->find()->andWhere(['title' => 'banner')->one();

```

### Update File Content

The content of a file object can be easily updated using the following methods.
If the file history is activated, a new history entry is created automatically.

```php
$file->setStoredFileContent('V1');
$file->setStoredFile($UploadedFileObject);
$file->setStoredFile($AnotherNewFileRecord);
```

## File History

By default, no history (versioning) of files is created. This must be activated by a flag `ActiveRecord::fileManagerEnableHistory`.

### Access File History Versions

```php 
// get a latest history records
$fileHistorys = $file->getHistoryFiles()->all();

// get latest version file data
$fileHistoryLatest = $file->getHistoryFiles()->one();
$fileData = file_get_contents($previousVersion->getFileStorePath());
```

### Rollback File History Version

```php 
// get latest version file data
$fileHistoryLatest = $file->getHistoryFiles()->one();
$file->setStoredFile($previousVersion->getFileStorePath());
```

## Converter & Variants

Converters are used to create variants (e.g. different file formats or images sizes) of an existing file.
All converted files (variants) will be automatically stored with the original file.

Example usage:

```php

$file = \humhub\modules\file\models\File::findOne(['guid' => 'your file guid']);

$previewImage = new \humhub\modules\file\converter\PreviewImage();
if ($previewImage->applyFile($file)) {
    // Can create preview of given file
    echo $previewImage->getUrl();
}
```

You can also create own Converters by using `humhub\modules\file\converter\BaseConverter`.

> Note: Always create file variants (e.g. previews) on the fly - variants may deleted during the upgrade progress.

## Image Manipulation

HumHub bundles **Imagine **as Yii 2 Extension.

Please see the [Imagine Extension for Yii 2](http://www.yiiframework.com/doc-2.0/ext-imagine-index.html) documentation for more details.

## Access Control
TBD

## Storage Manager
TBD

