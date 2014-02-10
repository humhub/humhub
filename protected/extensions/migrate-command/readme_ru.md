Extended Migration Command
==========================

Данное расширение — расширенная версия [миграций Yii](http://www.yiiframework.com/doc/guide/1.1/ru/database.migration),
которая добавляет поддержку модулей и некоторые дополнительные возможности. Если у вас есть пожелания
или вы нашли ошибки, пишите [в трекер проекта](https://github.com/yiiext/migrate-command/issues) или [напрямую автору](mailto:mail@cebe.cc) (по-английски).

Возможнсти
----------

* Поддержка модулей (миграции в отельных директориях для каждого модуля), а именно:
 * ...включение и выключение модулей
 * ...[добавить](#hh8) новый модуль через migrate up
 * ...[удалить](#hh9) модуль через migratie down
 * ...[выбрать модули](#hh7) для которых будут применены миграции
 * ...зависимости модулей (в планах)
 * ...различные шаблоны для миграций различных модулей (в планах)

Ссылки
------

* [GIT](https://github.com/yiiext/migrate-command)
* [Документация по миграциям Yii](http://www.yiiframework.com/doc/guide/1.1/ru/database.migration)
* [Обсуждение](http://www.yiiframework.com/forum/index.php?/topic/22471-extension-extended-database-migration/)
* [Сообщить об ошибке или предложить функционал](https://github.com/yiiext/migrate-command/issues)

Требования
----------

* Yii 1.1.6 и выше (именно в этой версии появились миграции)
если вы скопируете MigrateCommand и [CDbMigration](http://www.yiiframework.com/doc/api/1.1/CDbMigration),
то сможете использовать данное расширение с любой версией Yii.

Установка
---------

* Распаковать в `protected/extensions`.
* Добавить следующее в [конфиг](http://www.yiiframework.com/doc/guide/1.1/ru/database.migration#customizing-migration-command):

```php
'commandMap' => array(
	'migrate' => array(
		// псевдоним директории, в которую распаковано расширение
		'class' => 'application.extensions.yiiext.commands.migrate.EMigrateCommand',
		// путь для хранения общих миграций
		'migrationPath' => 'application.db.migrations',
		// имя таблицы с версиями
		'migrationTable' => 'tbl_migration',
		// имя псевдомодуля для общих миграций. По умолчанию равно "core".
		'applicationModuleName' => 'core',
		// определяем все модули, для которых нужны миграции  (в противном случае, модули будут взяты из конфигурации Yii)
		'modulePaths' => array(
			'admin'      => 'application.modules.admin.db.migrations',
			'user'       => 'application.modules.user.db.migrations',
			'yourModule' => 'application.any.other.path.possible',
			// ...
		),
		// можно задать имя поддиректории для хранения миграций в директории модуля
		'migrationSubPath' => 'migrations',
		// отключаем некоторые модули
		'disabledModules' => array(
			'admin', 'anOtherModule', // ...
		),
		// название компонента для подключения к базе данных
		'connectionID'=>'db',
		// алиас шаблона для новых миграций
		'templateFile'=>'application.db.migration_template',
	),
),
```
**Важно:** если вы уже использовали MigrateCommand, необходимо добавить столбец module в таблицу версий migrationTable:

```sql
ALTER TABLE `tbl_migration` ADD COLUMN `module` varchar(32) DEFAULT NULL;
UPDATE `tbl_migration` SET module='core';
```

Использование
-------------

###Обычное использование

Для просмотра всех доступных параметров и коротких примеров по использованию можно
воспользоваться `yiic migrate help`.

Основы работы с миграциями описаны в [официальном руководстве](http://www.yiiframework.com/doc/guide/1.1/ru/database.migration).
Если вы не использовали миграции до этого момента, стоит начать именно с него.
Использование расширенной версии не сильно отличается от обычной.
Единственная отличная команда — это [create](http://www.yiiframework.com/doc/guide/1.1/ru/database.migration#creating-migrations),
для которой требуется указание имя модуля:

```
yiic migrate create modulename create_user_table
```

Команда, приведённая выше создаёт миграцию 'create_user_table' в модуле 'modulename'. Обычное использование

```
yiic migrate create create_user_table
```

создаёт общую миграцию 'create_user_table' (в псевдомодуле `core`).

###Параметр --module

Во всех остальных командах (`up`, `down`, `history`, `new`, `to` и `mark`) можно использовать
параметр `--module=<modulenames>`, где `<modulenames>` — разделённый запятыми список
имён модулей, либо просто имя модуля. Данный параметр позволяет ограничить действие команды
определёнными модулями.
Примеры:

```
yiic migrate new --module=core
```

Покажет все общие миграции (для модуля `core`).

```
yiic migrate up 5 --module=core,user
```

Применит пять миграций в модулях `core` и `user`. Миграции остальных модулей будут
проигнорированы.

```
yiic migrate history --module=core,user
```

Покажет, какие миграции применены к модулям `core` и `user`.
Если не указать модуль, команда ведёт себя как та, что включена в Yii за тем исключением,
что применяется ещё и ко всем модулям.

###Добавление модуля

Просто подключите модуль в файле конфигурации и запустите `yiic migrate up --module=yourModule`.

###Удаление модуля

Запустите `yiic migrate to m000000_000000 --module=yourModule`. Для этого все миграции
должны реализовывать метод `down()`.

