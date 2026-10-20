<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\web\security\helpers;

use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;

/**
 * A single Content Security Policy violation report, as posted by the browser to the report endpoint.
 *
 * Browsers use two formats: the `report-uri` format (`application/csp-report`) holds one report under the
 * key `csp-report`, the Reporting API format (`application/reports+json`) a list of reports of different
 * types, of which only `csp-violation` entries are used.
 *
 * @since 1.19.1
 */
class CspReport
{
    /**
     * Larger request bodies are discarded completely.
     */
    public const MAX_BODY_SIZE = 65536;

    public const LOG_CATEGORY = 'web.security';

    private function __construct(private readonly array $report)
    {
    }

    /**
     * @return self[] the CSP violation reports contained in the request body, empty if it holds none
     */
    public static function parse(string $body): array
    {
        if ($body === '' || strlen($body) > self::MAX_BODY_SIZE) {
            return [];
        }

        try {
            $data = Json::decode($body);
        } catch (InvalidArgumentException) {
            return [];
        }

        if (!is_array($data)) {
            return [];
        }

        // Reporting API: a list of reports of different types
        if (array_is_list($data)) {
            $reports = [];
            foreach ($data as $entry) {
                if (is_array($entry) && ($entry['type'] ?? null) === 'csp-violation' && is_array($entry['body'] ?? null)) {
                    $reports[] = new self($entry['body']);
                }
            }

            return $reports;
        }

        // report-uri: a single report
        if (is_array($data['csp-report'] ?? null)) {
            return [new self($data['csp-report'])];
        }

        return [];
    }

    /**
     * A report without a disposition counts as enforced, so a real violation is never logged as a warning.
     */
    public function isReportOnly(): bool
    {
        return ($this->report['disposition'] ?? null) === 'report';
    }

    /**
     * @return string one line naming the directive, the blocked resource and the source file, e.g.
     * `CSP violation (report-only): script-src-elem https://example.org/x.js in https://example.com/dashboard`
     */
    public function getSummary(): string
    {
        $directive = $this->field('effective-directive', 'effectiveDirective')
            ?? $this->field('violated-directive', 'violatedDirective')
            ?? 'unknown directive';

        $summary = 'CSP violation (' . ($this->isReportOnly() ? 'report-only' : 'enforced') . '): ' . $directive;

        $blocked = $this->field('blocked-uri', 'blockedURL');
        if ($blocked !== null) {
            $summary .= ' ' . $blocked;
        }

        $source = $this->field('source-file', 'sourceFile');
        if ($source !== null) {
            $summary .= ' in ' . $source;
        }

        return $summary;
    }

    /**
     * Logs the summary and the full report, as warning for a Report-Only violation and as error otherwise.
     */
    public function log(): void
    {
        $message = $this->getSummary() . "\n"
            . json_encode($this->report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // The report quotes the policy, and with it the nonce of the reporting session
        $message = preg_replace('/\'nonce-[^\']*\'/', "'nonce-xxxxxxxxxxxxxxxxxxxxxxxx'", $message);

        if ($this->isReportOnly()) {
            Yii::warning($message, self::LOG_CATEGORY);
        } else {
            Yii::error($message, self::LOG_CATEGORY);
        }
    }

    /**
     * @return string|null the first non-empty string value of the given keys, without control characters
     */
    private function field(string ...$names): ?string
    {
        foreach ($names as $name) {
            $value = $this->report[$name] ?? null;
            if (is_string($value)) {
                $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return null;
    }
}
