<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use Exception;
use Yii;
use yii\db\Expression;
use yii\web\DbSession;
use yii\web\ErrorHandler;
use yii\web\Request;
use yii\db\Query;

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

            $hasLoginColumns = $this->hasLoginColumns();
            $row = (new Query())->select($hasLoginColumns ? ['id', 'user_id', 'user_agent'] : ['id', 'user_id'])
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
            if ($userId !== null && ($row === false || (int)$row['user_id'] !== $userId) && $hasLoginColumns) {
                $fields['logged_in_at'] = time();
                $fields['user_agent'] = $this->getUserAgent();
            } elseif ($userId !== null && $hasLoginColumns && $row['user_agent'] === null) {
                // Sessions started before the columns existed: a session belongs to one browser, so
                // its current user agent is the right one; the login time stays unknown
                $fields['user_agent'] = $this->getUserAgent();
            }

            // Atomic INSERT ... ON DUPLICATE KEY UPDATE: concurrent requests with the same (new or expired)
            // session ID must not fail with a duplicate key error, as an INSERT after the SELECT above would.
            $this->db->createCommand()
                ->upsert($this->sessionTable, array_merge(['id' => $id], $fields), $fields)
                ->execute();
        } catch (Exception $e) {
            // A session write handler must not produce output, as it may run mid-request (e.g. session_regenerate_id()).
            // Also log to the PHP error log, as the Yii log targets may fail as well (e.g. DbTarget).
            Yii::error($e, 'user');
            error_log(ErrorHandler::convertExceptionToString($e));

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
