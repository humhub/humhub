Changelog
---------

#### 0.8.0 (to be released)

- Enh: #12 added customization of 'migrations' subdirectory via $migrationSubPath (cebe thanks to redguy666)
- Enh: show `connectionString` of active database component (schmunk42)

#### 0.7.1 (2012-01-31)

- fix for getTable trying to hit a db cache and die in endless loop (cebe)
- made sure that  mark  and  to  action are working correctly (cebe)

#### 0.7.0 (2012-01-28)

- adjusted sql commands to be compatible with nearly all pdo db systems (cebe)
- modules are now loaded from yii application config if not set (cebe)
- improved create action error handling (cebe)

#### 0.6.0 (2011-09-12) not released, did not work

- adjusted sql commands to be compatible with sqlite and postgres (cebe thanks to redguy)
- added compatibility with CDbMigration, migrations need not extend EDbMigration (cebe)

#### 0.5.0 (2011-08-09)

- implemented mark-action so it now works with modules (cebe)
- implemented to-action so it now works with modules (cebe)
- fixed problem that base-migrations where not cretated on mark command (cebe)

#### 0.4.0 (2011-08-08)

- added confirm() method to EDbMigration (cebe)

#### 0.3.1 (2011-08-05)

- fixed a problem with finding new migrations (cebe)
- added base migration for every single module (cebe)
- fixed problem with history and down migration when --module parameter is set (cebe)
- added $moduleDelimiter property and replaced string function with multibyte versions (cebe)
- complete refactoring of basic functionality, more stability and more straight forward (cebe)

#### 0.1.0 (2011-08-04)

- Initial public release (cebe)
- module support for migrations
- extended execute() of CDbMigration with parameter $verbose
