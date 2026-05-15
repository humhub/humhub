# Pull Requests

## Issue Reference

All new features and bug fixes should have an associated issue to provide a single point of reference for discussion and documentation. Take a few minutes to look through the existing issue list for one that matches the contribution you intend to make. If you find one already on the issue list, then please leave a comment on that issue indicating you intend to work on that item. If you do not find an existing issue matching what you intend to work on, please open a new issue or create a pull request directly if it is straightforward fix. This will allow the team to review your suggestion, and provide appropriate feedback along the way.

For small changes or documentation issues or straightforward fixes, you don't need to create an issue, a pull request is enough in this case. In such cases you can use the Pull Request ID in the Changelog. 

## Git - Branches

** Core Repository (github.com/humhub/humhub) **

- `master` For bugfixes and critical translation updates 
- `develop` For all new features, optimizations, refactoring, complex non-critical bug fixes

** For new branches **
Usually we use enh/[issue-id]-some-info or fix/[issue-id]-some-info
  
** Module Repositories (github.com/humhub and github.com/humhub-contrib) **

Usually there is only one `master` or `main` branch in module repositories. If there is an active `develop` branch and it is a major change PR, please use this branch if necessary.

## Changelog

Edit the CHANGELOG file to include your change, you should insert this at the bottom of the file under the first heading (the version that is currently under development), the line in the change log should look like one of the following:

```
Bug #999: a description of the bug fix (Your Name)
Enh #999: a description of the enhancement (Your Name)
```

`#999` is the GitHub issue number. If there is no GitHub issue, or the GitHub issue is in a different repository, please use the GitHub PR number.

The changelog files are located in the following locations:

** Core Repository **

- `/CHANGELOG.md`

** Module Repositories ** 

- `/docs/CHANGELOG.md` 

For modules, it can be the case that no "Under Development" version exists in the changelog file yet. In this case please create a new version section and also adjust the file `module.json`. 

For minor enhancements and bugfixes, only the minor version has to be changed. e.g. 1.0.**1** If a newer HumHub core version is required (`minVersion` adjustment in `module.json`) or a major module feature was implemented, the major version should be changed. e.g. 1.**1**.0

## Tests

TBD

Unit tests are always welcome. Tested and well covered code greatly simplifies the task of checking your contributions. 

## CLA

To have your contribution accepted, you, as the author of the pull request, need to accept our Contributor License Agreement (CLA). A CLA is an agreement that ensures you have the necessary rights and permissions to contribute the code or changes you submit to our open-source project. You can review and accept the CLA at this link: https://cla-assistant.io/humhub/humhub

## Documentation

- **Core Changes** Should always be briefly described in the documentation in the [Migration Guide](https://github.com/humhub/humhub/blob/develop/MIGRATE-DEV.md).

- **Module Features** New features should also be added in the Readme files if necessary.  
