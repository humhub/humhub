Internationalization (I18N)
===========================

**Optionally** you can use following module translation method instead of Yii's standard approach ([http://www.yiiframework.com/doc-2.0/guide-tutorial-i18n.html#translating-module-messages](http://www.yiiframework.com/doc-2.0/guide-tutorial-i18n.html#translating-module-messages)).  


### Message Category

Following message category syntax is automatically mapped against your modules *messages* folder.

```php
Yii::t('ExampleModule.some_own_category', 'Translate me');
```

Base Category Naming Examples:

- polls -> PollsModule
- custom_pages -> CustomPagesModule


### (Re-) Generate message files

Example message creation command for module with id *example*:

> php yii message/extract-module *example*
