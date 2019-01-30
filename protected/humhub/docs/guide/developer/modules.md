Module Developement
===================

The following guide describes the basic module structure and extended module features as well as important considerations regarding your own custom modules.
Since HumHub is based on the [Yii Application Framework](http://www.yiiframework.com/doc-2.0) you should at least be familiar with the basic concepts of this framework
before writing your own code as:

 - [Basic Application Structure](https://www.yiiframework.com/doc/guide/2.0/en/structure-overview)
 - [Controllers](https://www.yiiframework.com/doc/guide/2.0/en/structure-controllers)
 - [Models](https://www.yiiframework.com/doc/guide/2.0/en/structure-models)
 - [Views](https://www.yiiframework.com/doc/guide/2.0/en/structure-views)

You should also follow the [Coding Standards](coding-standards.md) and keep an eye on the [Migration Guide](modules-migrate.md) in order to
keep your module compatible with new HumHub versions and facilitate new features.

## Introduction

Before starting with the development of your custom module, first consider the following **module options**:

- Can my module be [enabled on user and/or space level](modules-base-class.md#use-of-contentcontainermodule)?
- Does my module produce [content](content.md)?
- Does my module produce [stream entries](stream.md)?
- Does my module add any [sidebar snippets](snippet.md)?
- Do I need to [extend or change the default behaviour](module-change-behavior.md) of core components?
- Do I need specific [permissions](permissions.md) for my module?
- Does my module create any [notifications](notifications.md) or [activities](activities.md)?
- Should [guest users](permissions.md#guest-access) have access to some parts of my module?

Furthermore you may have to consider the following **issues**:

- [Module settings and configuration](settings.md)
- [Append a module to a specific navigation](module-change-behavior.md#extend-menus)
- [Client side developement](javascript-index.md)
- [Schema Migrations and Integrity](models.md)
- [Testing](testing.md)
- [File handling](files.md)
- [Events](events.md)
- [Translation](i18n.md)
- [Live UI updates](live.md)
- [Security](security.md)
- [Embedded Themes](../theme/module.md)


> Info: You may want to use the [devtools Module](https://github.com/humhub/humhub-modules-devtools) to create a module skeleton.
