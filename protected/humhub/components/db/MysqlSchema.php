<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\db;


use PDOException;
use yii\db\mysql\Schema;

/**
 * Class MysqlSchema
 *
 * This class is a hotfix for Yii2 bug #18207 and will be removed with HumHub 1.7+.
 *
 * @see https://github.com/yiisoft/yii2/issues/18207
 * @expectedDeprecation 1.7
 * @package humhub\components\db
 */
class MysqlSchema extends Schema
{

    /**
     * Collects the foreign key column details for the given table.
     * @param TableSchema $table the table metadata
     * @throws \Exception
     */
    protected function findConstraints($table)
    {
        $sql = <<<'SQL'
SELECT
    `kcu`.`CONSTRAINT_NAME` AS `constraint_name`,
    `kcu`.`COLUMN_NAME` AS `column_name`,
    `kcu`.`REFERENCED_TABLE_NAME` AS `referenced_table_name`,
    `kcu`.`REFERENCED_COLUMN_NAME` AS `referenced_column_name`
FROM `information_schema`.`REFERENTIAL_CONSTRAINTS` AS `rc`
JOIN `information_schema`.`KEY_COLUMN_USAGE` AS `kcu` ON
    (
        `kcu`.`CONSTRAINT_CATALOG` = `rc`.`CONSTRAINT_CATALOG` OR
        (`kcu`.`CONSTRAINT_CATALOG` IS NULL AND `rc`.`CONSTRAINT_CATALOG` IS NULL)
    ) AND
    `kcu`.`CONSTRAINT_SCHEMA` = `rc`.`CONSTRAINT_SCHEMA` AND
    `kcu`.`CONSTRAINT_NAME` = `rc`.`CONSTRAINT_NAME`
WHERE `rc`.`CONSTRAINT_SCHEMA` = database() AND `kcu`.`TABLE_SCHEMA` = database()
AND `rc`.`TABLE_NAME` = :tableName AND `kcu`.`TABLE_NAME` = :tableName1
SQL;

        try {
            $rows = $this->db->createCommand($sql, [':tableName' => $table->name, ':tableName1' => $table->name])->queryAll();
            $constraints = [];

            foreach ($rows as $row) {
                $constraints[$row['constraint_name']]['referenced_table_name'] = $row['referenced_table_name'];
                $constraints[$row['constraint_name']]['columns'][$row['column_name']] = $row['referenced_column_name'];
            }

            $table->foreignKeys = [];
            foreach ($constraints as $name => $constraint) {
                $table->foreignKeys[$name] = array_merge(
                    [$constraint['referenced_table_name']],
                    $constraint['columns']
                );
            }
        } catch (\Exception $e) {
            $previous = $e->getPrevious();
            if (!$previous instanceof PDOException || strpos($previous->getMessage(), 'SQLSTATE[42S02') === false) {
                throw $e;
            }

            // table does not exist, try to determine the foreign keys using the table creation sql
            $sql = $this->getCreateTableSql($table);
            $regexp = '/FOREIGN KEY\s+\(([^\)]+)\)\s+REFERENCES\s+([^\(^\s]+)\s*\(([^\)]+)\)/mi';
            if (preg_match_all($regexp, $sql, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $fks = array_map('trim', explode(',', str_replace('`', '', $match[1])));
                    $pks = array_map('trim', explode(',', str_replace('`', '', $match[3])));
                    $constraint = [str_replace('`', '', $match[2])];
                    foreach ($fks as $k => $name) {
                        $constraint[$name] = $pks[$k];
                    }
                    $table->foreignKeys[md5(serialize($constraint))] = $constraint;
                }
                $table->foreignKeys = array_values($table->foreignKeys);
            }
        }
    }
}
