<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\services;

use Throwable;
use Yii;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\web\Request as WebRequest;

/**
 * Asks the web server whether it serves the installation root.
 *
 * With the document root on `public/` the installation root - `protected/`, `uploads/`, the
 * Composer metadata and the `.env` file - must not be reachable. Whether it is cannot be read off
 * the local filesystem: `DOCUMENT_ROOT` is unreliable behind nginx aliases, PHP-FPM and containers.
 * So the site is asked over HTTP for a file that only exists in the installation root.
 *
 * @since 1.20
 */
final class DocumentRootProbeService
{
    public const STATE_EXPOSED = 'exposed';
    public const STATE_SAFE = 'safe';
    public const STATE_UNKNOWN = 'unknown';

    public const CACHE_KEY = 'docroot_probe';
    public const CACHE_DURATION = 3600;

    /**
     * Files that exist in the installation root and in no document root.
     *
     * `composer.json` and every dotfile are unusable here: the shipped `.htaccess` denies them, so
     * probing one would measure whether that single file is blocked rather than whether the
     * installation root is served at all. The changelog backs up the readme in case a distribution
     * package strips it.
     */
    public const PROBE_FILES = ['README.md', 'CHANGELOG.md'];

    /**
     * Servers that answer unknown paths with a styled 200 page would otherwise read as a hit.
     */
    public const PROBE_MARKER = 'HumHub';

    /**
     * A response of this type is the application answering, never one of the probe files.
     *
     * Where the `.htaccess` in the installation directory maps every request into `public/`, asking
     * for a file there reaches HumHub itself - status 200, and the page mentions HumHub, so neither
     * the status nor the marker tells the two apart. The content type does: a served markdown file
     * comes back as `text/markdown` or `text/plain`, never as an HTML document.
     */
    public const APPLICATION_CONTENT_TYPE = 'text/html';

    public const TIMEOUT = 5;

    private readonly DocumentRootService $documentRoot;
    private readonly ?string $baseUrl;

    /** @var callable(string):(array{status: int, content: string, contentType: string}|null) */
    private $fetch;

    /**
     * @param callable(string):(array{status: int, content: string, contentType: string}|null)|null $fetch
     *        performs one request, returning `null` when the site could not be reached
     */
    public function __construct(
        ?DocumentRootService $documentRoot = null,
        ?string $baseUrl = null,
        ?callable $fetch = null,
    ) {
        $this->documentRoot = $documentRoot ?? DocumentRootService::instance();
        $this->baseUrl = $baseUrl ?? self::detectBaseUrl();
        $this->fetch = $fetch ?? fn(string $url) => self::request($url);
    }

    public static function instance(): self
    {
        return new self();
    }

    /**
     * The cached verdict for this installation.
     *
     * An unknown verdict is not cached: a site that could not reach itself once should be retried
     * rather than reported as unverifiable for the next hour.
     *
     * @return string one of the `STATE_*` constants
     */
    public function getState(): string
    {
        // The deprecated entry script is served from the installation root, so there is nothing to ask.
        if ($this->documentRoot->isLegacyEntryScript()) {
            return self::STATE_EXPOSED;
        }

        // Outside a web request there is no trustworthy base URL to reason about.
        if ($this->baseUrl === null) {
            return self::STATE_UNKNOWN;
        }

        $rootUrl = $this->getInstallationRootUrl();
        if ($rootUrl === null) {
            return self::STATE_SAFE;
        }

        $cached = Yii::$app->cache->get(self::CACHE_KEY);
        if (is_string($cached)) {
            return $cached;
        }

        $state = $this->probe($rootUrl);

        if ($state !== self::STATE_UNKNOWN) {
            Yii::$app->cache->set(self::CACHE_KEY, $state, self::CACHE_DURATION);
        }

        return $state;
    }

    /**
     * @return string|null the URL the installation root would be reachable under, `null` when it
     *                     cannot be addressed through this site
     */
    public function getInstallationRootUrl(): ?string
    {
        return $this->baseUrl === null
            ? null
            : $this->documentRoot->getInstallationRootUrl($this->baseUrl);
    }

    /**
     * Runs the probe without touching the cache.
     *
     * @param string $installationRootUrl URL the installation root would be reachable under
     * @return string one of the `STATE_*` constants
     */
    public function probe(string $installationRootUrl): string
    {
        $answered = false;

        foreach (self::PROBE_FILES as $file) {
            $response = ($this->fetch)(rtrim($installationRootUrl, '/') . '/' . $file);

            // The site could not be reached at all - asking for another file only costs another
            // timeout. The second probe file is there for a stripped README, not for an outage.
            if ($response === null) {
                break;
            }

            $answered = true;

            if (
                $response['status'] >= 200 && $response['status'] < 300
                && !str_starts_with(trim(strtolower($response['contentType'])), self::APPLICATION_CONTENT_TYPE)
                && str_contains($response['content'], self::PROBE_MARKER)
            ) {
                return self::STATE_EXPOSED;
            }
        }

        return $answered ? self::STATE_SAFE : self::STATE_UNKNOWN;
    }

    /**
     * @return array{status: int, content: string, contentType: string}|null `null` when the site
     *         could not be reached
     */
    private static function request(string $url): ?array
    {
        try {
            $response = (new Client())
                ->createRequest()
                ->setMethod('GET')
                ->setUrl($url)
                ->setOptions([
                    'timeout' => self::TIMEOUT,
                    'sslVerifyPeer' => (bool) (Yii::$app->params['curl']['validateSsl'] ?? true),
                ])
                ->send();
        } catch (Throwable $e) {
            Yii::warning('Could not probe the installation root at ' . $url . ': ' . $e->getMessage(), 'base');
            return null;
        }

        return [
            'status' => (int) $response->getStatusCode(),
            'content' => (string) $response->getContent(),
            'contentType' => (string) $response->getHeaders()->get('content-type', ''),
        ];
    }

    /**
     * The URL this installation is being reached under, or `null` outside a web request.
     *
     * Only the current request can answer this. The stored `baseUrl` setting cannot: it may predate
     * a move of the document root, and trusting it hides the very misconfiguration this check looks
     * for - an installation reached under `/public/index.php` while the setting still reads
     * `https://example.com` derives no installation root URL at all and reports a clean bill for a
     * document root that serves `protected/` and `.env`.
     */
    public static function detectBaseUrl(): ?string
    {
        return Yii::$app->getRequest() instanceof WebRequest
            ? rtrim(Url::base(true), '/')
            : null;
    }
}
