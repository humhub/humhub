# Build custom CSS package

Since HumHub 1.2 it is recommended to use the `theme.less` and `variables.less` files to define your custom theme styles and build your `theme.css` file by compiling the `build.less` file.


## Create custom LESS file

The `build.less` will automatically import all required **.less** files of your HumHub project and your theme less files in the following order:

1. Default variables `/static/less/variables.less`
2. Default mixins `/static/less/mixins.less`
3. All default components and definitions `/static/less/humhub.less`
4. Theme variable overwrites `/themes/Example/less/variables.less`
5. Theme mixins `/themes/Example/less/mixins.less`
6. Theme definitions/overwrites `/themes/Example/less/theme.less`

If you wish to overwrite default theme variables as text and background colors, just copy and edit your variables from `humhub/static/less/variables.less` into your themes `variables.less` file. 

> Info: All variables defined in `variables.less` can also be accessed in your views by calling `Yii::$app->view->theme->variable('myVariable');`, and are used in the mail views by default.


## Compile CSS package

If you are using the command line tool [lessc](http://lesscss.org/), you can build your theme as follows:

```
lessc -x themes/Example/less/build.less themes/Example/css/theme.css
```

or respectively by using [grunt](../developer/core-build.md):

```
grunt build-theme --name=Example
```

> Info: For compiling your less file, there are also other alternatives like  [WinLess](http://winless.org/) or  [SimpLESS](https://wearekiss.com/simpless). 


## Prevent default component file imports

If you wish to exclude a specific default component file defined in `humhub/static/less/humhub.less`, you can overwrite certain variables to prevent the file import in your build.

By setting for example the variable `@prev-login: true;` in your `theme.less` or `variables.less` file, you can prevent the inclusion of the default `login.less` file into your theme. 
This approach can be handy if your theme requires major style changes for a default component.

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
