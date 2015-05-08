ModuleManager Component from ZF2
================================

This is the ModuleManager component for ZF2.

- File issues at https://github.com/zendframework/zf2/issues
- Create pull requests against https://github.com/zendframework/zf2
- Documentation is at http://framework.zend.com/docs

LICENSE
-------

The files in this archive are released under the [Zend Framework
license](http://framework.zend.com/license), which is a 3-clause BSD license.

Description
-----------
This is a module loader and manager for ZF2.

Currently Implemented
---------------------

* **Phar support:** 
  Modules can be packaged, distributed, installed, and ran as phar archives. 
  Supports both executable and non-executable archives; with and without a stub.
  `Module` class must be made available by either Module.php in the root of the
  phar or in the stub if it is an executable phar. Below is a list of phar 
  archive/compression formats that are supported and their respective extensions, 
  as detected by the module loader:
    * **Executable** (can be included directly, which executes stub):
        * phar (.phar)
        * phar + gz  (.phar.gz)
        * phar + bz2 (.phar.bz2)
        * tar (.phar.tar)
        * tar + gz (.phar.tar.gz)
        * tar + bz2 (.phar.tar.bz2)
        * zip (.zip)
    * **Non-executable** (phar cannot be included directly; no stub can be set):
        * tar (.tar)
        * tar + gz (.tar.gz)
        * tar + bz2 (.tar.bz2)
        * zip (.zip)
* **Configuration merging:**
    The module manager goes through each enabled module, loads its
    `Zend\Config\Config` object via the `getConfig()` method of the respective
    `Module` class; merging them into a single configuration object to be passed
    along to the bootstrap class, which can be defined inthe config of course!
* **Caching merged configuration:**
    To avoid the overhead of loading and merging the configuration of each
    module for every execution, the module manager supports caching the merged
    configuration as an array via `var_export()`. Subsequent requests will bypass
    the entire configuration loading/merging process, nearly eliminating any
    configuration-induced overhead.
* **Module init():**
    The module manager calls on the `init()` method on the `Module` class of
    each enabled module, passing itself as the only parameter. This gives
    modules a chance to register their own autoloaders or perform any other
    initial setup required. **Warning:** The `init()` method is called for every
    enabled module for every single request. The work it performs should be kept
    to an absolute minimum (such as registering a simple classmap autoloader).
* **100% unit test coverage:**
    Much effort has been put into extensive unit testing of the module loader
    and manager. In addition to covering every line of code, further effort was
    made to test other use-cases such as nested/sub-modules and various other 
    behaviors.
* **Module Dependency**
    Refactored to now allow self resolution of dependencies. Now provides better access
    to all provisions & dependencies within an application. This is opt-in with
    the enable_dependency_check option. Modules can declare dependencies on
    other modules (and versions of the required modules).

Stuff that still needs work:
----------------------------

* How to expire the merged config cache in production and/or development.
* ~~Ability for modules to cleanly "share" resources? For example, you have 5 module which all use a database connection (or maybe two: master for writes, slave for reads).~~ Update: see [this thread](http://zend-framework-community.634137.n4.nabble.com/Sharing-resources-across-3rd-party-modules-td3875023.html) on the zf-contributor mailing list.
* How can modules use varying view templating types? For example, one module uses twig, another uses smarty, another mustache, and yet another uses phtml. Does it make sense to have modules for each template library or system, then modules can just declare the respective one as a dependency?
* How to handle static assets such as images, js, and css files ([assetic](https://github.com/kriswallsmith/assetic) has been suggested).
* ~~Should dependencies also be resolved outside of the distribution channel (such as pyrus) to better handle manual / alternate installation methods?~~ [SOLVED]
* Should we, and if so, how would we handle DB schemas for installation, update/migrations, and uninstallation of modules?
* When a module is uninstalled, what's removed, what should stay? Do we put the responsibility of uninstallation in the hands of each module developer?
* When a module is uninstalled, should it check for other modules that are still depending on it? How much is too much, and when do we just leave it up to the developers?
