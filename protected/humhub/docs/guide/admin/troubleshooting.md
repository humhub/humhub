Troubleshooting and Help
========================

This guide provides some assistance for common administrative problems. Please read this guide before creating a new
issue on github or the community. In case this guide does not help you with your problem, please add as much
information as possible to your issue description as:

- How to reproduce your problem
- Add log error and info messages either from `Administration -> Information -> Logs` or under `protected/runtime/logs`
- Information about your setup as:
  - **HumHub version**: check `Administration -> Information` or `protected/humhub/config/common.php` 
  - **Module version**: in case the problem is module related check `Administration -> Modules` or `protected/modules/<moduleId>/module.json`
  - **PHP version**
- Screenshots
- Check the Javascript console of your browser: 
  - [Chrome](https://developers.google.com/web/tools/chrome-devtools/console/)
  - [Firefox](https://developer.mozilla.org/en-US/docs/Tools/Web_Console/Opening_the_Web_Console)
  - [Safari](https://developer.apple.com/library/archive/documentation/NetworkingInternetWeb/Conceptual/Web_Inspector_Tutorial/EnableWebInspector/EnableWebInspector.html)
  - [Opera](https://dev.opera.com/extensions/testing/)
  - [Edge](https://docs.microsoft.com/en-us/microsoft-edge/devtools-guide/console)
  - [IE 11](https://msdn.microsoft.com/en-us/library/hh968260(v=vs.85).aspx)
 - Is there any event, which could have triggered the problem as:
   - HumHub Update
   - Module Update
   - Installed a new Module
   - Changed server environment

Cron Job Setup
----------------------------------------

The cron setup can be frustrating and it's hard to provide a guide for every possible server environment. There is a
[community driven wiki](https://community.humhub.com/s/installation-and-setup/wiki/page/view?title=Cron+Job+Setup) with
examples of cron settings for different environments. Furthermore refer to the [Cron Job Setup](cron-jobs.md) guide if not already done.

Please check the following known issues before opening an issue:

- Are you using the right executable for your cron command.

Make sure you are using **php-cli** instead of **php-cgi** and that your php is pointing to the same installation/configuration
as your apache server. You can check the installed php and installed packages by running the following commands:

```
> php -v
> php --ini
```

In case you still need help please add the following information to your issue description:

- Do you have access to setup Cron Jobs?
- Does your server use Cron or Crontab?
- Does your server use a third-party Cron Job provider?
- Are you using VPS or Dedicated/Shared/Other Hosting?
- Can you provide screenshots of your Cron Job settings? (With personal information blurred out!)
- What type of server are you using? (CloudLinux CentOS 6, Windows IIS, or etc)

Data Integrity
-----------------------------------------

Some problems may be triggered by data integrity issues, especially if you use a `myisam` database.

The integrity check can be used to ensure the data integrity of your modules.
You can run the integrity check with the following command:

```
php yii integrity/run
```

Search index
-----------------------------------------

Especially when using the default [search index](search.md) you may have to rebuild the search index from time to time.
You should consider switching to another search provider if your user base or amount of content grows.

Please refer to the [Search System](search.md) part for more information about the search in HumHub.

Error after update
-----------------------------------------

- Check the version and compatibility of installed modules
- Check the compatibility of your module
- Check the [Theming Migration](../theme/migrate.md) guide.
- Check `Administration -> Information -> Database` or run `php yii migrate/up --includeModuleMigrations=1` to check for faulty migrations

> Note: Please always backup your installation prior to an update

Support Community
-----------------

There is also an active support community at: http://community.humhub.com


Github - Bugtracker
-------------------

**How to file a bug**
- Go to our issue tracker on GitHub: https://github.com/humhub/humhub/issues
- Search for existing issues using the search field at the top of the page
- File a new issue including the info listed below
- Thanks a ton for helping make Brackets higher quality!

**When filing a new bug, please include:**

- Descriptive title - use keywords so others can find your bug (avoiding duplicates)
- Specific and repeatable steps that trigger the problem
- What happens when you follow the steps, and what you expected to happen instead.
- Include the exact text of any error messages if applicable (or upload screenshots).
- HumHub version (or if you're pulling directly from Git, your current commit SHA - use git rev-parse HEAD)
- Did this work in a previous version? If so, also provide the version that it worked in.
- OS/PHP/MySQL version
- Modules? Confirm that you've tested with Debug > Reload Without Extensions first (see below).
- Any errors logged in Debug > Show Developer Tools - Console view

Direct Support (Enterprise Edition only)
----------------------------------------

As Enterprise Edition user you can create direct support inquiries at: 
`Administration -> Enterprise Edition -> Support`.

If you have problems related to the installation, please contact us at: info@humhub.com
