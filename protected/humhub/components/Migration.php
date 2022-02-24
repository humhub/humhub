<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\models\Setting;
use Yii;

/**
 * Migration is the base class for representing a database migration.
 *
 * @see \yii\db\Migration
 */
class Migration extends \yii\db\Migration
{

    protected function safeCreateTable($table, $columns, $options = null)
    {
        if (!$this->db->getTableSchema($table, true)) {
            $this->createTable($table, $columns, $options);
        } else {
            if (!$this->compact) {
                echo "    > skipped create table $table, table does already exist ...\n";
            }
            Yii::warning("Tried to create an already existing existing table '$table' in migration " . get_class($this));
        }
    }

    protected function safeDropTable($table)
    {
        if ($this->db->getTableSchema($table, true)) {
            $this->dropTable($table);
        } else {
            if (!$this->compact) {
                echo "    > skipped drop table $table, table does not exist ...\n";
            }
            Yii::warning("Tried to drop a non existing table '$table' in migration " . get_class($this));
        }
    }

    /**
     * Check if the column already exists in the table
     *
     * @since 1.9.1
     * @param string $index
     * @param string $table
     * @return bool
     */
    protected function columnExists($column, $table): bool
    {
        $tableSchema = $this->db->getTableSchema($table, true);
        return $tableSchema && in_array($column, $tableSchema->columnNames, true);
    }

    protected function safeDropColumn($table, $column)
    {
        if ($this->columnExists($column, $table)) {
            $this->dropColumn($table, $column);
        } else {
            if (!$this->compact) {
                echo "    > skipped drop column $column from table $table, column does not exist ...\n";
            }
            Yii::warning("Tried to drop a non existing column '$column' from table '$table' in migration " . get_class($this));
        }
    }

    protected function safeAddColumn($table, $column, $type)
    {
        if (!$this->columnExists($column, $table)) {
            $this->addColumn($table, $column, $type);
        } else {
            if (!$this->compact) {
                echo "    > skipped add column $column from table $table, column does already exist ...\n";
            }
            Yii::warning("Tried to add an already existing column '$column' on table '$table' in migration " . get_class($this));
        }
    }

    /**
     * Check if the index already exists in the table
     *
     * @since 1.9.1
     * @param string $index
     * @param string $table
     * @return bool
     */
    protected function indexExists($index, $table): bool
    {
        return (bool) $this->db->createCommand('SHOW KEYS FROM ' . $this->db->quoteTableName($table) .
            ' WHERE Key_name = ' . $this->db->quoteValue($index))
            ->queryOne();
    }

    /**
     * Check if the foreign index already exists in the table
     *
     * @since 1.9.1
     * @param string $index
     * @param string $table
     * @return bool
     */
    protected function foreignIndexExists(string $index, string $table): bool
    {
        return (bool) $this->db->createCommand('SELECT * FROM information_schema.key_column_usage
            WHERE REFERENCED_TABLE_NAME IS NOT NULL 
              AND TABLE_NAME = ' . $this->db->quoteValue($table) . '
              AND TABLE_SCHEMA = ' . $this->db->quoteValue($this->getDsnAttribute('dbname')). '
              AND CONSTRAINT_NAME = ' . $this->db->quoteValue($index))
            ->queryOne();
    }

    /**
     * Create an index if it doesn't exist yet
     *
     * @since 1.9.1
     * @param string $index
     * @param string $table
     * @param string|array $columns
     * @param bool $unique
     */
    protected function safeCreateIndex($index, $table, $columns, $unique = false)
    {
        if ($this->indexExists($index, $table)) {
            if (!$this->compact) {
                echo "    > skipped create index $index in the table $table, index already exists ...\n";
            }
            Yii::warning("Tried to create an already existing index '$index' on table '$table' in migration " . get_class($this));
            return;
        }

        $this->createIndex($index, $table, $columns, $unique);
    }

    /**
     * Drop an index if it exists in the table
     *
     * @since 1.9.1
     * @param string $index
     * @param string $table
     */
    protected function safeDropIndex($index, $table)
    {
        if (!$this->indexExists($index, $table)) {
            if (!$this->compact) {
                echo "    > skipped drop index $index from the table $table, index does not exist ...\n";
            }
            Yii::warning("Tried to drop a non existing index '$index' from table '$table' in migration " . get_class($this));
            return;
        }

        $this->dropIndex($index, $table);
    }

    /**
     * Add a primary index if it doesn't exist yet
     *
     * @since 1.9.1
     * @param string $index
     * @param string $table
     * @param string|array $columns
     */
    protected function safeAddPrimaryKey($index, $table, $columns)
    {
        if ($this->indexExists('PRIMARY', $table)) {
            if (!$this->compact) {
                echo "    > skipped create primary index $index in the table $table, primary index already exists ...\n";
            }
            Yii::warning("Tried to create an already existing primary index '$index' on table '$table' in migration " . get_class($this));
            return;
        }

        $this->addPrimaryKey($index, $table, $columns);
    }

    /**
     * Drop a primary index if it exists in the table
     *
     * @since 1.9.1
     * @param string $index
     * @param string $table
     */
    protected function safeDropPrimaryKey($index, $table)
    {
        if (!$this->indexExists('PRIMARY', $table)) {
            if (!$this->compact) {
                echo "    > skipped drop primary index $index from the table $table, primary index does not exist ...\n";
            }
            Yii::warning("Tried to drop a non existing primary index '$index' from table '$table' in migration " . get_class($this));
            return;
        }

        $this->dropPrimaryKey($index, $table);
    }

    /**
     * Add a foreign index if it doesn't exist yet
     *
     * @since 1.9.1
     * @param string $index
     * @param string $table
     * @param string|array $columns
     */
    protected function safeAddForeignKey($index, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        if ($this->foreignIndexExists($index, $table)) {
            if (!$this->compact) {
                echo "    > skipped create foreign index $index in the table $table, foreign index already exists ...\n";
            }
            Yii::warning("Tried to create an already existing foreign index '$index' on table '$table' in migration " . get_class($this));
            return;
        }

        $this->addForeignKey($index, $table, $columns, $refTable, $refColumns, $delete, $update);
    }

    /**
     * Drop a foreign if it exists in the table
     *
     * @since 1.9.1
     * @param string $index
     * @param string $table
     */
    protected function safeDropForeignKey($index, $table)
    {
        if (!$this->foreignIndexExists($index, $table)) {
            if (!$this->compact) {
                echo "    > skipped drop foreign index $index from the table $table, foreign index does not exist ...\n";
            }
            Yii::warning("Tried to drop a non existing foreign index '$index' from table '$table' in migration " . get_class($this));
            return;
        }

        $this->dropForeignKey($index, $table);
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
     * @throws \yii\db\Exception
     */
    protected function renameClass($oldClass, $newClass)
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
         * Use raw query for better performace.
         */
        $updateSql = "
            UPDATE activity
            LEFT JOIN `like` ON like.object_model=activity.object_model AND like.object_id=activity.object_id
            SET activity.object_model=:likeModelClass, activity.object_id=like.id
            WHERE activity.class=:likedActivityClass AND like.id IS NOT NULL and activity.object_model != :likeModelClass
        ";

        Yii::$app->db->createCommand($updateSql, [
            ':likeModelClass' => \humhub\modules\like\models\Like::className(),
            ':likedActivityClass' => \humhub\modules\like\activities\Liked::className()
        ])->execute();
    }

    /**
     * Creates and executes an UPDATE SQL statement without any output.
     * The method will properly escape the column names and bind the values to be updated.
     *
     * @param string $table the table to be updated.
     * @param array $columns the column data (name => value) to be updated.
     * @param array|string $condition the conditions that will be put in the WHERE part. Please
     * refer to [[Query::where()]] on how to specify conditions.
     * @param array $params the parameters to be bound to the query.
     */
    public function updateSilent($table, $columns, $condition = '', $params = [])
    {
        $this->db->createCommand()->update($table, $columns, $condition, $params)->execute();
    }

    /**
     * Creates and executes an INSERT SQL statement without any output
     * The method will properly escape the column names, and bind the values to be inserted.
     * @param string $table the table that new rows will be inserted into.
     * @param array $columns the column data (name => value) to be inserted into the table.
     */
    public function insertSilent($table, $columns)
    {
        $this->db->createCommand()->insert($table, $columns)->execute();
    }

    /**
     * Returns whether this is a completely new installation with an empty database (installation process).
     *
     * @return bool
     * @since 1.8
     */
    protected function isInitialInstallation()
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
}
