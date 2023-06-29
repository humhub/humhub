<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\models\Setting;
use humhub\modules\like\activities\Liked;
use humhub\modules\like\models\Like;
use Throwable;
use Traversable;
use Yii;
use yii\db\ColumnSchemaBuilder;
use yii\db\Exception;

/**
 * Migration is the base class for representing a database migration.
 *
 * @see \yii\db\Migration
 */
class Migration extends \yii\db\Migration
{
    public const LOG_CATEGORY = 'migration';

    /**
     * @var string Main table of the current migration. MUST be overridden statically or initialized during
     *             static::__construct() or static::init()
     * @see static::safeAddForeignKeyToUserTable()
     */
    protected string $table;

    /**
     * @var string Name of the current database driver. Initialized during static::init().
     * @see static::timestampWithoutAutoUpdate()
     */
    protected string $driverName;

    /**
     * Initializes static::$driverName
     *
     * @return void
     * @since 1.15
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @see static::$driverName
     */
    public function init()
    {
        parent::init();

        $this->driverName = $this->db->getDriverName();
    }

    /**
     * @inheritdoc
     * @since 1.15.0
     */
    public function up()
    {
        return $this->saveUpDown([$this, 'safeUp']);
    }

    /**
     * @inheritdoc
     * @since 1.15.0
     */
    public function down()
    {
        return $this->saveUpDown([$this, 'safeDown']);
    }

    /**
     * Helper function for self::up() and self::down()
     *
     * @param array $action
     * @return bool|null
     * @since 1.15.0
     */
    protected function saveUpDown(array $action): ?bool
    {
        $transaction = $this->db->beginTransaction();
        try {
            if ($action() === false) {
                $transaction->rollBack();

                $this->logWarning('Migration {class} was not applied');

                return false;
            }
            $transaction->commit();
        } catch (Throwable $e) {
            $this->printException($e);
            $transaction->rollBack();
            $this->logException($e, end($action));

            return false;
        }

        return null;
    }

    /**
     * @param string $table
     * @param $columns
     * @param string|null $options
     * @return void
     * @see static::createTable()
     */
    protected function safeCreateTable(string $table, $columns, ?string $options = null): void
    {
        if (!$this->db->getTableSchema($table, true)) {
            $this->createTable($table, $columns, $options);
        } else {
            if (!$this->compact) {
                echo "    > skipped create table $table, table does already exist ...\n";
            }
            $this->logWarning("Tried to create an already existing existing table '$table'");
        }
    }

    /**
     * @param string $table
     * @return void
     * @see static::dropTable()
     * @noinspection PhpUnused
     */
    protected function safeDropTable(string $table): void
    {
        if ($this->db->getTableSchema($table, true)) {
            $this->dropTable($table);
        } else {
            if (!$this->compact) {
                echo "    > skipped drop table $table, table does not exist ...\n";
            }
            $this->logWarning("Tried to drop a non existing table '$table'");
        }
    }

    /**
     * Check if the column already exists in the table
     *
     * @param string $column
     * @param string $table
     * @return bool
     * @since 1.9.1
     */
    protected function columnExists(string $column, string $table): bool
    {
        $tableSchema = $this->db->getTableSchema($table, true);
        return $tableSchema && in_array($column, $tableSchema->columnNames, true);
    }

    protected function safeDropColumn(string $table, string $column): void
    {
        if ($this->columnExists($column, $table)) {
            $this->dropColumn($table, $column);
        } else {
            if (!$this->compact) {
                echo "    > skipped drop column $column from table $table, column does not exist ...\n";
            }
            $this->logWarning("Tried to drop a non existing column '$column' from table '$table'");
        }
    }

    /**
     * @param string $table table name
     * @param string $column column name
     * @param string|ColumnSchemaBuilder $type column type
     * @return void
     * @see static::addColumn()
     */
    protected function safeAddColumn(string $table, string $column, $type): void
    {
        if (!$this->columnExists($column, $table)) {
            $this->addColumn($table, $column, $type);
        } else {
            if (!$this->compact) {
                echo "    > skipped add column $column from table $table, column does already exist ...\n";
            }
            $this->logWarning("Tried to add an already existing column '$column' on table '$table'");
        }
    }

    /**
     * Check if the index already exists in the table
     *
     * @param string $index
     * @param string $table
     * @return bool
     * @throws Exception
     * @since 1.9.1
     */
    protected function indexExists(string $index, string $table): bool
    {
        return (bool) $this->db->createCommand('SHOW KEYS FROM ' . $this->db->quoteTableName($table) .
            ' WHERE Key_name = ' . $this->db->quoteValue($index))
            ->queryOne();
    }

    /**
     * Check if the foreign index already exists in the table
     *
     * @param string $index
     * @param string $table
     * @return bool
     * @throws Exception
     * @since 1.9.1
     */
    protected function foreignIndexExists(string $index, string $table): bool
    {
        return (bool) $this->db->createCommand('SELECT * FROM information_schema.key_column_usage
            WHERE REFERENCED_TABLE_NAME IS NOT NULL
              AND TABLE_NAME = ' . $this->db->quoteValue($table) . '
              AND TABLE_SCHEMA = ' . $this->db->quoteValue($this->getDsnAttribute('dbname')) . '
              AND CONSTRAINT_NAME = ' . $this->db->quoteValue($index))
            ->queryOne();
    }

    /**
     * Create an index if it doesn't exist yet
     *
     * @param string $index
     * @param string $table
     * @param string|array $columns
     * @param bool $unique
     * @throws Exception
     * @since 1.9.1
     * @see static::createIndex()
     */
    protected function safeCreateIndex(string $index, string $table, $columns, bool $unique = false): void
    {
        if ($this->indexExists($index, $table)) {
            if (!$this->compact) {
                echo "    > skipped create index $index in the table $table, index already exists ...\n";
            }
            $this->logWarning("Tried to create an already existing index '$index' on table '$table'");
            return;
        }

        $this->createIndex($index, $table, $columns, $unique);
    }

    /**
     * Drop an index if it exists in the table
     *
     * @param string $index
     * @param string $table
     * @throws Exception
     * @since 1.9.1
     * @see static::dropIndex()
     * @noinspection PhpUnused
     */
    protected function safeDropIndex(string $index, string $table): void
    {
        if (!$this->indexExists($index, $table)) {
            if (!$this->compact) {
                echo "    > skipped drop index $index from the table $table, index does not exist ...\n";
            }
            $this->logWarning("Tried to drop a non existing index '$index' from table '$table'");
            return;
        }

        $this->dropIndex($index, $table);
    }

    /**
     * Add a primary index if it doesn't exist yet
     *
     * @param string $index
     * @param string $table
     * @param string|array $columns
     * @throws Exception
     * @since 1.9.1
     * @see static::addPrimaryKey()
     */
    protected function safeAddPrimaryKey(string $index, string $table, $columns): void
    {
        if ($this->indexExists('PRIMARY', $table)) {
            if (!$this->compact) {
                echo "    > skipped create primary index $index in the table $table, primary index already exists ...\n";
            }
            $this->logWarning("Tried to create an already existing primary index '$index' on table '$table'");
            return;
        }

        $this->addPrimaryKey($index, $table, $columns);
    }

    /**
     * Drop a primary index if it exists in the table
     *
     * @param string $index
     * @param string $table
     * @throws Exception
     * @since 1.9.1
     * @see static::dropPrimaryKey()
     * @noinspection PhpUnused
     */
    protected function safeDropPrimaryKey(string $index, string $table): void
    {
        if (!$this->indexExists('PRIMARY', $table)) {
            if (!$this->compact) {
                echo "    > skipped drop primary index $index from the table $table, primary index does not exist ...\n";
            }
            $this->logWarning("Tried to drop a non existing primary index '$index' from table '$table'");
            return;
        }

        $this->dropPrimaryKey($index, $table);
    }

    /**
     * Add a foreign index if it doesn't exist yet
     *
     * @param string $index
     * @param string $table
     * @param string|array $columns
     * @param string $refTable
     * @param string|array $refColumns
     * @param string|null $delete
     * @param string|null $update
     * @throws Exception
     * @since 1.9.1
     * @see static::addForeignKey()
     */
    protected function safeAddForeignKey(string $index, string $table, $columns, string $refTable, $refColumns, ?string $delete = null, ?string $update = null): void
    {
        if ($this->foreignIndexExists($index, $table)) {
            if (!$this->compact) {
                echo "    > skipped create foreign index $index in the table $table, foreign index already exists ...\n";
            }
            $this->logWarning("Tried to create an already existing foreign index '$index' on table '$table'");
            return;
        }

        $this->addForeignKey($index, $table, $columns, $refTable, $refColumns, $delete, $update);
    }

    /**
     * Drop a foreign if it exists in the table
     *
     * @param string $index
     * @param string $table
     * @throws Exception
     * @since 1.9.1
     * @noinspection PhpUnused
     */
    protected function safeDropForeignKey(string $index, string $table): void
    {
        if (!$this->foreignIndexExists($index, $table)) {
            if (!$this->compact) {
                echo "    > skipped drop foreign index $index from the table $table, foreign index does not exist ...\n";
            }
            $this->logWarning("Tried to drop a non existing foreign index '$index' from table '$table'");
            return;
        }

        $this->dropForeignKey($index, $table);
    }

    /**
     * Add a foreign key constraint to the user table on the field indicated.
     *
     * @param string $sourceField Source field referencing `user.id`
     * @return void
     * @throws Exception
     * @see static::$table
     * @since 1.15
     */
    public function safeAddForeignKeyToUserTable(string $sourceField): void
    {
        // add foreign key for table `user`
        $this->safeAddForeignKey(
            "fk-{$this->table}-$sourceField",
            $this->table,
            $sourceField,
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * Add a foreign key constraint to the user table on the `updated_by` field.
     *
     * @return void
     * @throws Exception
     * @see static::safeAddForeignKeyToUserTable()
     * @since 1.15
     * @noinspection PhpUnused
     */
    public function safeAddForeignKeyUpdatedBy(): void
    {
        $this->safeAddForeignKeyToUserTable('updated_by');
    }

    /**
     * Add a foreign key constraint to the user table on the `created_by` field.
     *
     * @return void
     * @throws Exception
     * @see static::safeAddForeignKeyToUserTable()
     * @since 1.15
     * @noinspection PhpUnused
     */
    public function safeAddForeignKeyCreatedBy(): void
    {
        $this->safeAddForeignKeyToUserTable('created_by');
    }


    /**
     * Returns the field configuration for a FK field
     *
     * @return ColumnSchemaBuilder
     * @since 1.15
     * @noinspection PhpUnused
     */
    public function integerReferenceKey(): ColumnSchemaBuilder
    {
        return $this->integer(11)
            ->notNull()
            ;
    }

    /**
     * Returns the field configuration for a timestamp field that does not get automatically updated by mysql in case it
     * being the first timestamp column in the table.
     *
     * @param $precision
     * @return ColumnSchemaBuilder
     * @since 1.15
     * @see https://dev.mysql.com/doc/refman/8.0/en/timestamp-initialization.html
     */
    public function timestampWithoutAutoUpdate($precision = null): ColumnSchemaBuilder
    {
        // Make sure to define default table storage engine
        return in_array($this->driverName, ['mysql', 'mysqli'], true)
            ? $this->timestamp($precision)
                ->append('DEFAULT CURRENT_TIMESTAMP')
            : $this->timestamp($precision);
    }


    /**
     * Renames a class
     *
     * This is often required because some classes are also stored in database
     * e.g. for polymorphic relations.
     *
     * This method is also required for 0.20 namespace migration!
     *
     * @param string $oldClass
     * @param string $newClass
     * @throws Exception
     */
    protected function renameClass(string $oldClass, string $newClass): void
    {
        $this->updateSilent('activity', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('activity', ['class' => $newClass], ['class' => $oldClass]);
        $this->updateSilent('comment', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('content', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('file', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('like', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('notification', ['source_class' => $newClass], ['source_class' => $oldClass]);
        $this->updateSilent('notification', ['class' => $newClass], ['class' => $oldClass]);
        $this->updateSilent('user_mentioning', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('user_follow', ['object_model' => $newClass], ['object_model' => $oldClass]);

        //$this->updateSilent('wall', ['object_model' => $newClass], ['object_model' => $oldClass]);

        /**
         * Looking up "NewLike" activities with this className
         * Since 0.20 the className changed to Like (is not longer the target object e.g. post)
         *
         * Use raw query for better performance.
         */
        $updateSql = "
            UPDATE activity
            LEFT JOIN `like` ON like.object_model=activity.object_model AND like.object_id=activity.object_id
            SET activity.object_model=:likeModelClass, activity.object_id=like.id
            WHERE activity.class=:likedActivityClass AND like.id IS NOT NULL and activity.object_model != :likeModelClass
        ";

        Yii::$app->db->createCommand($updateSql, [
            ':likeModelClass' => Like::class,
            ':likedActivityClass' => Liked::class
        ])->execute();
    }

    /**
     * Creates and executes an UPDATE SQL statement without any output.
     * The method will properly escape the column names and bind the values to be updated.
     *
     * @param string $table the table to be updated.
     * @param array|Traversable $columns the column data (name => value) to be updated.
     * @param array|string $condition the conditions that will be put in the WHERE part. Please
     * refer to [[Query::where()]] on how to specify conditions.
     * @param array|Traversable $params the parameters to be bound to the query.
     * @throws Exception
     */
    public function updateSilent(string $table, $columns, $condition = '', $params = []): void
    {
        $this->db->createCommand()->update($table, $columns, $condition, $params)->execute();
    }

    /**
     * Creates and executes an INSERT SQL statement without any output
     * The method will properly escape the column names, and bind the values to be inserted.
     * @param string $table the table that new rows will be inserted into.
     * @param array|Traversable $columns the column data (name => value) to be inserted into the table.
     * @throws Exception
     */
    public function insertSilent(string $table, $columns): void
    {
        $this->db->createCommand()->insert($table, $columns)->execute();
    }

    /**
     * Returns whether this is a completely new installation with an empty database (installation process).
     *
     * @return bool
     * @since 1.8
     */
    protected function isInitialInstallation(): bool
    {
        return (!Setting::isInstalled());
    }

    /**
     * Get data from database dsn config
     *
     * @since 1.9.3
     * @param string $name 'host', 'port', 'dbname'
     * @return string|null
     */
    private function getDsnAttribute(string $name): ?string
    {
        return preg_match('/' . preg_quote($name) . '=([^;]*)/', $this->db->dsn, $match)
            ? $match[1]
            : null;
    }

    /**
     * @param string $message Message to be logged
     * @param array $params Parameters to translate in message
     * @return void
     * @since 1.15.0
     */
    protected function logError(string $message, array $params = []): void
    {
        Yii::error($this->logTranslation($message, $params), self::LOG_CATEGORY);
    }

    /**
     * @param string $message Message to be logged
     * @param array $params Parameters to translate in message
     * @return void
     * @since 1.15.0
     */
    protected function logWarning(string $message, array $params = []): void
    {
        Yii::warning($this->logTranslation($message, $params), self::LOG_CATEGORY);
    }

    /**
     * @param string $message Message to be logged
     * @param array $params Parameters to translate in message
     * @return void
     * @since 1.15.0
     * @noinspection PhpUnused
     */
    protected function logInfo(string $message, array $params = []): void
    {
        Yii::info($this->logTranslation($message, $params), self::LOG_CATEGORY);
    }

    /**
     * @param string $message Message to be logged
     * @param array $params Parameters to translate in message
     * @return void
     * @since 1.15.0
     */
    protected function logDebug(string $message, array $params = []): void
    {
        Yii::debug($this->logTranslation($message, $params), self::LOG_CATEGORY);
    }


    /**
     * Translate log messages
     *
     * @param string $message Message to be logged
     * @param array $params Parameters to translate in message
     * @return void
     * @since 1.15.0
     */
    protected function logTranslation(string $message, array $params = []): string
    {
        // make sure the class is set
        $params['class'] ??= static::class;

        if (false === strpos('{class}', $message)) {
            $message = "Migration {class}: $message";
        }

        // enclose keys in curly brackets
        $params = array_combine(array_map(static fn($key) => "{{$key}}", $params), $params);

        // replace "{key}" with "value"
        return strtr($message, $params);
    }

    /**
     * Get data from database dsn config
     *
     * @param Throwable $e The Throwable to be logged
     * @param string $method The Method that was running
     * @since 1.15.0
     */
    protected function logException(Throwable $e, string $method): void
    {
        $this->logError(
            'Migration {class}::{method}() failed: {message} ({file}:{line}). See debug log for full trace.',
            [
                'method' => $method,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        );

        $this->logDebug($e->getTraceAsString());
    }

    /**
     * Required, since parent is private ...
     *
     * @param Throwable $t
     */
    private function printException(Throwable $t): void
    {
        echo 'Exception: ' . $t->getMessage() . ' (' . $t->getFile() . ':' . $t->getLine() . ")\n";
        echo $t->getTraceAsString() . "\n";
    }
}
