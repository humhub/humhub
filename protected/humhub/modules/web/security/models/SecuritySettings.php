<?php

namespace humhub\modules\web\security\models;

use Exception;
use humhub\modules\web\security\helpers\CSPBuilder;
use humhub\modules\web\security\helpers\Security;
use humhub\modules\web\security\Module;
use Yii;
use yii\base\Model;
use yii\helpers\Url;

/**
 * The SecuritySettings are used to load and parse a security config file.
 * The config file path is defined in the security module class.
 *
 * When initialized this class will load and cache the security rules from the config file and initialize
 * a Content-Security-Policy builder if the `csp` section of the config is defined.
 *
 * The security rules can contain the following sections:
 *
 * - `headers`: contains headers to be set
 * - `csp`:  contains a csp configuration
 * - `csp-report-only`: contains report only rules
 *
 * An instance of this class only manages the csp creation of a single csp section mentioned above. The active section
 * can be set by setting the [[cspSection]]. By default the `csp` section is used.
 *
 * > Note: This class is not responsible for actually setting the header values
 *
 * @package humhub\modules\web\security\models
 * @since 1.4
 */
class SecuritySettings extends Model
{
    public const HEADER_CONTENT_SECRUITY_POLICY = 'Content-Security-Policy';
    public const HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY = 'Content-Security-Policy-Report-Only';
    public const HEADER_X_CONTENT_TYPE = 'X-Content-Type-Options';
    public const HEADER_STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';
    public const HEADER_X_FRAME_OPTIONS = 'X-Frame-Options';

    public const HEADER_REFERRER_POLICY = 'Referrer-Policy';
    public const HEADER_X_PERMITTED_CROSS_DOMAIN_POLICIES = 'X-Permitted-Cross-Domain-Policies';

    public const HEADER_PUBLIC_KEY_PINS = 'Public-Key-Pins';

    public const CSP_SECTION_REPORT_ONLY = 'csp-report-only';

    /**
     * @since 1.19.1
     */
    public const HEADER_REPORTING_ENDPOINTS = 'Reporting-Endpoints';

    /**
     * Replaced with the script nonce of the session, e.g. `'nonce-xZnHrdklZksbCle1zhrmDj9g'`
     * @since 1.19.1
     */
    public const PLACEHOLDER_NONCE = '{{ nonce }}';

    /**
     * Replaced with the `report-uri` and `report-to` directives pointing to the report endpoint
     * @since 1.19.1
     */
    public const PLACEHOLDER_REPORTING = '{{ reporting }}';

    /**
     * @since 1.19.1
     */
    public const REPORTING_ENDPOINT_NAME = 'csp-endpoint';

    /**
     * Endpoint name of the Report-Only policy, its URL carries `disposition=report`
     * @since 1.19.1
     */
    public const REPORT_ONLY_ENDPOINT_NAME = 'csp-report-only-endpoint';

    /**
     * @var [] static config cache
     */
    private static $rules;

    /**
     * @var CSPBuilder
     */
    private $csp;

    /**
     * @var string defines the csp settings key
     */
    public $cspSection = 'csp';

    /**
     * @var bool this flag avoids resetting the nonce to the csp
     */
    private $nonceAttached = false;

    /**
     * @return bool checks if any csp section, the Report-Only policy of the web module or a configured
     * header has reporting enabled
     */
    public static function isReportingEnabled()
    {
        $instance = new static();
        return $instance->isCspReportEnabled()
            || $instance->hasSection(static::CSP_SECTION_REPORT_ONLY)
            || Yii::$app->getModule('web')->enableCspReportOnly
            || $instance->usesReportingPlaceholder();
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        static::$rules = Yii::$app->getModule('web')->security;
        $this->initCSP();
    }

    /**
     * Initializes a static CSPBuilder instance by means of the given `csp` configuration definition.
     *
     * @throws Exception
     */
    private function initCSP()
    {
        if (!static::$rules || !$this->hasSection($this->cspSection)) {
            return;
        }

        $this->csp = CSPBuilder::fromArray(static::$rules[$this->cspSection]);

        if ($this->isCspReportEnabled()) {
            $this->csp->setReportUri(static::getReportUrl($this->isReportOnlyCSP()));
        }
    }

    /**
     * Checks if the currently active security rule activates the script nonce support.
     *
     * @return bool
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy
     */
    public function isNonceSupportActive()
    {
        if (!static::$rules || !isset(static::$rules[$this->cspSection]['nonce'])) {
            return false;
        }

        return static::$rules[$this->cspSection]['nonce'] === true;
    }

    /**
     * Helper function for receiving the Content-Security-Policy header which is either generated from the `csp` section
     * of the security config if given, or may be defined in the `header` section of the configuration directly.
     *
     *
     * > Note: If the `csp` configuration section is given, the Content-Security-Policy of the `header` section will be ignored.
     *
     * @return null|string
     * @throws Exception
     */
    public function getCSPHeader()
    {
        return $this->getHeader(static::HEADER_CONTENT_SECRUITY_POLICY);
    }

    /**
     * Returns the header keys for the csp header, this are either report-only or normal csp header keys with respect of
     * old browsers.
     *
     * @return array
     */
    public function getCSPHeaderKeys()
    {
        // If the `csp section is set to report-only`
        if ($this->isReportOnlyCSP()) {
            return [static::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY];
        }

        return [static::HEADER_CONTENT_SECRUITY_POLICY];
    }

    /**
     * Checks if the current csp section should be treated as report-only csp
     *
     * @return bool
     */
    public function isReportOnlyCSP()
    {
        if (!$this->hasSection($this->cspSection)) {
            return false;
        }

        return $this->cspSection === static::CSP_SECTION_REPORT_ONLY
            || (isset(static::$rules[$this->cspSection]['report-only']) && static::$rules[$this->cspSection]['report-only'] === true);
    }

    /**
     * Compiles and returns the active CSP rule.
     *
     * @return null|string
     */
    private function generateCsp()
    {
        return $this->csp ? $this->csp->compile() : null;
    }

    /**
     * Can be used to get the value of a security header configuration from the config file. The Content-Security-Policy
     * will be generated from the `csp` configuration section if present, otherwise this function will search this header
     * in the `header` section.
     *
     * @param $header
     * @return null|string
     * @throws Exception
     */
    public function getHeader(string $header): ?string
    {
        if ($this->isCSPHeaderKey($header)) {

            // Make sure a nonce has been created and attached
            if (!$this->isNonceSupportActive() && !$this->isReportOnlyCSP()) {
                Security::setNonce();
            } elseif (!$this->nonceAttached) {
                $this->csp->nonce('script-src', Security::getNonce(true));
                $this->nonceAttached = true;
            }

            $csp = $this->generateCsp();
            if ($csp) {
                return $csp;
            }
        }

        return $this->applyHeaderMasks($header);
    }

    /**
     * Converts the placeholders in a header value of the `headers` section, see [[applyPlaceholders()]]
     *
     * @param string $header
     * @return string|null
     */
    private function applyHeaderMasks(string $header): ?string
    {
        if (!isset(static::$rules['headers'][$header])) {
            return null;
        }

        $headerValue = static::$rules['headers'][$header];

        if (is_string($headerValue)) {
            $headerValue = $this->applyPlaceholders($headerValue);
        }

        return $headerValue;
    }

    /**
     * Converts placeholders to their values:
     *  - {{ nonce }} is converted to 'nonce-xZnHrdklZksbCle1zhrmDj9g'
     *                when config `web.csp.nonce` === `true`, otherwise to an empty string
     *  - {{ reporting }} is converted to the `report-uri` and `report-to` directives of the report endpoint,
     *                of the Report-Only endpoint if `$reportOnly` is set
     */
    private function applyPlaceholders(string $value, bool $reportOnly = false): string
    {
        if (str_contains($value, static::PLACEHOLDER_NONCE)) {
            $nonce = $this->isNonceSupportActive() ? Security::getNonce(true) : null;
            $value = str_replace(static::PLACEHOLDER_NONCE, $nonce ? '\'nonce-' . $nonce . '\'' : '', $value);
        }

        if (str_contains($value, static::PLACEHOLDER_REPORTING)) {
            $value = str_replace(
                static::PLACEHOLDER_REPORTING,
                'report-uri ' . static::getReportUrl($reportOnly) . '; report-to '
                . ($reportOnly ? static::REPORT_ONLY_ENDPOINT_NAME : static::REPORTING_ENDPOINT_NAME),
                $value,
            );
        }

        return $value;
    }

    /**
     * @return string|null the Report-Only policy of the web module with its placeholders converted,
     * null when it is not enabled
     * @since 1.19.1
     */
    public function getReportOnlyHeader(): ?string
    {
        $module = Yii::$app->getModule('web');

        if (!$module->enableCspReportOnly || !is_string($module->cspReportOnly) || $module->cspReportOnly === '') {
            return null;
        }

        return $this->applyPlaceholders($module->cspReportOnly, true);
    }

    /**
     * @return bool whether a sent CSP header contains the {{ reporting }} placeholder
     * @since 1.19.1
     */
    public function usesReportingPlaceholder(): bool
    {
        return $this->usesReportingInHeaders() || $this->usesReportingInReportOnly();
    }

    private function usesReportingInHeaders(): bool
    {
        $header = static::$rules['headers'][static::HEADER_CONTENT_SECRUITY_POLICY] ?? null;

        return is_string($header) && str_contains($header, static::PLACEHOLDER_REPORTING);
    }

    private function usesReportingInReportOnly(): bool
    {
        $module = Yii::$app->getModule('web');

        return $module->enableCspReportOnly
            && is_string($module->cspReportOnly)
            && str_contains($module->cspReportOnly, static::PLACEHOLDER_REPORTING);
    }

    /**
     * @return string|null value of the Reporting-Endpoints header for the `report-to` directives in use,
     * null if none is used
     * @since 1.19.1
     */
    public function getReportingEndpointsHeader(): ?string
    {
        $endpoints = [];

        if ($this->usesReportingInHeaders()) {
            $endpoints[] = static::REPORTING_ENDPOINT_NAME . '="' . static::getReportUrl() . '"';
        }

        if ($this->usesReportingInReportOnly()) {
            $endpoints[] = static::REPORT_ONLY_ENDPOINT_NAME . '="' . static::getReportUrl(true) . '"';
        }

        return $endpoints === [] ? null : implode(', ', $endpoints);
    }

    /**
     * @param bool $reportOnly whether the URL is used by a Report-Only policy; adds `disposition=report`,
     * so the endpoint can classify reports of browsers that omit the disposition field
     * @return string absolute URL of the report endpoint, absolute because Reporting-Endpoints requires it
     * @since 1.19.1
     */
    public static function getReportUrl(bool $reportOnly = false): string
    {
        $route = ['/web/security-report'];
        if ($reportOnly) {
            $route['disposition'] = 'report';
        }

        return Url::to($route, true);
    }

    /**
     * Checks if the given header key is a csp related key
     *
     * @param $header
     * @return bool
     */
    public function isCSPHeaderKey($header)
    {
        return in_array($header, [
            static::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY,
            static::HEADER_CONTENT_SECRUITY_POLICY], true);
    }

    /**
     * Returns all headers in the `headers` section of the security configuration
     * @return array
     */
    public function getHeaders()
    {
        $result = [];
        if (!isset(static::$rules['headers'])) {
            return $result;
        }

        return static::$rules['headers'];
    }

    /**
     * Checks if the given section is present in the security configuration
     *
     * @param $section
     * @return bool
     */
    public function hasSection($section)
    {
        return isset(static::$rules[$section]);
    }

    /**
     * @return bool checks if reporting is enabled for on the active csp configuration section
     */
    public function isCspReportEnabled()
    {
        return $this->isReportOnlyCSP() || (isset(static::$rules['csp']['report']) && static::$rules['csp']['report'] === true);
    }
}
