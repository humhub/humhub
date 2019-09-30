Custom Views
============

> Warning: Since View files may change during onging HumHub development, we recommend to reduce view file overwrites to a minimum. See [Migration](migrate.md) chapter for more details regarding updates.

Foreword
--------

HumHub's templating system enables web-designers and developers to easily build their own theme, provided they have a solid technical grounding.

Required technical background:
- PHP 5.6+
- MVC concept
- HTML, CSS & Javascript
- Bootstrap v3


Determine View File
--------------------

The first step is to determine the original view file which you wish to overwrite.

All in **HumHub** used views are stored in the `views` folder of the corresponding module / package.

Basically there are two types of views:

- **Controller views** (located directly in the directory `views` of the module)
- **Widget views** (located in the directory `widgets/views` of the module)

> See also: [Overview](../developer/overview.md) of available HumHub core modules.

Another possible straightforward approach to determine the correct view is to search the files for a piece of view code.
As example: The search for `id="login-form"` will lead you to the file `humhub/modules/user/views/auth/login.php`. 


Create Modified Version
------------------------

Once you have located the view file to overwrite, you need to simply copy it to the correct folder in your [Theme Folder](structure.md).

Within the "views" directory of your [Theme Folder](structure.md), the path of the modified view file is mapped 1:1 from the corresponding module directory. 
If the view file to be overwritten is not assigned to a module, "humhub" is used as base  folder. 

### Examples

**Module view file:**
     
Base folders like `protected/modules/polls/views` or `protected/humhub/modules/admin/views`.

| Original file | Themed file |
|----------|-------------|
| protected/humhub/modules/admin/views/user/add.php | themes/example/views/admin/views/user/add.php |
| protected/humhub/modules/user/widgets/views/userListBox.php | themes/example/views/user/widgets/views/userListBox.php |
| protected/modules/polls/views/poll/show.php | themes/example/views/polls/views/poll/show.php |

**Core view files :**

All view files located in folders like `protected/widgets/views` or `protected/views`.

| Original file | Themed file |
|----------|-------------|
| protected/humhub/widgets/views/logo.php  | themes/example/views/humhub/widgets/views/logo.php |
| protected/humhub/widgets/mails/views/mailHeadline.php | themes/example/views/humhub/widgets/mails/views/mailHeadline.php |
| protected/humhub/views/error/index.php  | themes/example/views/humhub/error/index.php|
