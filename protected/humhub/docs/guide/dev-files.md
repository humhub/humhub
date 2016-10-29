File Handling
=============

The HumHub core module *humhub/modules/file* provides a generic file management which can be used in custom modules to store and read files including access control.

The file management is available for [[humhub\components\ActiveRecord]] classes.
You can access the [[humhub\modules\file\components\FileManager]] via [[humhub\components\ActiveRecords::getFileManager]].

Examples Usage
--------------

### Direct Mapping

### Lazy Mapping


Javascript Uploads
------------------
TBD

Converter
---------

Converters are used to create variants (e.g. diffrent file formats or images size) of an existing file.
All converted files will be automatically stored with the original file.

Example usage:
```php
$file = \humhub\modules\file\models\File::findOne(['guid' => 'your file guid']);

$previewImage = new \humhub\modules\file\converter\PreviewImage();
$previewImage->applyFile($file);

echo $previewImage->getUrl();

```

You can also create own Converters by using [[humhub\modules\file\converter\BaseConverter]].


Image Manipulation
------------------

HumHub provides several ways to deal with images.

### Imagine Extension

### ImageConverter


Access Control
-------------
TBD


Storage
-------
TBD
