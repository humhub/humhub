# Git Repositories

This section describes the organization of Git Repositories for Modules in GitHub/GitLab environments operated by HumHub.

## Branches

If a public module release is planned and possible in a timely manner, changes can be comitted (PR) directly in the `master` Branch. (If no active `develop` branch exists.)

Before making changes in the `develop` branch, make sure that it is up to date, since this branch is not always used in the regular maintenance and development of the modules.

## Versioning

Even though the HumHub core currently follows its own versioning scheme, modules should rely on [Semantic Versioning](https://semver.org/).

## Increase required minimum HumHub Version

If a feature necessarily requires a newer **HumHub Core** version than the one specified in the current `modules.json`, at least the **Minor Version Part** of the module must be increased. 
(Changing the patch level is not sufficient in this cases.)

If the required **HumHub Core** version has not yet been released, the change must be made in the `develop` branch of the module.

Additionally the new minimum version of HumHub should be added in the `docs/CHANGELOG.md` file. 

## New Version Marketplace Releases

Steps to release a new module version:

1. Merge `develop` into `master` (if applicable)
2. Add release date to `docs/CHANGELOG.md` and check version number in `module.json`.
3. Create a Git tag for the new version e.g. ```git add -a "1.1.0" -m "Release 1.1.0"```

## Translations

For modules that are translated via our translation service on https://translate.humhub.org:

- The `message` files MUST NOT be generated automatically using the `yii message` command.
- Modifications to the `message` files may only be made via the translate.humhub.org site. 
