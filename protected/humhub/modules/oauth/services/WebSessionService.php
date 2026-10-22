<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\services;

use humhub\components\gates\GateManager;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\models\WebSessionCode;
use Yii;
use yii\base\InvalidArgumentException;

/**
 * Turns the app's grant into a web session for its WebViews: a single-use code, redeemed by
 * the WebView, and the binding of the resulting session to the grant.
 *
 * @since 1.20
 */
class WebSessionService
{
    public const CODE_LIFETIME = 60;

    /**
     * Session key binding a web session to the grant it was established from.
     */
    public const SESSION_KEY_GRANT = 'oauth.grantId';

    public function createCode(Grant $grant): string
    {
        if (!$this->isAppGrant($grant)) {
            throw new InvalidArgumentException('Only grants of the HumHub app get web sessions.');
        }

        $code = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        (new WebSessionCode([
            'code_hash' => hash('sha256', $code),
            'grant_id' => $grant->id,
            'expires_at' => time() + self::CODE_LIFETIME,
        ]))->save(false);

        return $code;
    }

    /**
     * @return Grant|null the grant, if the code is valid; a valid code is consumed, expired ones are left for the daily cleanup
     */
    public function redeem(string $code): ?Grant
    {
        $hash = hash('sha256', $code);
        $row = WebSessionCode::findOne(['code_hash' => $hash]);
        if ($row === null) {
            return null;
        }

        // Deleting is the claim: of two concurrent redemptions only one deletes the row.
        $claimed = WebSessionCode::deleteAll(['and', ['code_hash' => $hash], ['>', 'expires_at', time()]]);
        if ($claimed !== 1) {
            return null;
        }

        $grant = $row->grant;
        if ($grant === null || !$this->isAppGrant($grant) || !(new GrantService())->isUsable($grant)) {
            return null;
        }

        return $grant;
    }

    /**
     * Marks the current session as established from the grant.
     */
    public function bindSession(Grant $grant): void
    {
        Yii::$app->session->set(self::SESSION_KEY_GRANT, $grant->id);
        Yii::$app->session->set(GateManager::SESSION_KEY_ISSUED_CREDENTIAL, true);
    }

    /**
     * @return int|null the grant id the current session is bound to, without starting a session
     */
    public function getBoundGrantId(): ?int
    {
        if (!Yii::$app->has('session')) {
            return null;
        }

        $session = Yii::$app->session;
        if (!$session->getIsActive() && !$session->getHasSessionId()) {
            return null;
        }

        $grantId = $session->get(self::SESSION_KEY_GRANT);
        return $grantId === null ? null : (int)$grantId;
    }

    public function unbindSession(): void
    {
        Yii::$app->session->remove(self::SESSION_KEY_GRANT);
        Yii::$app->session->remove(GateManager::SESSION_KEY_ISSUED_CREDENTIAL);
    }

    private function isAppGrant(Grant $grant): bool
    {
        return $grant->type === Grant::TYPE_OAUTH && $grant->client?->type === Client::TYPE_SYSTEM;
    }
}
