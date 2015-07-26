Theming
=======

With **HumHub** you can easily create your own themes. Regardless of whether you want to build a complete new looking interface or you only want to do some small changes to fit the platform better to your needs. **HumHub** gives you the options you need. 

``Note:`` If you want to update an existing theme to a newer version of HumHub, please read the [Theming Migration Guide](migrate.md).

### What you need to know about theming in HumHub
1. **HumHub** was build within the **yiiframework** (<http://www.yiiframework.com>). This framework implements the MVC (Model-View-Controller) design pattern. If this is new for you, please visit <http://www.yiiframework.com/doc-2.0/guide-README.html> for more information.

2. The interface of **HumHub** is using the actually **Twitter Bootstrap Framework** (<http://www.getbootstrap.com>) to building the interface.

3. If you created a new theme you have to put it to the **/themes** folder in your HumHub directory. You can switch to the new theme through your account menu **Administration > Design > Theme**.




---

## Structure of theme folders

The principle of theming in **HumHub** is overwriting. To build a new theme, you have to create a new folder with the name of your new theme (for example: "mytheme") inside the "themes" folder in application root.

**The following listing shows you the folder structure for a theme:**

    /mytheme/                       - My Theme Name
        /css/                       - Your theme css files (optional)
        /js/                        - Additional javascript files (optional)
        /font/                      - Additional fonts (optional)
        /img/                       - Images (optional)         
        /views/                     - Overwritten Views
            /moduleId/              - Id of Module (module_core Id, module Id, or base controller id)
                /controllerId/      - Id of Controller
                    index.php       - Overwritten View File
                /widgets/           - Links to /someModule/widgets/views/
                    someWidget.php  - Overwritten widget view
            /widgets/                - Links to /protected/widget/views

``Note: ``  There is an difference between yii´s normal widget theme handling!

---

## Edit CSS
If you want to create a new theme or just want change some details, we recommend to dublicate the standard HumHub theme (duplicate and rename the folder) and make your changes there. This is still not even easier to start this is also important when you do updates later. Because all changes you did directly at the HumHub Theme will be override by an update.

At the **/themes/yourtheme/css/** folder you will find the compressed **theme.css** and a **theme.less** file. The easiest way is to edit the *.less file and compile then the css file.

At the first lines in the **theme.less** file you will find the color variables. If you just want to change the colors to adapt HumHub to your CI, simple change the color codes there and complie the *.less file.

### Modules
So that every module adobt the colors from the actually installed theme, you will find color classes for font, border and background colors of every bootstrap style. So if you build a module, just add the specific classes to the html elements you want to style.






## Custom CSS
If your theme is activated, you have to tell **HumHub**, where to find new **CSS** or **JavaScript** files. To do that, you have to copy the **head.php** from:

    /protected/views/layout/

to your theme folder:

    /themes/mytheme/views/layout/head.php

Now open the copied **head.php** with an editor of your choice and you can add further **CSS** or **JavaScript** files, you needed.

Here is an example how to load your own css file and override the bootstrap standard classes:

    // Core bootstrap css file
    <link href="<?php echo Yii::app()->baseUrl; ?>/css/bootstrap.min.css" rel="stylesheet">

    // Additional needed CSS classes
    <link href="<?php echo Yii::app()->baseUrl; ?>/css/style.css" rel="stylesheet">

    // Your theme css file
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/css/mytheme.css" rel="stylesheet">

``Note: ``  Your own css file has to be load **after** the bootstrap core css file to work correctly.

If your own css file is loading correctly you can writing your own styles depends an the **Twitter Bootstrap Guidelines** you will find here: <http://getbootstrap.com/getting-started>

---

## Custom views
To edit a view, you have to copy the original **view** from the **protected** folder into your **theme** folder. Please take care to observe the themes folder structure above.

If your theme is activiated, **HumHub** automatically looks at first inside the theme folder. If there was no view found, he will load the original one.




