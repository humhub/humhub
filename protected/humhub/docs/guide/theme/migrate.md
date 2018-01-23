# Migration - Foreword

Here you will learn how you can adapt existing themes to work with the latest HumHub versions.

## Custom Stylesheets

Since **HumHub** version 1.2 you simply need to rebuild your theme stylesheet.
All changes or additions will be automatically applied to your theme.

> Note: If you disabled any default component import ([Prevent default component file imports](css.md#prevent-default-component-file-imports)) you may need to manually these adapt new changes.

## View Files

As mentioned in the [View Files](view.md) section, you may need to manually adjust overwritten view files if there are any changes made in the new **HumHub** version.

### Identifing changes using Git command

> Note: You'll need the git software and a current checkout for this approach.

Switch to the HumHub Git directory and execute:

``` 
git diff v1.0.0 v1.1.0 -- protected/humhub/modules/user/views/auth/login.php
```

This example commands shows all made changes on the login view file between HumHub version 1.0.0 and 1.1.0.
### Identifing changes using Diff Tool

TBD

### Identifing changes using GitHub

You can easily create a list of modified files in HumHub via GitHub.

Example link to get changes between HumHub v1.0 and v1.1: https://github.com/humhub/humhub/compare/v1.0.0...v1.1.0

On the GitHub page click on `Files changed` and search for your modified view files.
If you identified a changed file, click on it to see changes.

