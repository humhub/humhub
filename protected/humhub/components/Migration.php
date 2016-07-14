<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;

/**
 * Migration is the base class for representing a database migration.
 *
 * @see \yii\db\Migration
 */
class Migration extends \yii\db\Migration
{

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
        $this->updateSilent('user_mentioning', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('user_follow', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('wall', ['object_model' => $newClass], ['object_model' => $oldClass]);

        /**
         * Looking up "NewLike" activities with this className
         * Since 0.20 the className changed to Like (is not longer the target object e.g. post)
         * 
         * Use raw query for better performace.
         */
        /*
          $likes = (new \yii\db\Query())->select("activity.*, like.id as likeid")->from('activity')
          ->leftJoin('like', 'like.object_model=activity.object_model AND like.object_id=activity.object_id')
          ->where(['class' => 'humhub\modules\like\activities\Liked'])->andWhere('like.id IS NOT NULL')->andWhere(['!=', 'activity.object_model', \humhub\modules\like\models\Like::className()]);

          foreach ($likes->each() as $like) {
          Yii::$app->db->createCommand()->update('activity', ['object_model' => \humhub\modules\like\models\Like::className(), 'object_id' => $like['likeid']], ['id' => $like['id']])->execute();
          }
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
    public function updateSilent($table, $columns, $condition = '', $params = array())
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

}
