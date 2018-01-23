<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use Yii;
use yii\web\DbSession;
use yii\web\ErrorHandler;
use yii\db\Query;
use yii\db\Expression;

/**
 * @inheritdoc
 */
class Session extends DbSession
{

    /**
     * Returns all current logged in users.
     * 
     * @return ActiveQueryUser
     */
    public static function getOnlineUsers()
    {
        $query = \humhub\modules\user\models\User::find();
        $query->leftJoin('user_http_session', 'user_http_session.user_id=user.id');
        $query->andWhere(['IS NOT', 'user_http_session.user_id', new Expression('NULL')]);
        $query->andWhere(['>', 'user_http_session.expire', time()]);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public function writeSession($id, $data)
    {
        // exception must be caught in session write handler
        // http://us.php.net/manual/en/function.session-set-save-handler.php
        try {
            $userId = new Expression('NULL');
            if (!Yii::$app->user->getIsGuest()) {
                $userId = Yii::$app->user->id;
            }

            $expire = time() + $this->getTimeout();
            $query = new Query;
            $exists = $query->select(['id'])
                    ->from($this->sessionTable)
                    ->where(['id' => $id])
                    ->createCommand($this->db)
                    ->queryScalar();
            if ($exists === false) {
                $this->db->createCommand()
                        ->insert($this->sessionTable, [
                            'id' => $id,
                            'data' => $data,
                            'expire' => $expire,
                            'user_id' => $userId,
                        ])->execute();
            } else {
                $this->db->createCommand()
                        ->update($this->sessionTable, ['data' => $data, 'expire' => $expire, 'user_id' => $userId], ['id' => $id])
                        ->execute();
            }
        } catch (\Exception $e) {
            $exception = ErrorHandler::convertExceptionToString($e);
            // its too late to use Yii logging here
            error_log($exception);
            echo $exception;

            return false;
        }

        return true;
    }

}
