Mail Layouts
============

The original mail layout templates are located in the folder `protected/humhub/views/mail/layouts`.

Following the view overwrite logic, you can copy a modified template version to `themes/YourTheme/views/mail/layouts`.

Mail Colors
-----------

Since the CSS support in mail templates is very limited, you may need to access the current color scheme manually.

You can access all CSS variables defined in `variables.less` by calling `Yii::$app->view->theme->variable('variableName');`.

Example:
```
<html> 
    ...
    <body style="background-color:<?= Yii::$app->view->theme->variable('background-color-page') ?>; ">
        ...
    </body>
    ...
</html>