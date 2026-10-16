<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\services\DocumentRootService;
use humhub\services\PwaService;
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
     * @inheritdoc
     */
    public function init()
    {
        // Only meaningful for an actual HTTP request - this component is also constructed where
        // neither value exists, and asking for them there throws.
        if (isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_FILENAME'])) {
            $rewrittenScriptUrl = self::resolveRewrittenScriptUrl(
                $this->getScriptUrl(),
                $this->getUrl(),
                DocumentRootService::PUBLIC_DIR,
            );

            if ($rewrittenScriptUrl !== null) {
                $this->setScriptUrl($rewrittenScriptUrl);
            }
        }

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
            defined('YII_ENV_TEST') && YII_ENV_TEST && basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === 'index-test.php' && in_array(
                $_SERVER['SCRIPT_NAME'],
                [
                    '/' . PwaService::URL_SERVICE_WORKER,
                    '/' . PwaService::URL_OFFLINE,
                    '/' . PwaService::URL_MANIFEST,
                ],
                true,
            )
        ) {
            $this->setScriptUrl('/index.php');
        }
    }

    /**
     * Corrects the entry script URL when the web server maps every request into the document root.
     *
     * The `.htaccess` shipped in the installation directory rewrites `/dashboard` to
     * `public/dashboard` internally, so nothing beside `public/` can be requested. Apache then
     * reports `/public/index.php` as the entry script although the visitor asked for `/dashboard`.
     * Yii derives the base URL from the entry script, ends up with `/public`, cannot reconcile that
     * with the request and throws "Unable to determine the path info of the current request" - so
     * this is not cosmetic, the installation does not work without the correction.
     *
     * The rewrite is recognised without a marker: if the entry script sits in the document root
     * directory but the request URI does not carry that segment, the web server put it there.
     *
     * @param string $scriptUrl URL of the entry script as the web server reports it
     * @param string $requestUri URI the visitor actually requested
     * @param string $publicDir name of the document root directory
     * @return string|null the corrected entry script URL, `null` when nothing was rewritten
     * @since 1.20
     */
    public static function resolveRewrittenScriptUrl(string $scriptUrl, string $requestUri, string $publicDir): ?string
    {
        $scriptDir = rtrim(dirname($scriptUrl), '/');
        $suffix = '/' . $publicDir;

        if (!str_ends_with($scriptDir, $suffix)) {
            return null;
        }

        $path = parse_url($requestUri, PHP_URL_PATH) ?: '';

        // The visitor asked for the segment themselves, so it belongs in the base URL.
        if ($path === $scriptDir || str_starts_with($path, $scriptDir . '/')) {
            return null;
        }

        return substr($scriptDir, 0, -strlen($suffix)) . '/' . basename($scriptUrl);
    }

    /**
     * @return string|null the value of http header `HUMHUB-VIEW-CONTEXT`
     */
    public function getViewContext()
    {
        return $this->getHeaders()->get(static::HEADER_VIEW_CONTEXT);
    }
}
