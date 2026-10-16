<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\controllers\CspReportController;
use Yii;
use yii\helpers\Url;

use function random_bytes;

/**
 * Response
 *
 * Also applies the configured default headers ({@see $defaultHeaders}) to every response.
 *
 * @author Luke
 */
class Response extends \yii\web\Response
{
    /**
     * Placeholder replaced with the CSP nonce of the current session, e.g. `'nonce-xZnHrdklZksb'`.
     * A header containing it is what enables nonce support - there is no separate switch.
     */
    public const NONCE_PLACEHOLDER = '{{ nonce }}';

    /**
     * Placeholder replaced with the URL of {@see CspReportController}.
     */
    public const REPORT_URI_PLACEHOLDER = '{{ reportUri }}';

    private const SESSION_KEY_NONCE = 'security-script-src-nonce';

    /**
     * @var array headers sent with every response, as `header name => value`. Any header may be
     * configured here, not just security related ones.
     *
     * Values may contain {@see NONCE_PLACEHOLDER} and {@see REPORT_URI_PLACEHOLDER}.
     *
     * These are defaults: a header an action set itself is left alone. The one special case is
     * `Content-Security-Policy` (and its report-only variant), which is only sent on HTML
     * documents, because a policy takes effect on a document and nowhere else.
     */
    public array $defaultHeaders = [];

    private ?string $nonce = null;

    public function init()
    {
        if (defined('YII_ENV_TEST') && YII_ENV_TEST && class_exists('indexTextResponseCode', false)) {
            \indexTextResponseCode::$response = $this;
        }

        parent::init();
    }

    /**
     * @inheritdoc
     *
     * The default headers are applied here rather than on `EVENT_BEFORE_SEND`, because the
     * response format sets the `Content-Type` in `prepare()` and because the error handler
     * calls `clear()` before rendering an error page, which drops headers set any earlier.
     */
    public function prepare()
    {
        parent::prepare();

        $this->applyDefaultHeaders();
    }

    /**
     * Returns the CSP nonce of the current session, creating it on first access.
     *
     * The nonce is bound to the session rather than to a single response on purpose: scripts
     * rendered into AJAX and PJAX responses have to satisfy the policy of the document that
     * is already open in the browser.
     *
     * @return string|null null when no configured header uses {@see NONCE_PLACEHOLDER}
     */
    public function getNonce(): ?string
    {
        if ($this->nonce !== null) {
            return $this->nonce;
        }

        if (!$this->usesNonce() || !Yii::$app->has('session')) {
            return null;
        }

        if (!Yii::$app->installationState->hasState(InstallationState::STATE_INSTALLED)) {
            return null;
        }

        $session = Yii::$app->getSession();
        $nonce = $session->get(self::SESSION_KEY_NONCE);

        if (!$nonce) {
            $nonce = base64_encode(random_bytes(18));
            $session->set(self::SESSION_KEY_NONCE, $nonce);
        }

        return $this->nonce = $nonce;
    }

    /**
     * @return bool whether a configured header sends violation reports to {@see CspReportController}
     */
    public function isCspReportingEnabled(): bool
    {
        return $this->hasPlaceholder(self::REPORT_URI_PLACEHOLDER);
    }

    private function applyDefaultHeaders(): void
    {
        $isHtmlDocument = str_starts_with(strtolower((string)$this->headers->get('Content-Type')), 'text/html');

        foreach ($this->defaultHeaders as $name => $value) {
            if (!is_string($value) || $value === '') {
                continue;
            }

            // A Content-Security-Policy only takes effect on a document, and a browser would
            // apply one sent with e.g. a JSON or JavaScript response to nothing at all.
            if (!$isHtmlDocument && stripos($name, 'Content-Security-Policy') === 0) {
                continue;
            }

            // setDefault, so an action that set the header itself keeps its own value.
            $this->headers->setDefault($name, $this->replacePlaceholders($value));
        }
    }

    private function replacePlaceholders(string $value): string
    {
        if (str_contains($value, self::NONCE_PLACEHOLDER)) {
            $nonce = $this->getNonce();
            $value = str_replace(self::NONCE_PLACEHOLDER, $nonce ? "'nonce-$nonce'" : '', $value);
        }

        if (str_contains($value, self::REPORT_URI_PLACEHOLDER)) {
            $value = str_replace(self::REPORT_URI_PLACEHOLDER, Url::to([CspReportController::ROUTE], true), $value);
        }

        return $value;
    }

    private function usesNonce(): bool
    {
        return $this->hasPlaceholder(self::NONCE_PLACEHOLDER);
    }

    private function hasPlaceholder(string $placeholder): bool
    {
        foreach ($this->defaultHeaders as $value) {
            if (is_string($value) && str_contains($value, $placeholder)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function xSendFile($filePath, $attachmentName = null, $options = [])
    {
        if (preg_match('/nginx|frankenphp/i', $_SERVER['SERVER_SOFTWARE'] ?? '')) {
            // set nginx specific X-Sendfile header name
            $options['xHeader'] = 'X-Accel-Redirect';
            // make path relative to docroot
            $docroot = rtrim((string) $_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR);
            if (str_starts_with($filePath, $docroot)) {
                $filePath = substr($filePath, strlen($docroot));
            }
        }

        return parent::xSendFile($filePath, $attachmentName, $options);
    }
}
