<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\modules\user\components\Session;
use humhub\modules\user\models\User;
use humhub\modules\user\models\UserSession;
use Yii;
use yii\db\Query;

/**
 * Lists and terminates the active sessions of a user
 *
 * @since 1.20
 */
class UserSessionService
{
    private readonly string $currentSessionId;

    public function __construct(public readonly User $user, ?string $currentSessionId = null)
    {
        $this->currentSessionId = $currentSessionId ?? (string)Yii::$app->session->getId();
    }

    /**
     * Sessions are only known when they are stored by the HumHub database session
     */
    public function isAvailable(): bool
    {
        return Yii::$app->session instanceof Session;
    }

    /**
     * @return UserSession[] most recently active first
     */
    public function getActiveSessions(): array
    {
        $timeout = $this->getTimeout();

        $sessions = [];
        foreach ($this->query()->select(['id', 'expire', 'logged_in_at', 'user_agent'])->orderBy(['expire' => SORT_DESC])->all() as $row) {
            $sessions[] = new UserSession(
                key: hash('sha256', $row['id']),
                isCurrent: $row['id'] === $this->currentSessionId,
                loggedInAt: $row['logged_in_at'] === null ? null : (int)$row['logged_in_at'],
                lastActivity: (int)$row['expire'] - $timeout,
                userAgent: $row['user_agent'],
            );
        }

        // The current session first, the order of the others stays by last activity
        usort($sessions, fn(UserSession $a, UserSession $b) => $b->isCurrent <=> $a->isCurrent);

        return $sessions;
    }

    /**
     * A remember-me cookie of that device stays valid, use [[terminateOthers()]] to invalidate it
     *
     * @param string $key the [[UserSession::$key]] of a session of this user
     * @return bool whether a session was terminated - the current session is never terminated here
     */
    public function terminate(string $key): bool
    {
        foreach ($this->query()->select('id')->column() as $id) {
            if ($id !== $this->currentSessionId && hash_equals(hash('sha256', $id), $key)) {
                return Yii::$app->db->createCommand()->delete($this->getTable(), ['id' => $id])->execute() > 0;
            }
        }

        return false;
    }

    /**
     * Terminates all sessions except the current one and invalidates the remember-me cookies of other devices
     */
    public function terminateOthers(): void
    {
        $this->deleteOthers();
        Yii::$app->user->rotateAuthKey($this->user);
    }

    /**
     * Deletes all sessions except the current one, remember-me cookies are not invalidated
     *
     * @since 1.20
     */
    public function deleteOthers(): void
    {
        Yii::$app->db->createCommand()->delete($this->getTable(), [
            'and',
            ['user_id' => $this->user->id],
            ['!=', 'id', $this->currentSessionId],
        ])->execute();
    }

    private function query(): Query
    {
        return (new Query())->from($this->getTable())
            ->where(['user_id' => $this->user->id])
            ->andWhere(['>', 'expire', time()]);
    }

    private function getTimeout(): int
    {
        return Yii::$app->session instanceof Session
            ? Yii::$app->session->getTimeout()
            : (int)Yii::$app->user->authTimeout;
    }

    private function getTable(): string
    {
        return Yii::$app->session instanceof Session ? Yii::$app->session->sessionTable : Session::TABLE;
    }
}
