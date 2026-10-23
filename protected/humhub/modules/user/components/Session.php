<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use Exception;
use Yii;
use yii\web\DbSession;
use yii\web\ErrorHandler;
use yii\web\Request;
use yii\db\Query;
use yii\db\Expression;

/**
 * @inheritdoc
 */
class Session extends DbSession
{
    /**
     * @since 1.20
     */
    public const TABLE = 'user_http_session';

    /**
     * @inheritdoc
     */
    public $sessionTable = self::TABLE;

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
     * @inheritDoc
     */
    public function getTimeout()
    {
        return (int) Yii::$app->user->authTimeout;
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams()
    {
        $params = parent::getCookieParams();
        if (Yii::$app->request->autoEnsureSecureConnection
            && Yii::$app->request->isSecureConnection) {
            $params['secure'] = true;
        }
        return $params;
    }

    /**
     * @inheritdoc
     */
    public function writeSession($id, $data)
    {
        // exception must be caught in session write handler
        // http://us.php.net/manual/en/function.session-set-save-handler.php
        try {
            $userId = Yii::$app->user->getIsGuest() ? null : (int)Yii::$app->user->id;

            $row = (new Query())->select(['id', 'user_id'])
                ->from($this->sessionTable)
                ->where(['id' => $id])
                ->createCommand($this->db)
                ->queryOne();

            $fields = [
                'data' => $data,
                'expire' => time() + $this->getTimeout(),
                'user_id' => $userId,
            ];

            // DbSession::regenerateID() copies the guest row on login, so a login is the change of the user
            if ($userId !== null && ($row === false || (int)$row['user_id'] !== $userId) && $this->hasLoginColumns()) {
                $fields['logged_in_at'] = time();
                $fields['user_agent'] = $this->getUserAgent();
            }

            if ($row === false) {
                $this->db->createCommand()
                    ->insert($this->sessionTable, array_merge(['id' => $id], $fields))
                    ->execute();
            } else {
                $this->db->createCommand()
                    ->update($this->sessionTable, $fields, ['id' => $id])
                    ->execute();
            }
        } catch (Exception $e) {
            $exception = ErrorHandler::convertExceptionToString($e);
            // its too late to use Yii logging here
            error_log($exception);
            echo $exception;

            return false;
        }

        return true;
    }

    /**
     * The code may run before the migration adding the columns, e.g. during an update
     */
    private function hasLoginColumns(): bool
    {
        return $this->db->getTableSchema($this->sessionTable)?->getColumn('logged_in_at') !== null;
    }

    private function getUserAgent(): ?string
    {
        $userAgent = Yii::$app->request instanceof Request ? Yii::$app->request->getUserAgent() : null;

        return empty($userAgent) ? null : mb_substr($userAgent, 0, 255);
    }
}
