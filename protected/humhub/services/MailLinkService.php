<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\services;

use Yii;

/**
 * Service to handle URLs to "go" app in order to open then in mobile app
 *
 * @author Luke
 * @since 1.18.0
 */
class MailLinkService
{
    private ?string $appUrl;
    private ?string $sitePattern;
    private ?string $hid = null;
    private bool $isEnabled = false;

    public function __construct()
    {
        $this->appUrl = Yii::$app->params['humhub']['goUrl'];

        $baseUrl = Yii::$app->settings->get('baseUrl');
        $this->sitePattern = is_string($baseUrl) ? preg_quote($baseUrl, '#') : null;

        $installationId = Yii::$app->getModule('admin')->settings->get('installationId');
        $this->hid = is_string($installationId) && strlen($installationId) > 5
            ? substr($installationId, 0, 3) . substr($installationId, -3)
            : null;

        $this->isEnabled = (bool) Yii::$app->settings->get('mailerLinkService');
    }

    public static function instance(): self
    {
        return new self();
    }

    public function isConfigured(): bool
    {
        return $this->isEnabled &&
            is_string($this->appUrl) &&
            is_string($this->hid) &&
            is_string($this->sitePattern);
    }

    public function processUrls(string $text): ?string
    {
        return $this->isConfigured()
            ? preg_replace_callback('#' . $this->sitePattern . '[^\s]+#i', [$this, 'callbackReplaceUrl'], $text)
            : $text;
    }

    private function callbackReplaceUrl($matches)
    {
        return $this->buildUrl($matches[0]);
    }

    public function processLinks($text): ?string
    {
        return $this->isConfigured()
            ? preg_replace_callback('#(<a.+?href=")(' . $this->sitePattern . '[^"\r\n]*)#is', [$this, 'callbackReplaceLink'], $text)
            : $text;
    }

    private function callbackReplaceLink($matches)
    {
        return $matches[1] . $this->buildUrl($matches[2]);
    }

    public function buildUrl(string $url): string
    {
        return $this->appUrl . '?url=' . urlencode($url) . '&hid=' . $this->hid;
    }
}
