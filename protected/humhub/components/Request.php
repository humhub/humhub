<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;

/**
 * @inheritdoc
 *
 *
 * @author luke
 */
class Request extends \yii\web\Request
{
    /**
     * Http header name for view context information
     *
     * @see \humhub\components\View::$viewContext
     */
    public const HEADER_VIEW_CONTEXT = 'HUMHUB-VIEW-CONTEXT';
    /**
     * Whenever a secure connection is detected, force it.
     *
     * @var bool
     * @since 1.13
     */
    public $autoEnsureSecureConnection = true;

    /**
     * Whether this request was authenticated by the regular browser session rather than by
     * an API credential — set by {@see \humhub\components\api\SessionAuth}.
     *
     * API requests run with a session-less user component (`enableSession = false`) so a
     * token login can never write into the browser session. Consumers that need to tell
     * "no session at all" (a machine client) from "authenticated by the browser's session"
     * (the platform's own frontend calling the API) must therefore ask this flag instead of
     * inferring it from `Yii::$app->user->enableSession`:
     *
     * - {@see \humhub\components\gates\GateFilter} — a session-authenticated request must
     *   still pass the user gates a browser request passes (2FA, legal, onboarding).
     * - {@see \humhub\modules\user\components\Impersonation} — the private-content
     *   restriction of an active impersonation must apply to it too.
     *
     * @var bool
     * @since 1.19
     */
    public bool $isSessionAuthenticated = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (Yii::$app->installationState->hasState(InstallationState::STATE_INSTALLED)) {
            $secret = Yii::$app->settings->get('secret');
            if ($secret != "") {
                $this->cookieValidationKey = $secret;
            }
        }

        if ($this->cookieValidationKey == '') {
            $this->cookieValidationKey = 'installer';
        }

        if (
            defined('YII_ENV_TEST') && YII_ENV_TEST && $_SERVER['SCRIPT_FILENAME'] === 'index-test.php' && in_array(
                $_SERVER['SCRIPT_NAME'],
                ['/sw.js', '/offline.pwa.html', '/manifest.json'],
                true,
            )
        ) {
            $this->setScriptUrl('/index.php');
        }
    }

    /**
     * @return string|null the value of http header `HUMHUB-VIEW-CONTEXT`
     */
    public function getViewContext()
    {
        return $this->getHeaders()->get(static::HEADER_VIEW_CONTEXT);
    }
}
