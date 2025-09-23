<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

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
     * @var Throwable|null Exception that occurred during migration
     */
    protected ?Throwable $lastException = null;

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
     *
     * @return bool|null
     * @since 1.15.0
     */
    protected function saveUpDown(array $action): ?bool
    {
        $transaction = $this->db->beginTransaction();
        try {
            if ($action() === false) {
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }

                $this->logWarning('Migration {class} was not applied');

                return false;
            }

            if ($transaction->isActive) {
                $transaction->commit();
            }
        } catch (Throwable $e) {
            // Just to be sure: check if the exception was caused by committing a transaction that was not active
            if (
                ($e instanceof Exception)
                && $e->errorInfo[0] === '42000'
                && $e->errorInfo[1] === 1305
                && $e->errorInfo[2] === 'SAVEPOINT LEVEL1 does not exist'
            ) {
                // in this case, assume all is well
                return null;
            }

            $this->printException($e);

            // otherwise, if still active, roll back the transaction
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            $this->logException($e, end($action));

            return false;
        }

        return null;
    }

    /**
     * @param string $table
     * @param $columns
     * @param string|null $options
     *
     * @return bool indicates if the table has been created
     * @see static::createTable()
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function safeCreateTable(string $table, $columns, ?string $options = null)
    {
        if (!$this->db->getTableSchema($table, true)) {
            $this->createTable($table, $columns, $options);
            return true;
        }

        if (!$this->compact) {
            echo "    > skipped create table $table, table does already exist ...\n";
        }
        $this->logWarning("Tried to create an already existing existing table '$table'");
        return false;
    }

    /**
     * @param string $table
     *
     * @return bool indicates if the table has been dropped
     * @see static::dropTable()
     * @noinspection PhpUnused
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function safeDropTable(string $table)
    {
        if ($this->db->getTableSchema($table, true)) {
            $this->dropTable($table);
            return true;
        }

        if (!$this->compact) {
            echo "    > skipped drop table $table, table does not exist ...\n";
        }
        $this->logWarning("Tried to drop a non existing table '$table'");
        return false;
    }

    /**
     * Check if the column already exists in the table
     *
     * @param string $column
     * @param string $table
     *
     * @return bool
     * @since 1.9.1
     */
    protected function columnExists(string $column, string $table): bool
    {
        $tableSchema = $this->db->getTableSchema($table, true);
        return $tableSchema && in_array($column, $tableSchema->columnNames, true);
    }

    /**
     * @return bool indicates if column has been dropped
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function safeDropColumn(string $table, string $column)
    {
        if ($this->columnExists($column, $table)) {
            $this->dropColumn($table, $column);
            return true;
        }

        if (!$this->compact) {
            echo "    > skipped drop column $column from table $table, column does not exist ...\n";
        }
        $this->logWarning("Tried to drop a non existing column '$column' from table '$table'");
        return false;
    }

    /**
     * @param string $table table name
     * @param string $column column name
     * @param string|ColumnSchemaBuilder $type column type
     *
     * @return bool indicates if column has been added
     * @see static::addColumn()
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function safeAddColumn(string $table, string $column, $type)
    {
        if (!$this->columnExists($column, $table)) {
            $this->addColumn($table, $column, $type);
            return true;
        }

        if (!$this->compact) {
            echo "    > skipped add column $column from table $table, column does already exist ...\n";
        }
        $this->logWarning("Tried to add an already existing column '$column' on table '$table'");
        return false;
    }

    /**
     * Rename the column
     *
     * @param string $table the table whose column is to be renamed
     * @param string $name the old name of the column
     * @param string $newName the new name of the column
     *
     * @return bool indicates if column has been renamed
     * @see static::renameColumn()
     * @noinspection PhpMissingReturnTypeInspection
     * @since 1.17
     */
    protected function safeRenameColumn(string $table, string $name, string $newName): bool
    {
        if (!$this->columnExists($name, $table)) {
            if (!$this->compact) {
                echo "    > skipped rename column from $name to $newName in table $table, column $name doesn't exist ...\n";
            }
            $this->logWarning("Tried to rename a not existing column '$name' to '$newName' in table '$table'");
            return false;
        }

        if ($this->columnExists($newName, $table)) {
            if (!$this->compact) {
                echo "    > skipped rename column from $name to $newName in table $table, column $newName already exists ...\n";
            }
            $this->logWarning("Tried to rename to already existing column '$newName' from '$name' in table '$table'");
            return false;
        }

        $this->renameColumn($table, $name, $newName);
        return true;
    }

    /**
     * Check if the index already exists in the table
     *
     * @param string $index
     * @param string $table
     *
     * @return bool
     * @throws Exception
     * @since 1.9.1
     */
    protected function indexExists(string $index, string $table): bool
    {
        return (bool) $this->db->createCommand('SHOW KEYS FROM ' . $this->db->quoteTableName($table)
            . ' WHERE Key_name = ' . $this->db->quoteValue($index))
            ->queryOne();
    }

    /**
     * Check if the foreign index already exists in the table
     *
     * @param string $index
     * @param string $table
     *
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
     *
     * @return bool indicates if the index has been created
     * @throws Exception
     * @since 1.9.1
     * @see static::createIndex()
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function safeCreateIndex(string $index, string $table, $columns, bool $unique = false)
    {
        if (!$this->indexExists($index, $table)) {
            $this->createIndex($index, $table, $columns, $unique);
            return true;
        }

        if (!$this->compact) {
            echo "    > skipped create index $index in the table $table, index already exists ...\n";
        }
        $this->logWarning("Tried to create an already existing index '$index' on table '$table'");
        return false;
    }

    /**
     * Drop an index if it exists in the table
     *
     * @param string $index
     * @param string $table
     *
     * @return bool indicates if the index has been dropped
     * @throws Exception
     * @since 1.9.1
     * @see static::dropIndex()
     * @noinspection PhpUnused
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function safeDropIndex(string $index, string $table)
    {
        if ($this->indexExists($index, $table)) {
            $this->dropIndex($index, $table);
            return true;
        }

        if (!$this->compact) {
            echo "    > skipped drop index $index from the table $table, index does not exist ...\n";
        }
        $this->logWarning("Tried to drop a non existing index '$index' from table '$table'");
        return false;
    }

    /**
     * Add a primary index if it doesn't exist yet
     *
     * @param string $index
     * @param string $table
     * @param string|array $columns
     *
     * @return bool indicates if key has been added
     * @throws Exception
     * @since 1.9.1
     * @see static::addPrimaryKey()
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function safeAddPrimaryKey(string $index, string $table, $columns)
    {
        if (!$this->indexExists('PRIMARY', $table)) {
            $this->addPrimaryKey($index, $table, $columns);
            return true;
        }

        if (!$this->compact) {
            echo "    > skipped create primary index $index in the table $table, primary index already exists ...\n";
        }
        $this->logWarning("Tried to create an already existing primary index '$index' on table '$table'");
        return false;
    }

    /**
     * Drop a primary index if it exists in the table
     *
     * @param string $index
     * @param string $table
     *
     * @return bool indicates if key has been added
     * @throws Exception
     * @since 1.9.1
     * @see static::dropPrimaryKey()
     * @noinspection PhpUnused
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function safeDropPrimaryKey(string $index, string $table)
    {
        if ($this->indexExists('PRIMARY', $table)) {
            $this->dropPrimaryKey($index, $table);
            return true;
        }

        if (!$this->compact) {
            echo "    > skipped drop primary index $index from the table $table, primary index does not exist ...\n";
        }
        $this->logWarning("Tried to drop a non existing primary index '$index' from table '$table'");
        return false;
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
     *
     * @return bool indicates if key has been added
     * @throws Exception
     * @since 1.9.1
     * @see static::addForeignKey()
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function safeAddForeignKey(string $index, string $table, $columns, string $refTable, $refColumns, ?string $delete = null, ?string $update = null)
    {
        if (!$this->foreignIndexExists($index, $table)) {
            $this->addForeignKey($index, $table, $columns, $refTable, $refColumns, $delete, $update);
            return true;
        }

        if (!$this->compact) {
            echo "    > skipped create foreign index $index in the table $table, foreign index already exists ...\n";
        }
        $this->logWarning("Tried to create an already existing foreign index '$index' on table '$table'");
        return false;
    }

    /**
     * Drop a foreign key if it exists in the table
     *
     * @param string $index
     * @param string $table
     *
     * @return bool indicates if key has been dropped
     * @throws Exception
     * @since 1.9.1
     * @noinspection PhpUnused
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function safeDropForeignKey(string $index, string $table)
    {
        if ($this->foreignIndexExists($index, $table)) {
            $this->dropForeignKey($index, $table);
            return true;
        }

        if (!$this->compact) {
            echo "    > skipped drop foreign index $index from the table $table, foreign index does not exist ...\n";
        }
        $this->logWarning("Tried to drop a non existing foreign index '$index' from table '$table'");
        return false;
    }

    /**
     * Add a foreign key constraint to the user table on the field indicated.
     *
     * @param string $sourceField Source field referencing `user.id`
     *
     * @return bool indicates if key has been added
     * @throws Exception
     * @see static::$table
     * @since 1.15
     * @noinspection PhpMissingReturnTypeInspection
     **/
    public function safeAddForeignKeyToUserTable(string $sourceField)
    {
        // add foreign key for table `user`
        return $this->safeAddForeignKey(
            sprintf("fk-%s-%s", $this->table, $sourceField),
            $this->table,
            $sourceField,
            'user',
            'id',
            'CASCADE',
            'CASCADE',
        );
    }

    /**
     * Add a foreign key constraint to the user table on the `updated_by` field.
     *
     * @return bool indicates if key has been added
     * @throws Exception
     * @see static::safeAddForeignKeyToUserTable()
     * @since 1.15
     * @noinspection PhpUnused
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function safeAddForeignKeyUpdatedBy()
    {
        return $this->safeAddForeignKeyToUserTable('updated_by');
    }

    /**
     * Add a foreign key constraint to the user table on the `created_by` field.
     *
     * @return bool indicates if key has been added
     * @throws Exception
     * @see static::safeAddForeignKeyToUserTable()
     * @since 1.15
     * @noinspection PhpUnused
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function safeAddForeignKeyCreatedBy()
    {
        return $this->safeAddForeignKeyToUserTable('created_by');
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
            ->notNull();
    }

    /**
     * Returns the field configuration for a timestamp field that does not get automatically updated by mysql in case it
     * being the first timestamp column in the table.
     *
     * @param $precision
     *
     * @return ColumnSchemaBuilder
     * @since 1.15
     * @see https://dev.mysql.com/doc/refman/8.0/en/timestamp-initialization.html
     */
    public function timestampWithoutAutoUpdate($precision = null): ColumnSchemaBuilder
    {
        // Make sure to define the default table storage engine
        return in_array($this->driverName, ['mysql', 'mysqli'], true)
            ? $this->timestamp($precision)
                ->append('DEFAULT CURRENT_TIMESTAMP')
            : $this->timestamp($precision);
    }


    /**
     * Renames a class
     *
     * This is often required because some classes are also stored in the database,
     * e.g. for polymorphic relations.
     *
     * This method is also required for 0.20 namespace migration!
     *
     * @param string $oldClass
     * @param string $newClass
     *
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
         * Since 0.20 the className changed to Like (is not longer the target object, e.g. post)
         *
         * Use a raw query for better performance.
         */
        $updateSql = "
            UPDATE activity
            LEFT JOIN `like` ON like.object_model=activity.object_model AND like.object_id=activity.object_id
            SET activity.object_model=:likeModelClass, activity.object_id=like.id
            WHERE activity.class=:likedActivityClass AND like.id IS NOT NULL and activity.object_model != :likeModelClass
        ";

        Yii::$app->db->createCommand($updateSql, [
            ':likeModelClass' => Like::class,
            ':likedActivityClass' => Liked::class,
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
     *
     * @throws Exception
     */
    public function updateSilent(string $table, $columns, $condition = '', $params = []): void
    {
        $this->db->createCommand()->update($table, $columns, $condition, $params)->execute();
    }

    /**
     * Creates and executes an INSERT SQL statement without any output
     * The method will properly escape the column names, and bind the values to be inserted.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array|Traversable $columns the column data (name => value) to be inserted into the table.
     *
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
        return (!Yii::$app->isInstalled());
    }

    /**
     * Get data from database dsn config
     *
     * @since 1.9.3
     *
     * @param string $name 'host', 'port', 'dbname'
     *
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
     * @param array $params Parameters to translate in $message
     *
     * @return void
     * @since 1.15.0
     */
    protected function logError(string $message, array $params = []): void
    {
        Yii::error($this->logTranslation($message, $params), self::LOG_CATEGORY);
    }

    /**
     * @param string $message Message to be logged
     * @param array $params Parameters to translate in $message
     *
     * @return void
     * @since 1.15.0
     */
    protected function logWarning(string $message, array $params = []): void
    {
        Yii::warning($this->logTranslation($message, $params), self::LOG_CATEGORY);
    }

    /**
     * @param string $message Message to be logged
     * @param array $params Parameters to translate in $message
     *
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
     * @param array $params Parameters to translate in $message
     *
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
     * @param array $params Parameters to translate in $message
     *
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
     *
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
                'line' => $e->getLine(),
            ],
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
        $this->lastException = $t;

        echo 'Exception: ' . $t->getMessage() . ' (' . $t->getFile() . ':' . $t->getLine() . ")\n";
        echo $t->getTraceAsString() . "\n";
    }

    public function getLastException(): ?Throwable
    {
        return $this->lastException;
    }
}
