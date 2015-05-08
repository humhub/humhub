<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link       http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright  Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Authentication\Validator;

use Traversable;
use Zend\Authentication\Adapter\ValidatableAdapterInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Authentication\Exception;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\AbstractValidator;

/**
 * Authentication Validator
 */
class Authentication extends AbstractValidator
{
    /**
     * Error codes
     * @const string
     */
    const IDENTITY_NOT_FOUND = 'identityNotFound';
    const IDENTITY_AMBIGUOUS = 'identityAmbiguous';
    const CREDENTIAL_INVALID = 'credentialInvalid';
    const UNCATEGORIZED      = 'uncategorized';
    const GENERAL            = 'general';

    /**
     * Error Messages
     * @var array
     */
    protected $messageTemplates = array(
        self::IDENTITY_NOT_FOUND => 'Invalid identity',
        self::IDENTITY_AMBIGUOUS => 'Identity is ambiguous',
        self::CREDENTIAL_INVALID => 'Invalid password',
        self::UNCATEGORIZED      => 'Authentication failed',
        self::GENERAL            => 'Authentication failed',
    );

    /**
     * Authentication Adapter
     * @var ValidatableAdapterInterface
     */
    protected $adapter;

    /**
     * Identity (or field)
     * @var string
     */
    protected $identity;

    /**
     * Credential (or field)
     * @var string
     */
    protected $credential;

    /**
     * Authentication Service
     * @var AuthenticationService
     */
    protected $service;

    /**
     * Sets validator options
     *
     * @param mixed $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (is_array($options)) {
            if (array_key_exists('adapter', $options)) {
                $this->setAdapter($options['adapter']);
            }
            if (array_key_exists('identity', $options)) {
                $this->setIdentity($options['identity']);
            }
            if (array_key_exists('credential', $options)) {
                $this->setCredential($options['credential']);
            }
            if (array_key_exists('service', $options)) {
                $this->setService($options['service']);
            }
        }
        parent::__construct($options);
    }

    /**
     * Get Adapter
     *
     * @return ValidatableAdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set Adapter
     *
     * @param  ValidatableAdapterInterface $adapter
     * @return Authentication
     */
    public function setAdapter(ValidatableAdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Get Identity
     *
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Set Identity
     *
     * @param  mixed          $identity
     * @return Authentication
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * Get Credential
     *
     * @return mixed
     */
    public function getCredential()
    {
        return $this->credential;
    }

    /**
     * Set Credential
     *
     * @param  mixed          $credential
     * @return Authentication
     */
    public function setCredential($credential)
    {
        $this->credential = $credential;

        return $this;
    }

    /**
     * Get Service
     *
     * @return AuthenticationService
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set Service
     *
     * @param  AuthenticationService $service
     * @return Authentication
     */
    public function setService(AuthenticationService $service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Is Valid
     *
     * @param  mixed $value
     * @param  array $context
     * @return bool
     */
    public function isValid($value = null, $context = null)
    {
        if ($value !== null) {
            $this->setCredential($value);
        }

        if (($context !== null) && array_key_exists($this->identity, $context)) {
            $identity = $context[$this->identity];
        } else {
            $identity = $this->identity;
        }
        if (!$this->identity) {
            throw new Exception\RuntimeException('Identity must be set prior to validation');
        }

        if (($context !== null) && array_key_exists($this->credential, $context)) {
            $credential = $context[$this->credential];
        } else {
            $credential = $this->credential;
        }

        if (!$this->adapter) {
            throw new Exception\RuntimeException('Adapter must be set prior to validation');
        }
        $this->adapter->setIdentity($identity);
        $this->adapter->setCredential($credential);

        if (!$this->service) {
            throw new Exception\RuntimeException('AuthenticationService must be set prior to validation');
        }
        $result = $this->service->authenticate($this->adapter);

        if ($result->getCode() != Result::SUCCESS) {
            switch ($result->getCode()) {
                case Result::FAILURE_IDENTITY_NOT_FOUND:
                    $this->error(self::IDENTITY_NOT_FOUND);
                    break;
                case Result::FAILURE_CREDENTIAL_INVALID:
                    $this->error(self::CREDENTIAL_INVALID);
                    break;
                case Result::FAILURE_IDENTITY_AMBIGUOUS:
                    $this->error(self::IDENTITY_AMBIGUOUS);
                    break;
                case Result::FAILURE_UNCATEGORIZED:
                    $this->error(self::UNCATEGORIZED);
                    break;
                default:
                    $this->error(self::GENERAL);
            }

            return false;
        }

        return true;
    }
}
