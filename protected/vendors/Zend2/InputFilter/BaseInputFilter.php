<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

use ArrayAccess;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\InitializableInterface;

/**
 * @todo       How should we deal with required input when data is missing?
 *             should a message be returned? if so, what message?
 */
class BaseInputFilter implements InputFilterInterface, UnknownInputsCapableInterface, InitializableInterface
{
    protected $data;
    protected $inputs = array();
    protected $invalidInputs;
    protected $validationGroup;
    protected $validInputs;

    /**
     * This function is automatically called when creating element with factory. It
     * allows to perform various operations (add elements...)
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Countable: number of inputs in this input filter
     *
     * Only details the number of direct children.
     *
     * @return int
     */
    public function count()
    {
        return count($this->inputs);
    }

    /**
     * Add an input to the input filter
     *
     * @param  InputInterface|InputFilterInterface $input
     * @param  null|string                         $name Name used to retrieve this input
     * @throws Exception\InvalidArgumentException
     * @return InputFilterInterface
     */
    public function add($input, $name = null)
    {
        if (!$input instanceof InputInterface && !$input instanceof InputFilterInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of %s or %s as its first argument; received "%s"',
                __METHOD__,
                'Zend\InputFilter\InputInterface',
                'Zend\InputFilter\InputFilterInterface',
                (is_object($input) ? get_class($input) : gettype($input))
            ));
        }

        if ($input instanceof InputInterface && (empty($name) || is_int($name))) {
            $name = $input->getName();
        }

        if (isset($this->inputs[$name]) && $this->inputs[$name] instanceof InputInterface) {
            // The element already exists, so merge the config. Please note
            // that this merges the new input into the original.
            $original = $this->inputs[$name];
            $original->merge($input);
            return $this;
        }

        $this->inputs[$name] = $input;
        return $this;
    }

    /**
     * Retrieve a named input
     *
     * @param  string $name
     * @throws Exception\InvalidArgumentException
     * @return InputInterface|InputFilterInterface
     */
    public function get($name)
    {
        if (!array_key_exists($name, $this->inputs)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: no input found matching "%s"',
                __METHOD__,
                $name
            ));
        }
        return $this->inputs[$name];
    }

    /**
     * Test if an input or input filter by the given name is attached
     *
     * @param  string $name
     * @return bool
     */
    public function has($name)
    {
        return (array_key_exists($name, $this->inputs));
    }

    /**
     * Remove a named input
     *
     * @param  string $name
     * @return InputFilterInterface
     */
    public function remove($name)
    {
        unset($this->inputs[$name]);
        return $this;
    }

    /**
     * Set data to use when validating and filtering
     *
     * @param  array|Traversable $data
     * @throws Exception\InvalidArgumentException
     * @return InputFilterInterface
     */
    public function setData($data)
    {
        if (!is_array($data) && !$data instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable argument; received %s',
                __METHOD__,
                (is_object($data) ? get_class($data) : gettype($data))
            ));
        }
        if (is_object($data) && !$data instanceof ArrayAccess) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        $this->data = $data;
        $this->populate();
        return $this;
    }

    /**
     * Is the data set valid?
     *
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function isValid()
    {
        $data = $this->getRawValues();
        if (null === $data) {
            throw new Exception\RuntimeException(sprintf(
                '%s: no data present to validate!',
                __METHOD__
            ));
        }

        $inputs = $this->validationGroup ?: array_keys($this->inputs);
        return $this->validateInputs($inputs, $data);
    }

    /**
     * Validate a set of inputs against the current data
     *
     * @param  array $inputs
     * @param  array $data
     * @return bool
     */
    protected function validateInputs(array $inputs, array $data = array())
    {
        // backwards compatibility
        if (empty($data)) {
            $data = $this->getRawValues();
        }

        $this->validInputs   = array();
        $this->invalidInputs = array();
        $valid               = true;

        foreach ($inputs as $name) {
            $input      = $this->inputs[$name];
            $dataExists = array_key_exists($name, $data);

            // key doesn't exist, but input is not required; valid
            if (!$dataExists
                && $input instanceof InputInterface
                && !$input->isRequired()
            ) {
                $this->validInputs[$name] = $input;
                continue;
            }

            // key doesn't exist, input is required, allows empty; valid if
            // continueIfEmpty is false or input doesn't implement
            // that interface; otherwise validation chain continues
            if (!$dataExists
                && $input instanceof InputInterface
                && $input->isRequired()
                && $input->allowEmpty()
            ) {
                if(!($input instanceOf EmptyContextInterface && $input->continueIfEmpty())) {
                    $this->validInputs[$name] = $input;
                    continue;
                }
            }

            // key exists, is null, input is not required; valid
            if ($dataExists
                && null === $data[$name]
                && $input instanceof InputInterface
                && !$input->isRequired()
            ) {
                $this->validInputs[$name] = $input;
                continue;
            }

            // key exists, is null, input is required, allows empty; valid if
            // continueIfEmpty is false or input doesn't implement
            // that interface; otherwise validation chain continues
            if ($dataExists
                && null === $data[$name]
                && $input instanceof InputInterface
                && $input->isRequired()
                && $input->allowEmpty()
            ) {
                if (!($input instanceof EmptyContextInterface && $input->continueIfEmpty())) {
                    $this->validInputs[$name] = $input;
                    continue;
                }
            }

            // key exists, empty string, input is not required, allows empty; valid
            if ($dataExists
                && '' === $data[$name]
                && $input instanceof InputInterface
                && !$input->isRequired()
                && $input->allowEmpty()
            ) {
                $this->validInputs[$name] = $input;
                continue;
            }

            // key exists, empty string, input is required, allows empty; valid
            // if continueIfEmpty is false, otherwise validation continues
            if ($dataExists
                && '' === $data[$name]
                && $input instanceof InputInterface
                && $input->isRequired()
                && $input->allowEmpty()
            ) {
                if (!($input instanceof EmptyContextInterface && $input->continueIfEmpty())) {
                    $this->validInputs[$name] = $input;
                    continue;
                }
            }

            // key exists, is array representing file, no file present, input not
            // required or allows empty; valid
            if ($dataExists
                && is_array($data[$name])
                && (
                    (isset($data[$name]['error'])
                        && $data[$name]['error'] === UPLOAD_ERR_NO_FILE)
                    || (count($data[$name]) === 1
                        && isset($data[$name][0])
                        && is_array($data[$name][0])
                        && isset($data[$name][0]['error'])
                        && $data[$name][0]['error'] === UPLOAD_ERR_NO_FILE)
                )
                && $input instanceof InputInterface
                && (!$input->isRequired() || $input->allowEmpty())
            ) {
                $this->validInputs[$name] = $input;
                continue;
            }

            // make sure we have a value (empty) for validation
            if (!$dataExists) {
                $data[$name] = null;
            }

            // Validate an input filter
            if ($input instanceof InputFilterInterface) {
                if (!$input->isValid()) {
                    $this->invalidInputs[$name] = $input;
                    $valid = false;
                    continue;
                }
                $this->validInputs[$name] = $input;
                continue;
            }

            // Validate an input
            if ($input instanceof InputInterface) {
                if (!$input->isValid($data)) {
                    // Validation failure
                    $this->invalidInputs[$name] = $input;
                    $valid = false;

                    if ($input->breakOnFailure()) {
                        return false;
                    }
                    continue;
                }
                $this->validInputs[$name] = $input;
                continue;
            }
        }

        return $valid;
    }

    /**
     * Provide a list of one or more elements indicating the complete set to validate
     *
     * When provided, calls to {@link isValid()} will only validate the provided set.
     *
     * If the initial value is {@link VALIDATE_ALL}, the current validation group, if
     * any, should be cleared.
     *
     * Implementations should allow passing a single array value, or multiple arguments,
     * each specifying a single input.
     *
     * @param  mixed $name
     * @return InputFilterInterface
     */
    public function setValidationGroup($name)
    {
        if ($name === self::VALIDATE_ALL) {
            $this->validationGroup = null;
            return $this;
        }

        if (is_array($name)) {
            $inputs = array();
            foreach ($name as $key => $value) {
                if (!$this->has($key)) {
                    $inputs[] = $value;
                } else {
                    $inputs[] = $key;

                    // Recursively populate validation groups for sub input filters
                    $this->inputs[$key]->setValidationGroup($value);
                }
            }

            if (!empty($inputs)) {
                $this->validateValidationGroup($inputs);
                $this->validationGroup = $inputs;
            }

            return $this;
        }

        $inputs = func_get_args();
        $this->validateValidationGroup($inputs);
        $this->validationGroup = $inputs;

        return $this;
    }

    /**
     * Return a list of inputs that were invalid.
     *
     * Implementations should return an associative array of name/input pairs
     * that failed validation.
     *
     * @return InputInterface[]
     */
    public function getInvalidInput()
    {
        return (is_array($this->invalidInputs) ? $this->invalidInputs : array());
    }

    /**
     * Return a list of inputs that were valid.
     *
     * Implementations should return an associative array of name/input pairs
     * that passed validation.
     *
     * @return InputInterface[]
     */
    public function getValidInput()
    {
        return (is_array($this->validInputs) ? $this->validInputs : array());
    }

    /**
     * Retrieve a value from a named input
     *
     * @param  string $name
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function getValue($name)
    {
        if (!array_key_exists($name, $this->inputs)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a valid input name; "%s" was not found in the filter',
                __METHOD__,
                $name
            ));
        }
        $input = $this->inputs[$name];
        return $input->getValue();
    }

    /**
     * Return a list of filtered values
     *
     * List should be an associative array, with the values filtered. If
     * validation failed, this should raise an exception.
     *
     * @return array
     */
    public function getValues()
    {
        $inputs = $this->validationGroup ?: array_keys($this->inputs);
        $values = array();
        foreach ($inputs as $name) {
            $input = $this->inputs[$name];

            if ($input instanceof InputFilterInterface) {
                $values[$name] = $input->getValues();
                continue;
            }
            $values[$name] = $input->getValue();
        }
        return $values;
    }

    /**
     * Retrieve a raw (unfiltered) value from a named input
     *
     * @param  string $name
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function getRawValue($name)
    {
        if (!array_key_exists($name, $this->inputs)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a valid input name; "%s" was not found in the filter',
                __METHOD__,
                $name
            ));
        }
        $input = $this->inputs[$name];
        return $input->getRawValue();
    }

    /**
     * Return a list of unfiltered values
     *
     * List should be an associative array of named input/value pairs,
     * with the values unfiltered.
     *
     * @return array
     */
    public function getRawValues()
    {
        $values = array();
        foreach ($this->inputs as $name => $input) {
            if ($input instanceof InputFilterInterface) {
                $values[$name] = $input->getRawValues();
                continue;
            }
            $values[$name] = $input->getRawValue();
        }
        return $values;
    }

    /**
     * Return a list of validation failure messages
     *
     * Should return an associative array of named input/message list pairs.
     * Pairs should only be returned for inputs that failed validation.
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = array();
        foreach ($this->getInvalidInput() as $name => $input) {
            $messages[$name] = $input->getMessages();
        }

        return $messages;
    }

    /**
     * Ensure all names of a validation group exist as input in the filter
     *
     * @param  array $inputs
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    protected function validateValidationGroup(array $inputs)
    {
        foreach ($inputs as $name) {
            if (!array_key_exists($name, $this->inputs)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'setValidationGroup() expects a list of valid input names; "%s" was not found',
                    $name
                ));
            }
        }
    }

    /**
     * Populate the values of all attached inputs
     *
     * @return void
     */
    protected function populate()
    {
        foreach (array_keys($this->inputs) as $name) {
            $input = $this->inputs[$name];

            if (!isset($this->data[$name])) {
                // No value; clear value in this input
                if ($input instanceof InputFilterInterface) {
                    $input->setData(array());
                    continue;
                }

                $input->setValue(null);
                continue;
            }

            $value = $this->data[$name];

            if ($input instanceof InputFilterInterface) {
                $input->setData($value);
                continue;
            }

            $input->setValue($value);
        }
    }

    /**
     * Is the data set has unknown input ?
     *
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function hasUnknown()
    {
        if (null === $this->data) {
            throw new Exception\RuntimeException(sprintf(
                '%s: no data present!',
                __METHOD__
            ));
        }

        $data   = array_keys($this->data);
        $inputs = array_keys($this->inputs);
        $diff   = array_diff($data, $inputs);
        if (!empty($diff)) {
            return count(array_intersect($diff, $inputs)) == 0;
        }

        return false;
    }

    /**
     * Return the unknown input
     *
     * @throws Exception\RuntimeException
     * @return array
     */
    public function getUnknown()
    {
        if (null === $this->data) {
            throw new Exception\RuntimeException(sprintf(
                '%s: no data present!',
                __METHOD__
            ));
        }

        $data   = array_keys($this->data);
        $inputs = array_keys($this->inputs);
        $diff   = array_diff($data, $inputs);

        $unknownInputs = array();
        $intersect     = array_intersect($diff, $data);
        if (!empty($intersect)) {
            foreach ($intersect as $key) {
                $unknownInputs[$key] = $this->data[$key];
            }
        }

        return $unknownInputs;
    }

    /**
     * Get an array of all inputs
     *
     * @return array
     */
    public function getInputs()
    {
        return $this->inputs;
    }
}
