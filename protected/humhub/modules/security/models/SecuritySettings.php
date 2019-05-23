<?php

namespace humhub\modules\security\models;

use humhub\modules\security\helpers\Security;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Json;
use yii\helpers\Url;
use humhub\modules\security\helpers\CSPBuilder;
use humhub\modules\security\Module;


/**
 * The SecuritySettings are used to load and parse the security config file.
 * The config file path is defined in the security module class.
 *
 * When initialized this class will load and cache the security rules from the config file and initialize
 * a Content-Security-Policy builder if the `csp` section of the config is defined.
 *
 * Usage:
 *
 * $settings = new SecuritySettings();
 * $xFrameOptionHeader = $settings->getHeader(SecuritySettings::HEADER_X_FRAME_OPTIONS);
 * $csp = $settings->getCspHeader();
 *
 * > Note: This class is not responsible for actually setting the header values
 *
 * @package humhub\modules\security\models
 * @since 1.4
 */
class SecuritySettings extends Model
{
    const HEADER_CONTENT_SECRUITY_POLICY = 'Content-Security-Policy';
    const HEADER_CONTENT_SECRUITY_POLICY_IE = 'X-Content-Security-Policy';
    const HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY = 'Content-Security-Policy-Report-Only';
    const HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY_IE = 'X-Content-Security-Policy-Report-Only';

    const HEADER_X_CONTENT_TYPE = 'X-Content-Type-Options';
    const HEADER_X_XSS_PROTECTION = 'X-XSS-Protection';
    const HEADER_STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';
    const HEADER_X_FRAME_OPTIONS = 'X-Frame-Options';

    const HEADER_REFERRER_POLICY = 'Referrer-Policy';
    const HEADER_X_PERMITTED_CROSS_DOMAIN_POLICIES = 'X-Permitted-Cross-Domain-Policies';

    const HEADER_PUBLIC_KEY_PINS = 'Public-Key-Pins';

    /**
     * @var []
     */
    private static $rules;

    /**
     * @var CSPBuilder
     */
    private $csp;

    private $nonceAttached = false;

    /**
     * @var string defines the csp settings key
     */
    public $cspSection = 'csp';

    public $forceReportOnly = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!static::rules()) {
            $this->loadRules();
        }

        $this->initCSP();
    }

    public static function flushCache()
    {
        static::$rules = null;
    }

    /**
     * @throws InvalidConfigException
     * @throws \Exception
     */
    private function loadRules()
    {
        $configFile = $this->getConfigFilePath();

        try {
            static::$rules = Json::decode(file_get_contents($configFile));
        } catch (\Exception $e) {
            throw new InvalidConfigException(Yii::t('SecurityModule.error', 'Could not parse security file at {path}!', ['path' => $configFile]));
        }
    }

    /**
     * @return bool|string
     * @throws InvalidConfigException
     */
    private function getConfigFilePath()
    {
        /** @var $module Module */
        $module = Yii::$app->getModule('security');
        return $module->getConfigFilePath();
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
     * @throws \Exception
     */
    public function getCSPHeader()
    {
        return $this->getHeader(static::HEADER_CONTENT_SECRUITY_POLICY);
    }

    public function getCSPHeaderKeys()
    {
        // If the `csp section is set to report-only`
        if($this->isReportOnlyCSP()) {
            return [static::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY, static::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY_IE];
        }

        return [static::HEADER_CONTENT_SECRUITY_POLICY, static::HEADER_CONTENT_SECRUITY_POLICY_IE];
    }

    public function isReportOnlyCSP()
    {
        return $this->forceReportOnly || (isset(static::$rules[$this->cspSection]['report-only']) && static::$rules[$this->cspSection]['report-only'] === true);
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

    private function isEdge()
    {
        return false !== stripos($_SERVER['HTTP_USER_AGENT'], "Edge");
    }

    /**
     * Can be used to get the value of a security header configuration from the config file. The Content-Security-Policy
     * will be generated from the `csp` configuration section if present, otherwise this function will search this header
     * in the `header` section.
     *
     * @param $header
     * @return null|string
     * @throws \Exception
     */
    public function getHeader($header)
    {
        if ($this->isCSPHeaderKey($header)) {

            // Make sure a nonce has been created and attached
            if(!$this->isNonceSupportActive() && !$this->isReportOnlyCSP()) {
                Security::setNonce(null);
            } elseif (!$this->nonceAttached ) {
                $this->csp->nonce('script-src', Security::getNonce(true));
                $this->nonceAttached = true;
            }

            $csp = $this->generateCsp();
            if ($csp) {
                return $csp;
            }
        }

        if (isset(static::$rules['headers'][$header])) {
            return static::$rules['headers'][$header];
        }

        return null;
    }

    public function isCSPHeaderKey($header)
    {
        return in_array($header, [
            static::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY,
            static::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY_IE,
            static::HEADER_CONTENT_SECRUITY_POLICY,
            static::HEADER_CONTENT_SECRUITY_POLICY_IE], true);
    }

    public function getHeaders()
    {
        $result = [];
        if (!isset(static::$rules['headers'])) {
            return $result;
        }

        return static::$rules['headers'];
    }

    public function hasSection($section)
    {
        return isset(static::$rules[$section]);
    }

    /**
     * Initializes a static CSPBuilder instance by means of the given `csp` configuration definition.
     * @throws \Exception
     */
    private function initCSP()
    {
        if (!static::$rules || !$this->hasSection($this->cspSection)) {
            return;
        }

        $this->csp = CSPBuilder::fromArray(static::$rules[$this->cspSection]);

        if($this->isReportOnlyCSP() || (isset(static::$rules['report']) && static::$rules['report'] === true)) {
            $this->csp->setReportUri(Url::toRoute('/security/report/index'));
        }
    }


}