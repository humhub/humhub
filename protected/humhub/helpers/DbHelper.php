<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\helpers;

use Yii;
use yii\db\Command;
use yii\db\Query;

/**
 * @since 1.15
 */
class DbHelper
{
    /**
     * @see \yii\db\Connection::createCommand()
     * @since 1.15
     */
    public static function dbCommand($sql = null, $params = []): Command
    {
        return Yii::$app->getDb()->createCommand($sql, $params);
    }

    /**
     * @param Command $cmd
     * @param bool $execute
     *
     * @return Command
     * @throws \yii\db\Exception
     * @since 1.15
     */
    public static function dbCommandExecute(Command $cmd, bool $execute = true): Command
    {
        if ($execute) {
            $cmd->execute();
        }

        return $cmd;
    }

    /**
     * @see Query
     * @since 1.15
     */
    public static function dbQuery($tables, $condition, $params = [], $limit = 10): Query
    {
        return (new Query())
            ->from($tables)
            ->where($condition, $params)
            ->limit($limit);
    }

    /**
     * @see Query::select
     * @see Query::from
     * @see Query::where
     * @see \yii\db\QueryTrait::limit()
     * @since 1.15
     */
    public static function dbSelect($tables, $columns, $condition = '', $params = [], $limit = 10, $selectOption = null): array
    {
        return static::dbQuery($tables, $condition, $params, $limit)
            ->select($columns, $selectOption)
            ->all();
    }

    /**
     * @see Command::insert
     * @since 1.15
     */
    public static function dbInsert($table, $columns, bool $execute = true): Command
    {
        return static::dbCommandExecute(static::dbCommand()->insert($table, $columns), $execute);
    }

    /**
     * @see Command::upsert
     * @since 1.15
     */
    public static function dbUpsert($table, $insertColumns, $updateColumns = true, $params = [], bool $execute = true): Command
    {
        return static::dbCommandExecute(static::dbCommand()->upsert(
            $table,
            $insertColumns,
            $updateColumns,
            $params
        ), $execute);
    }

    /**
     * @see Command::update
     * @since 1.15
     */
    public static function dbUpdate($table, $columns, $condition = '', $params = [], bool $execute = true): Command
    {
        return static::dbCommandExecute(
            static::dbCommand()->update($table, $columns, $condition, $params),
            $execute
        );
    }

    /**
     * @see Command::delete()
     * @since 1.15
     */
    public static function dbDelete($table, $condition = '', $params = [], bool $execute = true): Command
    {
        return static::dbCommandExecute(static::dbCommand()->delete($table, $condition, $params), $execute);
    }

    /**
     * @see Command::delete()
     * @since 1.15
     */
    public static function dbCount($tables, $condition = '', $params = [])
    {
        return static::dbQuery($tables, $condition, $params)
            ->select("count(*)")
            ->scalar();
    }
}
