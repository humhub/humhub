<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

use Zend\Filter\FilterChain;
use Zend\Validator\NotEmpty;
use Zend\Validator\ValidatorChain;

class Input implements InputInterface, EmptyContextInterface
{
    /**
     * @var bool
     */
    protected $allowEmpty = false;

    /**
     * @var bool
     */
    protected $continueIfEmpty = false;

    /**
     * @var bool
     */
    protected $breakOnFailure = false;

    /**
     * @var string|null
     */
    protected $errorMessage;

    /**
     * @var FilterChain
     */
    protected $filterChain;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $notEmptyValidator = false;

    /**
     * @var bool
     */
    protected $required = true;

    /**
     * @var ValidatorChain
     */
    protected $validatorChain;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var mixed
     */
    protected $fallbackValue;

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * @param  bool $allowEmpty
     * @return Input
     */
    public function setAllowEmpty($allowEmpty)
    {
        $this->allowEmpty = (bool) $allowEmpty;
        return $this;
    }

    /**
     * @param  bool $breakOnFailure
     * @return Input
     */
    public function setBreakOnFailure($breakOnFailure)
    {
        $this->breakOnFailure = (bool) $breakOnFailure;
        return $this;
    }

    /**
     * @param bool $continueIfEmpty
     * @return \Zend\InputFilter\Input
     */
    public function setContinueIfEmpty($continueIfEmpty)
    {
        $this->continueIfEmpty = (bool) $continueIfEmpty;
        return $this;
    }

    /**
     * @param  string|null $errorMessage
     * @return Input
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = (null === $errorMessage) ? null : (string) $errorMessage;
        return $this;
    }

    /**
     * @param  FilterChain $filterChain
     * @return Input
     */
    public function setFilterChain(FilterChain $filterChain)
    {
        $this->filterChain = $filterChain;
        return $this;
    }

    /**
     * @param  string $name
     * @return Input
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * @param  bool $required
     * @return Input
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;
        $this->setAllowEmpty(!$required);
        return $this;
    }

    /**
     * @param  ValidatorChain $validatorChain
     * @return Input
     */
    public function setValidatorChain(ValidatorChain $validatorChain)
    {
        $this->validatorChain = $validatorChain;
        return $this;
    }

    /**
     * @param  mixed $value
     * @return Input
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param  mixed $value
     * @return Input
     */
    public function setFallbackValue($value)
    {
        $this->fallbackValue = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function allowEmpty()
    {
        return $this->allowEmpty;
    }

    /**
     * @return bool
     */
    public function breakOnFailure()
    {
        return $this->breakOnFailure;
    }

    /**
     * @return bool
     */
    public function continueIfEmpty()
    {
        return $this->continueIfEmpty;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return FilterChain
     */
    public function getFilterChain()
    {
        if (!$this->filterChain) {
            $this->setFilterChain(new FilterChain());
        }
        return $this->filterChain;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return ValidatorChain
     */
    public function getValidatorChain()
    {
        if (!$this->validatorChain) {
            $this->setValidatorChain(new ValidatorChain());
        }
        return $this->validatorChain;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $filter = $this->getFilterChain();
        return $filter->filter($this->value);
    }

    /**
     * @return mixed
     */
    public function getFallbackValue()
    {
        return $this->fallbackValue;
    }

    /**
     * @param  InputInterface $input
     * @return Input
     */
    public function merge(InputInterface $input)
    {
        $this->setAllowEmpty($input->allowEmpty());
        $this->setBreakOnFailure($input->breakOnFailure());
        $this->setContinueIfEmpty($input->continueIfEmpty());
        $this->setErrorMessage($input->getErrorMessage());
        $this->setName($input->getName());
        $this->setRequired($input->isRequired());
        $this->setValue($input->getRawValue());

        $filterChain = $input->getFilterChain();
        $this->getFilterChain()->merge($filterChain);

        $validatorChain = $input->getValidatorChain();
        $this->getValidatorChain()->merge($validatorChain);
        return $this;
    }

    /**
     * @param  mixed $context Extra "context" to provide the validator
     * @return bool
     */
    public function isValid($context = null)
    {
        // Empty value needs further validation if continueIfEmpty is set
        // so don't inject NotEmpty validator which would always
        // mark that as false
        if (!$this->continueIfEmpty()) {
            $this->injectNotEmptyValidator();
        }
        $validator = $this->getValidatorChain();
        $value     = $this->getValue();
        $result    = $validator->isValid($value, $context);
        if (!$result && $fallbackValue = $this->getFallbackValue()) {
            $this->setValue($fallbackValue);
            $result = true;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        if (null !== $this->errorMessage) {
            return (array) $this->errorMessage;
        }

        if ($this->getFallbackValue()) {
            return array();
        }

        $validator = $this->getValidatorChain();
        return $validator->getMessages();
    }

    /**
     * @return void
     */
    protected function injectNotEmptyValidator()
    {
        if ((!$this->isRequired() && $this->allowEmpty()) || $this->notEmptyValidator) {
            return;
        }
        $chain = $this->getValidatorChain();

        // Check if NotEmpty validator is already in chain
        $validators = $chain->getValidators();
        foreach ($validators as $validator) {
            if ($validator['instance'] instanceof NotEmpty) {
                $this->notEmptyValidator = true;
                return;
            }
        }

        $chain->prependByName('NotEmpty', array(), true);
        $this->notEmptyValidator = true;
    }
}
