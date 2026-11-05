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

/**
 * @inheritdoc
 */
class Session extends DbSession
{
    /**
     * @inheritdoc
     */
    public $sessionTable = 'user_http_session';

    /**
     * @var string|null ID of the session this request read last
     */
    private ?string $readId = null;

    /**
     * @var string|null data of the session this request read last
     */
    private ?string $readData = null;

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
    public function readSession($id)
    {
        $data = parent::readSession($id);

        $this->readId = $id;
        $this->readData = $data;

        return $data;
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

            // Sessions are not locked, so what this request read may be outdated by now. If it changed nothing, writing
            // the data back would remove what a parallel request stored in the meantime: only renew the expiry.
            if ($data !== '' && $id === $this->readId && $data === $this->readData) {
                $this->db->createCommand()
                    ->update($this->sessionTable, ['expire' => time() + $this->getTimeout(), 'user_id' => $userId], ['id' => $id])
                    ->execute();

                return true;
            }

            // Atomic INSERT ... ON DUPLICATE KEY UPDATE: concurrent requests with the same (new or expired)
            // session ID must not fail with a duplicate key error, as a SELECT followed by INSERT/UPDATE would.
            $this->db->createCommand()
                ->upsert($this->sessionTable, [
                    'id' => $id,
                    'data' => $data,
                    'expire' => time() + $this->getTimeout(),
                    'user_id' => $userId,
                ])->execute();
        } catch (Exception $e) {
            // A session write handler must not produce output, as it may run mid-request (e.g. session_regenerate_id()).
            // Also log to the PHP error log, as the Yii log targets may fail as well (e.g. DbTarget).
            Yii::error($e, 'user');
            error_log(ErrorHandler::convertExceptionToString($e));

            return false;
        }

        return true;
    }

}
