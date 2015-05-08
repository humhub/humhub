# INSTALLATION

Zend Framework requires no special installation steps. Simply download
the framework, extract it to the folder you would like to keep it in,
and add the library directory to your PHP `include_path`.


## SYSTEM REQUIREMENTS
-------------------

Zend Framework 2 requires PHP 5.3.3 or later.

## DEVELOPMENT VERSIONS

If you would like to preview enhancements or bug fixes that have not yet
been released, you can obtain the current development version of Zend
Framework using one of the following methods:

 -  Using a Git client. Zend Framework is open source software, and the
    Git repository used for its development is publicly available.
    Consider using Git to get Zend Framework if you already use Git for
    your application development, want to contribute back to the
    framework, or need to upgrade your framework version very often.

 -  Checking out a working copy is necessary if you would like to directly
    contribute to Zend Framework; a working copy can be updated any time
    using git pull.

To clone the git repository, use the following URL:

git://git.zendframework.com/zf.git

For more information about Git, please see the official website:

http://www.git-scm.org

## CONFIGURING THE INCLUDE PATH

Once you have a copy of Zend Framework available, your application will
need to access the framework classes. Though there are several ways to
achieve this, your PHP `include_path` needs to contain the path to the
Zend Framework classes under the `/library` directory in this
distribution. You can find out more about the PHP `include_path`
configuration directive here:

http://www.php.net/manual/en/ini.core.php#ini.include-path

Instructions on how to change PHP configuration directives can be found
here:

http://www.php.net/manual/en/configuration.changes.php

## GETTING STARTED

A great place to get up-to-speed quickly is the Zend Framework
QuickStart:

http://framework.zend.com/manual/2.0/en/user-guide/overview.html

The QuickStart covers some of the most commonly used components of ZF.
Since Zend Framework is designed with a use-at-will architecture and
components are loosely coupled, you can select and use only those
components that are needed for your project.
