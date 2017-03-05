Custom Views
============

> Info: Since View files may change during onging HumHub development, we recommend to reduce view file overwrites to a minimum. See [Migration](migrate.md) chapter for more details regarding updates.

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

All in **HumHub** used views are stored in the `views` folder of the corresponding module.

Basically there are three types of views:

- **Controller views** (located directly in the directory `views` of the module)
- **Widget views** (located in the directory `widgets/views` of the module)
- **Main layout views** (located in the directory `views` of the HumHub root application folder)

See also: [Overview](../developer/overview.md) of available HumHub core modules.

Another possible straightforward approach to determine the correct view is to search the files for a piece of view code.
As example: The search for `id="login-form"` will lead you to the file `humhub/modules/user/views/auth/login.php`. 


Create Modified Version
------------------------

Once you have located the view file to overwrite, you need to simply copy it to the correct folder in your theme directory.

See also the document [Theme Folder Structure](structure.md) for more details.

The view file conversion is done via following patterns:
- Controller views: `humhub/moduleId/views/actionId/viewFile.php` to `yourTheme/views/moduleId/actionId/viewFile.php`
- Widget views: `humhub/moduleId/widgets/views/viewFile.php` to `yourTheme/views/moduleId/widgets/viewFile.php`
- Main layout views: `humhub/views/layouts/viewFile.php` to `yourTheme/views/layouts/viewFile.php`

Some Examples:
- Login view: Copy the view file `humhub/modules/user/views/auth/login.php` to `yourTheme/views/user/auth/login.php`
- Main layout: Copy the view file `humhub/views/layouts/main.php` to `yourTheme/views/layouts/main.php`
