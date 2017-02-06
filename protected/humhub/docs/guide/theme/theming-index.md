Theming
=======

With **HumHub** you can easily create your own themes. Regardless of whether you want to build a complete new interface or you only want to do some small changes to better fit your needs.

> Note: If you want to update an existing theme to a newer version of HumHub, please read the [Theming Migration Guide](migrate.md).

### What you need to know about theming in HumHub

1. **HumHub** is build within the **yii2 framework** (<http://www.yiiframework.com>). Yii2 implements the MVC (Model-View-Controller) design pattern. If this is new for you, please visit <http://www.yiiframework.com/doc-2.0/guide-README.html> for more information.

2. The user interface of **HumHub** is based upon **Twitter Bootstrap** (<http://www.getbootstrap.com>).

3. If you created a new theme, you have to put it to the `/themes` folder of your HumHub root directory. You can switch to the new theme through your account menu **Administration > Settings > Appearance > Theme**.

---

## Theme Structure

The principle of theming in **HumHub** is overwriting. To build a new theme, you have to create a new folder with the name of your new theme (for example: "mytheme") inside the "themes" folder in application root.

**The following listing shows the directory structure of a theme:**

    /mytheme/                       - My Theme Name
        /css/                       - Your theme css files (optional)
            theme.css               - Your actual theme css file
        /less/                      - Contains less files used to build your theme.css (optional)
            build.less              - Used to build your theme.css
            variables.less          - Contains theme variables as text and background colors
            mixins.less             - Used to define own mixins
            theme.less              - Contains your own theme definitions
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

> Note:  The theme structure differs from yii´s normal structure.

---


## Create Custom Themes

### Setup your Theme

If you want to create a new theme or just want to change some styles, we recommend to dublicate the standard HumHub theme (duplicate and rename the folder) and make your changes there. 

> Note: Never edit the standard HumHub theme directly. All changes will be overwritten by an update.

### Build your Custom Theme

Since HumHub 1.2 it is recommended to use the `theme.less` and `variables.less` files to define your custom theme styles and build your `theme.css` file by compiling the `build.less` file.

If you are using the command line tool [lessc](http://lesscss.org/), you can build your theme as follows:

```
lessc -x build.less ../css/theme.css
```

> Info: For compiling your less file, there are also other alternatives like  [WinLess](http://winless.org/) or  [SimpLESS](https://wearekiss.com/simpless). 

The `build.less` will automatically import all required **.less** files of your HumHub project and your theme less files in the following order:

1. Default variables `humhub/static/less/variables.less`
2. Default mixins `humhub/static/less/mixins.less`
3. All default components and definitions `humhub/static/less/humhub.less`
4. Theme variable overwrites `mytheme/less/variables.less`
5. Theme mixins `mytheme/less/mixins.less`
6. Theme definitions/overwrites `mytheme/less/theme.less`

If you wish to overwrite default theme variables as text and background colors, just copy and edit your variables from `humhub/static/less/variables.less` into your themes `variables.less` file. 

> Info: All variables defined in `variables.less` can also be accessed in your views by calling `Yii::$app->view->theme->variable('myVariable');`, and are used in your mail views by default.

> Info: If your theme directory resides outside the `themes` directory of your HumHub project while developement, you'll have to edit the `@HUMHUB` path variable within the `build.less` file to point to the `static/less` folder of your HumHub project directory.

#### Prevent default component file imports

If you whish to exclude a specific default component file defined in `humhub/static/less/humhub.less`, you can overwrite certain variables to prevent the file import in your build.

By setting for example the variable `@prev-login: true;` in your `theme.less` or `variables.less` file, you can prevent the inclusion of the default `login.less` file into your theme. This approach can be handy if your theme requires major style changes for a default component.

Here is a list of all imported default components and the corresponding variables used to prevent the import:

- **base.less** - Base Html definition - `@prev-base`
- **topbar.less** - Topbar definition - `@prev-topbar`
- **login.less** - Login view and modal - `@prev-login`
- **dropdown.less** - Topbar definition - `@prev-dropdown`
- **media.less** - Bootstrap media objects -  `@prev-media`
- **installer.less** - Installer definitions - `@prev-installer`
- **pagination.less** - GridView Paginations - `@prev-pagination`
- **well.less** - Bootstrap well - `@prev-well`
- **nav.less** - Navigations - `@prev-nav`
- **button.less** - Buttons - `@prev-button`
- **form.less** - Forms - `@prev-form`
- **notification.less** - Notification module definitions - `@prev-notification`
- **badge.less** - Badges - `@prev-badge`
- **popover.less** - Bootstrap Popovers - `@prev-popover`
- **list-group.less** - List Gorups - `@prev-list-group`
- **modal.less** - Bootstrap Modals - `@prev-modal`
- **module.less** - Module component definition - `@prev-module`
- **tooltip.less** - Jquery UI Tooltips - `@prev-tooltip`
- **progress.less** - Progress bars - `@prev-progress`
- **table.less** - Table definitions - `@prev-table`
- **comment.less** - Comment Module definitions - `@prev-comment`
- **gridview.less** - Gridviews - `@prev-gridview`
- **oembed.less** - oEmbed definitions - `@prev-oembed`
- **activities.less** - Activity Module definition - `@prev-activities`
- **stream.less** - Stream Module definitions - `@prev-stream`
- **space.less** - Space Module definitions - `@prev-space`
- **file.less** - File Module definitions - `@prev-file`
- **tour.less** - Tour Module definitions - `@prev-tour`
- **mentioning.less** - User Mentioning definitions - `@prev-mentioning`
- **loader.less** - Loader Animation definitions - `@prev-loader`
- **markdown.less** - Markdown - `@prev-markdown`
- **sidebar.less** - Sidebar definitions - `@prev-sidebar`
- **datepicker.less** - Datepicker definitions - `@prev-datepicker`
- **user-feedback.less** - User Feedback as Status bar etc. - `@prev-user-feedback`
- **tags.less** - Bootstrap tags - `@prev-tags`

#### Additional theme assets
In order to load additional **CSS** or **JavaScript** files in your theme, add them to  `/themes/mytheme/views/layouts/head.php`

---

## Custom views

To modify a **view** or **layout**, you have to copy the original **view** from the **protected/humhub/***  folder into your **theme** folder. Please take care to observe the themes folder structure above.

**Examples:** 

- To edit your theme´s login view you have to copy the following file:

from: `humhub/protected/modules/user/views/auth/login.php`
to: `humhub/themes/mytheme/views/user/auth/login.php`

- To edit the main layout you have to copy the following file:


from: `humhub/protected/views/layouts/main.php`
to: `humhub/themes/mytheme/views/layouts/main.php`

> Note: If your theme is activiated, **HumHub** automatically searches for views, widgets and assets within your theme structure and only uses the default views as fallback.

## Javascript related issues

Since the HumHub user interface is highly based on Javascript, some changes may require the alignment of a Javascript module.

You can intercept the installation of a module by means of the following evetns:

- `humhub:beforeInitModule`
- `humhub:afterInitModule`
- `humhub:modules:<module_id>:beforeInit`
- `humhub:modules:<module_id>:afterInit`
- `humhub:ready`

The following example shows how to overwrite an exported Javascript module function:

```
humhub.module('mytheme', function(module, require, $) {
    var event = require('event');
	
    event.on('humhub:ready', function() {
        var status = require('ui.status');
	    status.info = function() {
            // Some logic...
        }
    });
});
```




