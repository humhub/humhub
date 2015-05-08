<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Captcha;

use Traversable;
use ZendService\ReCaptcha\ReCaptcha as ReCaptchaService;

/**
 * ReCaptcha adapter
 *
 * Allows to insert captchas driven by ReCaptcha service
 *
 * @see http://recaptcha.net/apidocs/captcha/
 */
class ReCaptcha extends AbstractAdapter
{
    /**@+
     * ReCaptcha Field names
     * @var string
     */
    protected $CHALLENGE = 'recaptcha_challenge_field';
    protected $RESPONSE  = 'recaptcha_response_field';
    /**@-*/

    /**
     * Recaptcha service object
     *
     * @var ReCaptchaService
     */
    protected $service;

    /**
     * Parameters defined by the service
     *
     * @var array
     */
    protected $serviceParams = array();

    /**
     * Options defined by the service
     *
     * @var array
     */
    protected $serviceOptions = array();

    /**#@+
     * Error codes
     */
    const MISSING_VALUE = 'missingValue';
    const ERR_CAPTCHA   = 'errCaptcha';
    const BAD_CAPTCHA   = 'badCaptcha';
    /**#@-*/

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = array(
        self::MISSING_VALUE => 'Missing captcha fields',
        self::ERR_CAPTCHA   => 'Failed to validate captcha',
        self::BAD_CAPTCHA   => 'Captcha value is wrong: %value%',
    );

    /**
     * Retrieve ReCaptcha Private key
     *
     * @return string
     */
    public function getPrivkey()
    {
        return $this->getService()->getPrivateKey();
    }

    /**
     * Retrieve ReCaptcha Public key
     *
     * @return string
     */
    public function getPubkey()
    {
        return $this->getService()->getPublicKey();
    }

    /**
     * Set ReCaptcha Private key
     *
     * @param  string $privkey
     * @return ReCaptcha
     */
    public function setPrivkey($privkey)
    {
        $this->getService()->setPrivateKey($privkey);
        return $this;
    }

    /**
     * Set ReCaptcha public key
     *
     * @param  string $pubkey
     * @return ReCaptcha
     */
    public function setPubkey($pubkey)
    {
        $this->getService()->setPublicKey($pubkey);
        return $this;
    }

    /**
     * Constructor
     *
     * @param  null|array|Traversable $options
     */
    public function __construct($options = null)
    {
        $this->setService(new ReCaptchaService());
        $this->serviceParams  = $this->getService()->getParams();
        $this->serviceOptions = $this->getService()->getOptions();

        parent::__construct($options);

        if (!empty($options)) {
            if (array_key_exists('private_key', $options)) {
                $this->getService()->setPrivateKey($options['private_key']);
            }
            if (array_key_exists('public_key', $options)) {
                $this->getService()->setPublicKey($options['public_key']);
            }
            $this->setOptions($options);
        }
    }

    /**
     * Set service object
     *
     * @param  ReCaptchaService $service
     * @return ReCaptcha
     */
    public function setService(ReCaptchaService $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * Retrieve ReCaptcha service object
     *
     * @return ReCaptchaService
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set option
     *
     * If option is a service parameter, proxies to the service. The same
     * goes for any service options (distinct from service params)
     *
     * @param  string $key
     * @param  mixed $value
     * @return ReCaptcha
     */
    public function setOption($key, $value)
    {
        $service = $this->getService();
        if (isset($this->serviceParams[$key])) {
            $service->setParam($key, $value);
            return $this;
        }
        if (isset($this->serviceOptions[$key])) {
            $service->setOption($key, $value);
            return $this;
        }
        return parent::setOption($key, $value);
    }

    /**
     * Generate captcha
     *
     * @see AbstractAdapter::generate()
     * @return string
     */
    public function generate()
    {
        return "";
    }

    /**
     * Validate captcha
     *
     * @see    \Zend\Validator\ValidatorInterface::isValid()
     * @param  mixed $value
     * @param  mixed $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        if (!is_array($value) && !is_array($context)) {
            $this->error(self::MISSING_VALUE);
            return false;
        }

        if (!is_array($value) && is_array($context)) {
            $value = $context;
        }

        if (empty($value[$this->CHALLENGE]) || empty($value[$this->RESPONSE])) {
            $this->error(self::MISSING_VALUE);
            return false;
        }

        $service = $this->getService();

        $res = $service->verify($value[$this->CHALLENGE], $value[$this->RESPONSE]);

        if (!$res) {
            $this->error(self::ERR_CAPTCHA);
            return false;
        }

        if (!$res->isValid()) {
            $this->error(self::BAD_CAPTCHA, $res->getErrorCode());
            $service->setParam('error', $res->getErrorCode());
            return false;
        }

        return true;
    }

    /**
     * Get helper name used to render captcha
     *
     * @return string
     */
    public function getHelperName()
    {
        return "captcha/recaptcha";
    }
}
